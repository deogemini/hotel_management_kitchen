<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Lodge;
use App\Models\Room;
use App\Models\User;
use App\Models\WebsiteEnquiry;
use App\Models\WebsiteEntry;
use App\Models\WebsiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_and_staff_can_manage_website_content(): void
    {
        $this->get('/')->assertOk()->assertSee('Hard Rock Executive Lodge');
        $this->get(route('public.rooms'))->assertOk();
        $this->get(route('public.facilities'))->assertOk();
        $this->get(route('public.gallery'))->assertOk();
        $this->get(route('public.about'))->assertOk();
        $this->get(route('public.contact'))->assertOk()->assertSee('info@hardrockexecutivelodge.co.tz');
        $this->get(route('public.policy', 'booking'))->assertOk()->assertSee('Draft policy');
        $this->get(route('public.sitemap'))->assertOk()->assertSee('<urlset', false);

        $this->get(route('website.admin.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']))
            ->get(route('website.admin.index'))
            ->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'chef']))
            ->get(route('website.admin.index'))
            ->assertForbidden();
    }

    public function test_room_booking_and_website_editor_pages_render(): void
    {
        $lodge = $this->createPublicProperty();
        $this->createRoom($lodge);
        $entry = WebsiteEntry::create([
            'category' => 'service',
            'image_category' => 'Facilities',
            'title' => 'Reception',
            'description' => 'Contact the lodge for details.',
            'is_active' => true,
        ]);

        $this->get(route('public.rooms'))->assertOk()->assertSee('Executive');
        $this->get(route('public.room', 'executive'))->assertOk()->assertSee('Guest capacity');
        $this->get(route('public.booking', ['room_type' => 'Executive']))->assertOk()->assertSee('Stay summary');
        $this->get(route('public.facilities'))->assertOk()->assertSee('Reception');

        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']))
            ->get(route('website.admin.settings'))
            ->assertOk();
        $this->get(route('website.admin.entries.create'))->assertOk();
        $this->get(route('website.admin.entries.edit', $entry))->assertOk();
        $this->get(route('website.admin.enquiries'))->assertOk();

        $enquiry = WebsiteEnquiry::create(['name' => 'Guest', 'email' => 'guest@example.com', 'message' => 'A question']);
        $this->patch(route('website.admin.enquiries.status', $enquiry), ['status' => 'Reviewed'])
            ->assertRedirect();
        $this->assertDatabaseHas('website_enquiries', ['id' => $enquiry->id, 'status' => 'Reviewed']);
    }

    public function test_public_booking_request_is_pending_and_duplicate_submission_is_idempotent(): void
    {
        $lodge = $this->createPublicProperty();
        $this->createRoom($lodge);
        $token = (string) Str::uuid();
        $payload = $this->bookingPayload($token);

        $firstResponse = $this->post(route('public.booking.submit'), $payload);
        $booking = Booking::firstOrFail();
        $firstResponse->assertRedirect(route('public.confirmation', [
            'bookingNumber' => $booking->booking_number,
            'token' => $booking->public_confirmation_token,
        ]));
        $this->assertSame('Pending', $booking->status);
        $this->assertNull($booking->room_id);
        $this->assertSame(2, $booking->number_of_guests);
        $this->assertSame(200.0, (float) $booking->room_total);

        $this->post(route('public.booking.submit'), $payload)->assertRedirect(route('public.confirmation', [
            'bookingNumber' => $booking->booking_number,
            'token' => $booking->public_confirmation_token,
        ]));
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('guests', 1);
    }

    public function test_public_booking_rechecks_overlapping_room_availability(): void
    {
        $lodge = $this->createPublicProperty();
        $room = $this->createRoom($lodge);
        $guest = Guest::create(['lodge_id' => $lodge->id, 'full_name' => 'Existing Guest', 'email' => 'existing@example.com']);
        $checkIn = CarbonImmutable::today('Africa/Dar_es_Salaam')->addDays(2);
        Booking::create([
            'booking_number' => 'EXISTING-BOOKING',
            'lodge_id' => $lodge->id,
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'room_type' => $room->room_type,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkIn->addDay()->toDateString(),
            'number_of_nights' => 1,
            'number_of_guests' => 1,
            'status' => 'Confirmed',
            'room_rate' => 200,
            'room_total' => 200,
        ]);

        $response = $this->from(route('public.booking'))->post(route('public.booking.submit'), $this->bookingPayload((string) Str::uuid(), [
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkIn->addDay()->toDateString(),
        ]));

        $response->assertRedirect(route('public.booking'));
        $response->assertSessionHasErrors('room_type');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_contact_enquiries_are_saved_and_rate_limited(): void
    {
        $this->createPublicProperty();
        $payload = [
            'name' => 'A Guest',
            'email' => 'guest@example.com',
            'phone' => '0712345678',
            'message' => 'Please share room information.',
            'website' => '',
        ];

        $this->from(route('public.contact'))->post(route('public.contact.submit'), $payload)->assertRedirect(route('public.contact'));
        $this->assertDatabaseHas('website_enquiries', ['email' => 'guest@example.com', 'status' => 'New']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('public.contact.submit'), $payload);
        }
        $this->post(route('public.contact.submit'), $payload)->assertTooManyRequests();
    }

    private function createPublicProperty(): Lodge
    {
        $lodge = Lodge::create(['name' => 'Hard Rock Executive Lodge']);
        WebsiteSetting::create([
            'lodge_id' => $lodge->id,
            'contact_email' => 'info@hardrockexecutivelodge.co.tz',
            'currency_code' => 'TZS',
            'hero_title' => 'Hard Rock Executive Lodge',
        ]);

        return $lodge;
    }

    private function createRoom(Lodge $lodge): Room
    {
        return Room::create([
            'lodge_id' => $lodge->id,
            'room_number' => '101',
            'room_type' => 'Executive',
            'price_per_night' => 200,
            'status' => 'Available',
            'features' => ['WiFi'],
            'public_capacity' => 2,
        ]);
    }

    private function bookingPayload(string $token, array $overrides = []): array
    {
        $checkIn = CarbonImmutable::today('Africa/Dar_es_Salaam')->addDays(2);

        return array_replace([
            'submission_token' => $token,
            'room_type' => 'Executive',
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkIn->addDay()->toDateString(),
            'number_of_adults' => 1,
            'number_of_children' => 1,
            'name' => 'Test Guest',
            'email' => 'guest@example.com',
            'phone' => '0712345678',
            'special_requests' => '',
            'website' => '',
        ], $overrides);
    }
}
