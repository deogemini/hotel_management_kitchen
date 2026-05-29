<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\OtherCharge;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceChargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_charge_can_be_added_with_partial_payment(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']));
        [$guest, $booking] = $this->createGuestBooking();

        $response = $this->post(route('service-charges.store'), [
            'guest_id' => $guest->id,
            'booking_id' => $booking->id,
            'service_type' => 'Laundry',
            'description' => 'Laundry clothes',
            'amount' => 20000,
            'paid_amount' => 5000,
            'payment_method' => 'Cash',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('other_charges', [
            'guest_id' => $guest->id,
            'booking_id' => $booking->id,
            'service_type' => 'Laundry',
            'paid_amount' => 5000,
            'balance_amount' => 15000,
            'payment_status' => 'Partial',
        ]);
        $this->assertDatabaseHas('payments', [
            'guest_id' => $guest->id,
            'booking_id' => $booking->id,
            'amount' => 5000,
            'payment_method' => 'Cash',
        ]);
    }

    public function test_service_charge_balance_can_be_paid_later(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']));
        [$guest, $booking] = $this->createGuestBooking();

        $charge = OtherCharge::create([
            'guest_id' => $guest->id,
            'booking_id' => $booking->id,
            'service_type' => 'Ironing',
            'description' => 'Ironing clothes',
            'amount' => 10000,
            'paid_amount' => 0,
            'balance_amount' => 10000,
            'payment_status' => 'Unpaid',
            'payment_method' => 'Cash',
        ]);

        $response = $this->post(route('payments.store'), [
            'target_type' => 'service_charge',
            'target_id' => $charge->id,
            'amount' => 10000,
            'payment_method' => 'Cash',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('Paid', $charge->fresh()->payment_status);
        $this->assertSame('0.00', $charge->fresh()->balance_amount);
    }

    private function createGuestBooking(): array
    {
        $guest = Guest::create(['full_name' => 'Service Test Guest']);
        $room = Room::create([
            'room_number' => '401',
            'room_type' => 'Single',
            'price_per_night' => 50000,
            'status' => 'Occupied',
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-SERVICE-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'room_type' => 'Single',
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'number_of_nights' => 1,
            'number_of_guests' => 1,
            'status' => 'Checked In',
            'room_rate' => 50000,
            'room_total' => 50000,
            'deposit_amount' => 0,
            'balance_amount' => 50000,
        ]);

        return [$guest, $booking];
    }
}
