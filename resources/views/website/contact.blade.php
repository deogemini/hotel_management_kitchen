@extends('website.layout')
@section('title', 'Contact')
@section('description', 'Contact Hard Rock Executive Lodge about room availability, rates, or your stay.')

@section('content')
    <section class="page-hero page-hero-compact"><div class="wrap"><p class="eyebrow eyebrow-light">WE’RE HERE TO HELP</p><h1>Get in touch</h1><p>Send a question and the lodge team will follow up using the details you provide.</p></div></section>
    <section class="section wrap contact-layout">
        <div class="contact-details"><p class="eyebrow">CONTACT THE LODGE</p><h2>We’d be glad to hear from you.</h2><p>For questions about rooms, rates, or booking terms, contact the lodge directly.</p>
            <div class="contact-detail"><span class="contact-icon" aria-hidden="true">✉</span><div><small>Email</small><a href="mailto:{{ $site['settings']->contact_email ?: config('website.email') }}">{{ $site['settings']->contact_email ?: config('website.email') }}</a></div></div>
            @if($site['phone'])<div class="contact-detail"><span class="contact-icon" aria-hidden="true">☎</span><div><small>Phone</small><a href="tel:{{ $site['phone'] }}">{{ $site['phone'] }}</a></div></div>@endif
            @if($site['location'])<div class="contact-detail"><span class="contact-icon" aria-hidden="true">⌖</span><div><small>{{ $site['settings']->address ? 'Address' : 'Location' }}</small><p>{{ $site['location'] }}</p>@if($site['settings']->map_url)<a href="{{ $site['settings']->map_url }}" target="_blank" rel="noopener">Directions ↗</a>@endif</div></div>@endif
            @if($site['settings']->whatsapp_number)@php($whatsappDigits = preg_replace('/\D+/', '', $site['settings']->whatsapp_number)) @if($whatsappDigits)<div class="contact-detail"><span class="contact-icon" aria-hidden="true">◉</span><div><small>WhatsApp</small><a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">Message the lodge ↗</a></div></div>@endif @endif
        </div>
        <div class="contact-form-card">
            @if(session('enquiry_sent'))<div class="form-success" role="status"><strong>Thank you for your message.</strong><p>Your enquiry has been saved for the lodge team.</p></div>@endif
            <form method="POST" action="{{ route('public.contact.submit') }}" class="contact-form">@csrf
                <div class="honeypot" aria-hidden="true"><label for="contact-website">Leave this field empty</label><input id="contact-website" name="website" tabindex="-1" autocomplete="off"></div>
                <label class="form-field"><span>Your name <b>*</b></span><input type="text" name="name" autocomplete="name" maxlength="160" required value="{{ old('name') }}">@error('name')<small class="field-error">{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Email <b>*</b></span><input type="email" name="email" autocomplete="email" maxlength="255" required value="{{ old('email') }}">@error('email')<small class="field-error">{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Phone <small>(optional)</small></span><input type="tel" name="phone" autocomplete="tel" maxlength="50" value="{{ old('phone') }}">@error('phone')<small class="field-error">{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Your message <b>*</b></span><textarea name="message" rows="6" maxlength="4000" required>{{ old('message') }}</textarea>@error('message')<small class="field-error">{{ $message }}</small>@enderror</label>
                <button class="button button-dark button-full" type="submit">Send enquiry <span aria-hidden="true">↗</span></button>
                <p class="fine-print">Your information will be used to respond to this enquiry. Read our <a href="{{ route('public.policy', 'privacy') }}">privacy policy</a>.</p>
            </form>
        </div>
    </section>
@endsection
