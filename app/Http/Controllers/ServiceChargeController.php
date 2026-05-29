<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\OtherCharge;
use App\Models\Payment;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceChargeController extends Controller
{
    public function index()
    {
        $serviceCharges = OtherCharge::with('guest', 'booking.room')->latest()->get();

        return view('service_charges.index', compact('serviceCharges'));
    }

    public function create(Request $request)
    {
        $guestId = $request->integer('guest_id') ?: null;

        return view('service_charges.create', [
            'serviceCharge' => new OtherCharge(),
            'guests' => Guest::orderBy('full_name')->get(),
            'bookings' => Booking::with('guest', 'room')
                ->whereIn('status', ['Pending', 'Confirmed', 'Checked In'])
                ->when($guestId, fn ($query) => $query->where('guest_id', $guestId))
                ->latest()
                ->get(),
            'guestId' => $guestId,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_id' => ['required', 'exists:guests,id'],
            'booking_id' => ['required', 'exists:bookings,id'],
            'service_type' => ['required', 'in:Laundry,Ironing,Transport,Room service,Other'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:Cash,Mobile money,Card,Room charge'],
        ]);

        $booking = Booking::where('guest_id', $data['guest_id'])->findOrFail($data['booking_id']);
        $amount = (float) $data['amount'];
        $paid = $data['payment_method'] === 'Room charge' ? 0 : (float) ($data['paid_amount'] ?? 0);

        if ($paid > $amount) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Paid amount cannot exceed the service charge total of '.number_format($amount, 2).'.',
            ]);
        }

        $serviceCharge = DB::transaction(function () use ($data, $booking, $amount, $paid) {
            $serviceCharge = OtherCharge::create([
                'guest_id' => $data['guest_id'],
                'booking_id' => $booking->id,
                'service_type' => $data['service_type'],
                'description' => $data['description'],
                'amount' => $amount,
                'paid_amount' => $paid,
                'balance_amount' => max(0, $amount - $paid),
                'payment_status' => $paid >= $amount ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
                'payment_method' => $data['payment_method'],
                'created_by' => auth()->id(),
            ]);

            if ($paid > 0) {
                Payment::create([
                    'payment_number' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
                    'payable_type' => OtherCharge::class,
                    'payable_id' => $serviceCharge->id,
                    'guest_id' => $serviceCharge->guest_id,
                    'booking_id' => $serviceCharge->booking_id,
                    'amount' => $paid,
                    'payment_method' => $data['payment_method'],
                    'status' => $paid >= $amount ? 'Paid' : 'Partial',
                    'received_by' => auth()->id(),
                    'paid_at' => now(),
                ]);
            }

            AuditService::log('service_charge.create', $serviceCharge, $serviceCharge->getAttributes());

            return $serviceCharge;
        });

        return redirect()->route('guests.show', $serviceCharge->guest_id)->with('success', 'Service charge added successfully.');
    }

    public function show(OtherCharge $serviceCharge)
    {
        $serviceCharge->load('guest', 'booking.room', 'payments');

        return view('service_charges.show', compact('serviceCharge'));
    }
}
