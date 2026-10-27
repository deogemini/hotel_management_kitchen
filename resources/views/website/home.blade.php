@extends('website.layout')

@section('title', 'Welcome')
@section('description', 'Welcome to Hard Rock Executive Lodge. Explore room categories, view current rates, and send a booking request.')

@section('content')
    <section class="hero">
        @if($site['settings']->hero_image_path)
            <img class="hero-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($site['settings']->hero_image_path) }}" alt="Hard Rock Executive Lodge" fetchpriority="high">
        @elseif(app()->isLocal())
            <img class="hero-image" src="{{ asset('img/rooms.jpg') }}" alt="Sample room photo for layout preview" fetchpriority="high">
            <span class="photo-note">Sample photo — replace in Website management</span>
        @endif
        <div class="hero-shade"></div>
        <div class="hero-content wrap">
            <p class="eyebrow eyebrow-light"><span></span> WELCOME TO</p>
            <h1>{{ $site['settings']->hero_title ?: config('website.name') }}</h1>
            <p class="hero-copy">{{ $site['settings']->hero_subtitle ?: 'A stay shaped around the details that matter to you.' }}</p>
            <div class="hero-actions">
                <a class="button button-gold" href="{{ route('public.booking') }}">Book your stay <span aria-hidden="true">↗</span></a>
                <a class="text-link text-link-light" href="{{ route('public.rooms') }}">Explore rooms <span aria-hidden="true">→</span></a>
            </div>
            <div class="hero-caption">Hard Rock Executive Lodge</div>
        </div>
    </section>

    <section class="availability-band" aria-labelledby="availability-title">
        <form class="availability-form wrap" action="{{ route('public.rooms') }}" method="GET">
            <div class="availability-intro"><span class="eyebrow">PLAN YOUR STAY</span><strong id="availability-title">Check availability</strong></div>
            <label class="field"><span>Check-in</span><input type="date" name="check_in_date" required min="{{ now('Africa/Dar_es_Salaam')->toDateString() }}" value="{{ request('check_in_date') }}"></label>
            <label class="field"><span>Check-out</span><input type="date" name="check_out_date" required min="{{ now('Africa/Dar_es_Salaam')->addDay()->toDateString() }}" value="{{ request('check_out_date') }}"></label>
            <label class="field field-count"><span>Adults</span><select name="adults"><option>1</option><option>2</option><option>3</option><option>4</option></select></label>
            <label class="field field-count"><span>Children</span><select name="children"><option>0</option><option>1</option><option>2</option><option>3</option></select></label>
            <button class="button button-dark" type="submit">Check dates <span aria-hidden="true">→</span></button>
        </form>
    </section>

    <section class="section section-intro wrap">
        <div class="section-heading">
            <p class="eyebrow">A WARM WELCOME</p>
            <h2>{{ $site['settings']->introduction_title ?: 'Make room for a good stay.' }}</h2>
        </div>
        <div class="intro-copy">
            <p>{{ $site['settings']->introduction ?: 'We’re glad you’re considering a stay with us. Explore the room categories and published rates, then contact the lodge for details tailored to your visit.' }}</p>
            @if($site['location'])<p class="location-note">{{ $site['location'] }}</p>@endif
            <a class="text-link" href="{{ route('public.about') }}">More about the lodge <span aria-hidden="true">→</span></a>
        </div>
    </section>

    <section class="section section-muted">
        <div class="wrap">
            <div class="section-topline"><div><p class="eyebrow">STAY YOUR WAY</p><h2>Rooms &amp; rates</h2></div><a class="text-link" href="{{ route('public.rooms') }}">View all rooms <span aria-hidden="true">→</span></a></div>
            @if($roomTypes->isNotEmpty())
                <div class="room-grid">
                    @foreach($roomTypes->take(3) as $room)
                        @include('website.partials.room-card', ['room' => $room, 'site' => $site])
                    @endforeach
                </div>
            @else
                <div class="empty-panel"><span class="empty-icon">⌂</span><h3>Room details are being prepared</h3><p>Contact the lodge for current room information and rates.</p><a class="button button-outline" href="{{ route('public.contact') }}">Contact the lodge</a></div>
            @endif
        </div>
    </section>

    @if($services->isNotEmpty())
        <section class="section wrap">
            <div class="section-topline"><div><p class="eyebrow">HERE FOR YOUR STAY</p><h2>Facilities &amp; services</h2></div><a class="text-link" href="{{ route('public.facilities') }}">Explore services <span aria-hidden="true">→</span></a></div>
            <div class="service-grid">
                @foreach($services->take(3) as $service)
                    <article class="service-card">
                        @if($service->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ $service->title }}" loading="lazy">@else<span class="service-icon" aria-hidden="true">✦</span>@endif
                        <h3>{{ $service->title }}</h3><p>{{ $service->description }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($offers->isNotEmpty())
        <section class="offers-section">
            <div class="wrap offer-wrap"><div><p class="eyebrow eyebrow-light">A LITTLE EXTRA</p><h2>Current offers</h2></div><div class="offer-list">
                @foreach($offers->take(2) as $offer)<article><h3>{{ $offer->title }}</h3><p>{{ $offer->description }}</p></article>@endforeach
            </div></div>
        </section>
    @endif

    <section class="section wrap gallery-preview">
        <div class="section-topline"><div><p class="eyebrow">A LOOK AROUND</p><h2>From the lodge</h2></div><a class="text-link" href="{{ route('public.gallery') }}">Visit gallery <span aria-hidden="true">→</span></a></div>
        @php($preview = $gallery->filter(fn($entry) => $entry->image_path)->take(3))
        @if($preview->isNotEmpty())
            <div class="gallery-strip">@foreach($preview as $photo)<a href="{{ route('public.gallery') }}"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->image_path) }}" alt="{{ $photo->title }}" loading="lazy"></a>@endforeach</div>
        @elseif($roomTypes->contains(fn($room) => ! $room->image_is_placeholder))
            <div class="gallery-strip">@foreach($roomTypes->filter(fn($room) => ! $room->image_is_placeholder)->take(3) as $room)<a href="{{ route('public.room', $room->slug) }}"><img src="{{ $room->image_url }}" alt="{{ $room->type }} room" loading="lazy"></a>@endforeach</div>
        @else
            <div class="gallery-empty"><span aria-hidden="true">▧</span><p>Approved lodge photographs will appear here.</p></div>
        @endif
    </section>

    <section class="contact-cta">
        <div class="wrap contact-cta-inner"><div><p class="eyebrow eyebrow-light">WE’RE HAPPY TO HELP</p><h2>Let’s plan your stay.</h2><p>Ask us about dates, room details, or anything you need to know before you arrive.</p></div><div class="cta-actions"><a class="button button-gold" href="{{ route('public.booking') }}">Send a booking request <span aria-hidden="true">↗</span></a><a class="text-link text-link-light" href="{{ route('public.contact') }}">Contact us <span aria-hidden="true">→</span></a></div></div>
    </section>
@endsection
