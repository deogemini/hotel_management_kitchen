@extends('website.layout')
@section('title', 'Booking request received')
@section('description', 'Your booking request was received and is pending confirmation from Hard Rock Executive Lodge.')

@section('content')
    <section class="section wrap confirmation-wrap">
        <div class="confirmation-card"><span class="confirmation-check" aria-hidden="true">✓</span><p class="eyebrow">REQUEST RECEIVED</p><h1>Thank you, {{ explode(' ', $booking->guest->full_name)[0] }}.</h1><p class="confirmation-copy">Your booking request is <strong>pending lodge confirmation</strong>. The lodge will contact you using the details you provided.</p>
            <div class="reference-box"><small>REQUEST REFERENCE</small><strong>{{ $booking->booking_number }}</strong></div>
            <dl class="confirmation-details"><div><dt>Room category</dt><dd>{{ $booking->room_type }}</dd></div><div><dt>Check-in</dt><dd>{{ $booking->check_in_date->format('d M Y') }}</dd></div><div><dt>Check-out</dt><dd>{{ $booking->check_out_date->format('d M Y') }}</dd></div><div><dt>Guests</dt><dd>{{ $booking->number_of_guests }}</dd></div><div><dt>Estimated room total</dt><dd>{{ number_format((float) $booking->room_total, 0) }} {{ $site['settings']->currency_code ?: config('website.currency') }}</dd></div><div><dt>Payment</dt><dd>No payment collected</dd></div></dl>
            <p class="fine-print">This is a booking request, not a confirmed reservation. Final availability, rate, and payment terms will be confirmed by the lodge.</p><a class="button button-dark" href="{{ route('public.home') }}">Return to home <span aria-hidden="true">→</span></a>
        </div>
    </section>
@endsection
