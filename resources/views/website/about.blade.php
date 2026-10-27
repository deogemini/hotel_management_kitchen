@extends('website.layout')
@section('title', 'About Us')
@section('description', 'Learn about Hard Rock Executive Lodge and find configured stay information.')

@section('content')
    <section class="page-hero page-hero-compact"><div class="wrap"><p class="eyebrow eyebrow-light">ABOUT THE LODGE</p><h1>A place to make your stay your own.</h1><p>{{ $site['settings']->introduction_title ?: 'Hard Rock Executive Lodge' }}</p></div></section>
    <section class="section wrap about-layout">
        <div class="about-main"><p class="eyebrow">WELCOME</p><h2>{{ $site['settings']->introduction_title ?: 'We look forward to welcoming you.' }}</h2><p>{{ $site['settings']->introduction ?: 'Thank you for considering Hard Rock Executive Lodge. Explore the room information currently available on this website, or reach out to the lodge team with questions about a future visit.' }}</p>
            @if($site['settings']->guest_policies)<div class="about-policies"><h3>Guest information</h3><p>{{ $site['settings']->guest_policies }}</p></div>@endif
            @if($site['location'])<div class="about-policies"><h3>Location</h3><p>{{ $site['location'] }}</p>@if($site['settings']->map_url)<a class="text-link" href="{{ $site['settings']->map_url }}" target="_blank" rel="noopener">Open directions <span aria-hidden="true">↗</span></a>@endif</div>@endif
        </div>
        <aside class="about-side"><p class="eyebrow">PLAN AHEAD</p><h2>Stay information</h2><dl class="stay-facts"><div><dt>Check-in</dt><dd>{{ $site['settings']->check_in_time ?: 'Please confirm with the lodge' }}</dd></div><div><dt>Check-out</dt><dd>{{ $site['settings']->check_out_time ?: 'Please confirm with the lodge' }}</dd></div><div><dt>Guest policies</dt><dd>{{ $site['settings']->guest_policies ? 'See details provided by the lodge' : 'Please contact the lodge' }}</dd></div></dl><a class="button button-dark button-full" href="{{ route('public.contact') }}">Ask a question <span aria-hidden="true">↗</span></a></aside>
    </section>
@endsection
