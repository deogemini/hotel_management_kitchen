@extends('website.layout')
@section('title', 'Rooms & Rates')
@section('description', 'Browse room categories and current nightly rates at Hard Rock Executive Lodge.')

@section('content')
    <section class="page-hero page-hero-compact"><div class="wrap"><p class="eyebrow eyebrow-light">FIND YOUR ROOM</p><h1>Rooms &amp; rates</h1><p>Explore the room categories and information currently provided by the lodge.</p></div></section>
    <section class="section wrap">
        <form class="availability-form availability-form-page" action="{{ route('public.rooms') }}" method="GET">
            <div class="availability-intro"><span class="eyebrow">YOUR DATES</span><strong>Check availability</strong></div>
            <label class="field"><span>Check-in</span><input type="date" name="check_in_date" required min="{{ now('Africa/Dar_es_Salaam')->toDateString() }}" value="{{ $dates['check_in_date'] ?? '' }}"></label>
            <label class="field"><span>Check-out</span><input type="date" name="check_out_date" required min="{{ now('Africa/Dar_es_Salaam')->addDay()->toDateString() }}" value="{{ $dates['check_out_date'] ?? '' }}"></label>
            <label class="field field-count"><span>Adults</span><select name="adults">@for($n=1;$n<=8;$n++)<option value="{{ $n }}" @selected((int) request('adults', 1) === $n)>{{ $n }}</option>@endfor</select></label>
            <label class="field field-count"><span>Children</span><select name="children">@for($n=0;$n<=8;$n++)<option value="{{ $n }}" @selected((int) request('children', 0) === $n)>{{ $n }}</option>@endfor</select></label>
            <button class="button button-dark" type="submit">Check dates <span aria-hidden="true">→</span></button>
        </form>
        @if($roomTypes->isNotEmpty())
            <div class="room-grid room-grid-wide">@foreach($roomTypes as $room)@include('website.partials.room-card', ['room' => $room, 'site' => $site])@endforeach</div>
        @else
            <div class="empty-panel"><span class="empty-icon">⌂</span><h2>Room information is being updated</h2><p>Please contact the lodge for current availability and rates.</p><a class="button button-dark" href="{{ route('public.contact') }}">Contact the lodge</a></div>
        @endif
        <p class="fine-print">Published rates are estimates from the room management system. Tax and breakfast details are shown only when configured by lodge staff. Final availability and terms are confirmed by the lodge.</p>
    </section>
@endsection
