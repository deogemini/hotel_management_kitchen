@extends('website.layout')
@section('title', 'Book your stay')
@section('description', 'Send a room booking request to Hard Rock Executive Lodge. Requests remain pending until the lodge confirms availability.')

@section('content')
    <section class="page-hero page-hero-compact"><div class="wrap"><p class="eyebrow eyebrow-light">YOUR STAY STARTS HERE</p><h1>Make a booking request</h1><p>Your request will remain pending until the lodge confirms availability and final terms.</p></div></section>
    <section class="section wrap booking-layout">
        <form class="booking-form" method="POST" action="{{ route('public.booking.submit') }}" data-booking-form data-currency="{{ $site['settings']->currency_code ?: config('website.currency') }}" data-nights="{{ $dates ? \Carbon\CarbonImmutable::createFromFormat('Y-m-d', $dates['check_in_date'])->diffInDays(\Carbon\CarbonImmutable::createFromFormat('Y-m-d', $dates['check_out_date'])) : 0 }}">
            @csrf
            <input type="hidden" name="submission_token" value="{{ old('submission_token', $submissionToken) }}">
            <div class="honeypot" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
            <section class="form-section" aria-labelledby="stay-heading"><span class="step-number">01</span><div class="form-section-heading"><p class="eyebrow">YOUR DATES &amp; ROOM</p><h2 id="stay-heading">Tell us about your stay</h2></div>
                <div class="form-grid">
                    <label class="form-field"><span>Check-in <b>*</b></span><input type="date" name="check_in_date" required min="{{ now('Africa/Dar_es_Salaam')->toDateString() }}" value="{{ old('check_in_date', $dates['check_in_date'] ?? '') }}">@error('check_in_date')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="form-field"><span>Check-out <b>*</b></span><input type="date" name="check_out_date" required min="{{ now('Africa/Dar_es_Salaam')->addDay()->toDateString() }}" value="{{ old('check_out_date', $dates['check_out_date'] ?? '') }}">@error('check_out_date')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="form-field form-field-wide"><span>Room category <b>*</b></span><select name="room_type" required data-rate-select><option value="">Choose a room</option>@foreach($roomTypes as $room)<option value="{{ $room->type }}" data-rate="{{ $room->rate }}" data-capacity="{{ $room->capacity ?? '' }}" @disabled($room->available === 0) @selected(old('room_type', $selectedType) === $room->type)>{{ $room->type }} — {{ number_format($room->rate, 0) }} {{ $site['settings']->currency_code ?: config('website.currency') }} per night{{ $room->available !== null ? ' · '.$room->available.' available' : '' }}</option>@endforeach</select>@error('room_type')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="form-field"><span>Adults <b>*</b></span><input type="number" min="1" max="20" name="number_of_adults" value="{{ old('number_of_adults', request('adults', 1)) }}" required>@error('number_of_adults')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="form-field"><span>Children</span><input type="number" min="0" max="20" name="number_of_children" value="{{ old('number_of_children', request('children', 0)) }}">@error('number_of_children')<small class="field-error">{{ $message }}</small>@enderror</label>
                </div>
            </section>
            <section class="form-section" aria-labelledby="guest-heading"><span class="step-number">02</span><div class="form-section-heading"><p class="eyebrow">GUEST DETAILS</p><h2 id="guest-heading">How can we reach you?</h2></div>
                <div class="form-grid">
                    <label class="form-field form-field-wide"><span>Full name <b>*</b></span><input type="text" name="name" autocomplete="name" maxlength="160" required value="{{ old('name') }}">@error('name')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="form-field"><span>Email <b>*</b></span><input type="email" name="email" autocomplete="email" maxlength="255" required value="{{ old('email') }}">@error('email')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="form-field"><span>Phone <b>*</b></span><input type="tel" name="phone" autocomplete="tel" maxlength="50" required value="{{ old('phone') }}">@error('phone')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="form-field form-field-wide"><span>Special requests <small>(optional)</small></span><textarea name="special_requests" rows="4" maxlength="2000" placeholder="Share anything you would like the lodge to know.">{{ old('special_requests') }}</textarea>@error('special_requests')<small class="field-error">{{ $message }}</small>@enderror</label>
                </div>
            </section>
            <section class="terms-summary"><p class="eyebrow">BEFORE YOU SEND</p><h2>Review the booking terms</h2><p>Your request is pending. No payment is collected through this form, and a room is not confirmed until the lodge contacts you.</p>
                <div class="policy-mini"><strong>Payment terms</strong><span>{{ $site['settings']->payment_policy_approved ? 'Approved policy' : 'Draft policy — confirm terms with the lodge' }}</span><a href="{{ route('public.policy', 'payment') }}">Read policy</a></div>
                <div class="policy-mini"><strong>Cancellation terms</strong><span>{{ $site['settings']->cancellation_policy_approved ? 'Approved policy' : 'Draft policy — confirm terms with the lodge' }}</span><a href="{{ route('public.policy', 'cancellation') }}">Read policy</a></div>
                <div class="policy-mini"><strong>Booking terms</strong><span>{{ $site['settings']->booking_policy_approved ? 'Approved policy' : 'Draft policy — confirm terms with the lodge' }}</span><a href="{{ route('public.policy', 'booking') }}">Read policy</a></div>
            </section>
            @if($roomTypes->isEmpty())<div class="form-alert">Online requests are not available yet. Please contact the lodge directly.</div>@endif
            <button class="button button-gold button-submit" type="submit" @disabled($roomTypes->isEmpty())>Send booking request <span aria-hidden="true">↗</span></button>
            <p class="fine-print">Your details will be used to respond to this booking request. See the <a href="{{ route('public.policy', 'privacy') }}">privacy policy</a>.</p>
        </form>
        <aside class="booking-aside"><div class="booking-aside-card"><p class="eyebrow">YOUR ESTIMATE</p><h2>Stay summary</h2><dl><div><dt>Room</dt><dd data-room-name>Choose a room</dd></div><div><dt>Nights</dt><dd data-night-count>{{ $dates ? \Carbon\CarbonImmutable::createFromFormat('Y-m-d', $dates['check_in_date'])->diffInDays(\Carbon\CarbonImmutable::createFromFormat('Y-m-d', $dates['check_out_date'])) : 'Choose dates' }}</dd></div><div><dt>Estimate</dt><dd data-price-total>—</dd></div></dl><p class="summary-note">The lodge confirms availability and final charges directly. This estimate uses the starting room rate and does not include fees or extras that have not been provided.</p></div>
            <div class="booking-aside-contact"><h3>Need help?</h3><p>Ask the lodge before sending your request.</p><a href="mailto:{{ $site['settings']->contact_email ?: config('website.email') }}">{{ $site['settings']->contact_email ?: config('website.email') }}</a>@if($site['phone'])<a href="tel:{{ $site['phone'] }}">{{ $site['phone'] }}</a>@endif</div></aside>
    </section>
@endsection
