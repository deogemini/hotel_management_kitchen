<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_booking_cannot_be_checked_in(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));

        $booking = $this->createBooking([
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'status' => 'Confirmed',
        ]);

        $response = $this->post(route('bookings.check-in', $booking));

        $response->assertSessionHasErrors('booking');
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'Confirmed',
            'checked_in_at' => null,
        ]);
        $this->assertDatabaseHas('rooms', [
            'id' => $booking->room_id,
            'status' => 'Reserved',
        ]);
    }

    public function test_today_booking_can_be_checked_in(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));

        $booking = $this->createBooking([
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'status' => 'Confirmed',
        ]);

        $response = $this->post(route('bookings.check-in', $booking));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'Checked In',
        ]);
        $this->assertDatabaseHas('rooms', [
            'id' => $booking->room_id,
            'status' => 'Occupied',
        ]);
        $this->assertNotNull($booking->fresh()->checked_in_at);
    }

    private function createBooking(array $attributes = []): Booking
    {
        $room = Room::create([
            'room_number' => '101',
            'room_type' => 'Single',
            'price_per_night' => 50000,
            'status' => 'Reserved',
        ]);

        $guest = Guest::create([
            'full_name' => 'Test Guest',
        ]);

        return Booking::create(array_merge([
            'booking_number' => 'BK-TEST-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'room_type' => 'Single',
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'number_of_nights' => 1,
            'number_of_guests' => 1,
            'status' => 'Confirmed',
            'room_rate' => 50000,
            'room_total' => 50000,
            'deposit_amount' => 0,
            'balance_amount' => 50000,
        ], $attributes));
    }
}
