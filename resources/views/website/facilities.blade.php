@extends('website.layout')
@section('title', 'Facilities & Services')
@section('description', 'View the facilities and services currently listed by Hard Rock Executive Lodge.')

@section('content')
    <section class="page-hero page-hero-compact"><div class="wrap"><p class="eyebrow eyebrow-light">THE DETAILS OF YOUR STAY</p><h1>Facilities &amp; services</h1><p>Services shown here are managed by the lodge.</p></div></section>
    <section class="section wrap">
        @if($services->isNotEmpty())
            <div class="service-grid service-grid-full">@foreach($services as $service)<article class="service-card service-card-large">@if($service->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ $service->title }}" loading="lazy">@else<span class="service-icon" aria-hidden="true">✦</span>@endif<h2>{{ $service->title }}</h2><p>{{ $service->description }}</p></article>@endforeach</div>
        @else
            <div class="empty-panel"><span class="empty-icon">✦</span><h2>Service details are being updated</h2><p>Please contact the lodge to confirm which services are available during your stay.</p><a class="button button-dark" href="{{ route('public.contact') }}">Ask the lodge</a></div>
        @endif
    </section>
@endsection
