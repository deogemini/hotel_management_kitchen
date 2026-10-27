@extends('website.layout')
@section('title', $room->type.' Room')
@section('description', 'View details and current rate for the '.$room->type.' room category at Hard Rock Executive Lodge.')

@section('content')
    <section class="section wrap room-detail">
        <p class="breadcrumb"><a href="{{ route('public.rooms') }}">Rooms &amp; rates</a><span>/</span>{{ $room->type }}</p>
        <div class="room-detail-grid">
            <div class="room-detail-photo">
                @if(! $room->image_is_placeholder || app()->isLocal())<img src="{{ $room->image_url }}" alt="{{ $room->type }} room" fetchpriority="high">@if($room->image_is_placeholder)<span class="photo-note">Sample photo — replace with lodge photography</span>@endif
                @else<span class="room-placeholder room-placeholder-large"><span>HR</span><small>Room photo coming soon</small></span>@endif
                @if($room->images->count() > 1)<div class="room-thumbnails">@foreach($room->images as $photo)<img src="{{ $photo }}" alt="Additional view of {{ $room->type }} room" loading="lazy">@endforeach</div>@endif
            </div>
            <div class="room-detail-copy">
                <p class="eyebrow">ROOM CATEGORY</p><h1>{{ $room->type }}</h1>
                <p class="room-detail-description">{{ $room->description ?: 'Contact the lodge for further details about this room category.' }}</p>
                <div class="detail-rate"><span>Starting rate</span><strong>{{ number_format($room->rate, 0) }} <small>{{ $site['settings']->currency_code ?: config('website.currency') }}</small></strong><span>per night · final rate confirmed by the lodge</span></div>
                <dl class="room-facts">
                    <div><dt>Bed type</dt><dd>{{ $room->bed_type ?: 'Contact the lodge' }}</dd></div>
                    <div><dt>Guest capacity</dt><dd>{{ $room->capacity ? 'Up to '.$room->capacity.' guests' : 'Contact the lodge' }}</dd></div>
                    <div><dt>Tax</dt><dd>{{ $room->tax_included === null ? 'Ask the lodge' : ($room->tax_included ? 'Included' : 'Not included') }}</dd></div>
                    <div><dt>Breakfast</dt><dd>{{ $room->breakfast_included === null ? 'Ask the lodge' : ($room->breakfast_included ? 'Included' : 'Not included') }}</dd></div>
                </dl>
                @if($room->features->isNotEmpty())<div class="detail-features"><h2>Listed room features</h2><ul>@foreach($room->features as $feature)<li>{{ $feature }}</li>@endforeach</ul></div>@endif
                @if($room->available !== null)<p class="availability-note">{{ $room->available > 0 ? $room->available.' rooms available for these dates' : 'No rooms available for these dates' }}</p>@endif
                <a class="button button-dark button-full" href="{{ route('public.booking', ['room_type' => $room->type] + ($dates ?? []) + request()->only('adults', 'children')) }}">Request this room <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </section>
@endsection
