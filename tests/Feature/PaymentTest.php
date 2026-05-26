<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_payment_cannot_exceed_remaining_balance(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));
        $booking = $this->createPartiallyPaidBooking();

        $response = $this->post(route('payments.store'), [
            'target_type' => 'booking',
            'target_id' => $booking->id,
            'amount' => 150001,
            'payment_method' => 'Cash',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertSame('150000.00', $booking->fresh()->balance_amount);
        $this->assertSame(1, $booking->payments()->count());
    }

    public function test_booking_payment_can_receive_exact_remaining_balance(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));
        $booking = $this->createPartiallyPaidBooking();

        $response = $this->post(route('payments.store'), [
            'target_type' => 'booking',
            'target_id' => $booking->id,
            'amount' => 150000,
            'payment_method' => 'Cash',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('0.00', $booking->fresh()->balance_amount);
        $this->assertSame(2, $booking->payments()->count());
    }

    private function createPartiallyPaidBooking(): Booking
    {
        $guest = Guest::create([
            'full_name' => 'Payment Test Guest',
        ]);

        $room = Room::create([
            'room_number' => '201',
            'room_type' => 'Single',
            'price_per_night' => 100000,
            'status' => 'Reserved',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-PAYMENT-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'room_type' => 'Single',
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'number_of_nights' => 2,
            'number_of_guests' => 1,
            'status' => 'Pending',
            'room_rate' => 100000,
            'room_total' => 200000,
            'deposit_amount' => 50000,
            'balance_amount' => 150000,
        ]);

        Payment::create([
            'payment_number' => 'PAY-DEPOSIT-001',
            'payable_type' => Booking::class,
            'payable_id' => $booking->id,
            'guest_id' => $guest->id,
            'booking_id' => $booking->id,
            'amount' => 50000,
            'payment_method' => 'Cash',
            'status' => 'Partial',
            'paid_at' => now(),
        ]);

        return $booking;
    }
}
