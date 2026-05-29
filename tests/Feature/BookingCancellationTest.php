<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_booking_can_be_cancelled_and_paid_amount_is_refunded(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));
        $booking = $this->createBooking([
            'check_in_date' => now()->addDays(3)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
            'number_of_nights' => 2,
            'room_total' => 200000,
            'deposit_amount' => 50000,
            'balance_amount' => 150000,
        ]);

        Payment::create([
            'payment_number' => 'PAY-CANCEL-001',
            'payable_type' => Booking::class,
            'payable_id' => $booking->id,
            'guest_id' => $booking->guest_id,
            'booking_id' => $booking->id,
            'amount' => 50000,
            'payment_method' => 'Cash',
            'status' => 'Partial',
            'paid_at' => now(),
        ]);

        $response = $this->post(route('bookings.cancel', $booking));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'Cancelled',
            'deposit_amount' => 0,
            'balance_amount' => 0,
        ]);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'Refunded',
        ]);
        $this->assertDatabaseHas('rooms', [
            'id' => $booking->room_id,
            'status' => 'Available',
        ]);
    }

    public function test_booking_cannot_be_cancelled_on_check_in_date(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));
        $booking = $this->createBooking([
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
        ]);

        $response = $this->post(route('bookings.cancel', $booking));

        $response->assertSessionHasErrors('booking');
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'Confirmed',
        ]);
        $this->assertDatabaseHas('rooms', [
            'id' => $booking->room_id,
            'status' => 'Reserved',
        ]);
    }

    private function createBooking(array $attributes = []): Booking
    {
        $guest = Guest::create([
            'full_name' => 'Cancellation Test Guest',
        ]);

        $room = Room::create([
            'room_number' => '301',
            'room_type' => 'Single',
            'price_per_night' => 100000,
            'status' => 'Reserved',
        ]);

        return Booking::create(array_merge([
            'booking_number' => 'BK-CANCEL-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'room_type' => 'Single',
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'number_of_nights' => 1,
            'number_of_guests' => 1,
            'status' => 'Confirmed',
            'room_rate' => 100000,
            'room_total' => 100000,
            'deposit_amount' => 0,
            'balance_amount' => 100000,
        ], $attributes));
    }
}
