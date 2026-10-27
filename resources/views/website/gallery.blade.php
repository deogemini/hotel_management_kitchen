@extends('website.layout')
@section('title', 'Gallery')
@section('description', 'View photographs published by Hard Rock Executive Lodge.')

@section('content')
    <section class="page-hero page-hero-compact"><div class="wrap"><p class="eyebrow eyebrow-light">A CLOSER LOOK</p><h1>Gallery</h1><p>Photographs published by the lodge.</p></div></section>
    <section class="section wrap">
        @if($photos->isNotEmpty())
            <div class="gallery-filters" role="group" aria-label="Filter gallery photos">@foreach(['All' => 'all', 'Rooms' => 'rooms', 'Exterior' => 'exterior', 'Dining' => 'dining', 'Facilities' => 'facilities'] as $label => $filter)<button type="button" data-gallery-filter="{{ $filter }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}</button>@endforeach</div>
            <div class="gallery-grid">@foreach($photos as $photo)<button class="gallery-item" type="button" data-gallery-category="{{ \Illuminate\Support\Str::slug($photo['category']) }}" data-gallery-image="{{ $photo['url'] }}" data-gallery-caption="{{ $photo['title'] }} — {{ $photo['category'] }}" aria-label="Enlarge image: {{ $photo['title'] }}"><img src="{{ $photo['url'] }}" alt="{{ $photo['title'] }}" loading="lazy"><span>{{ $photo['title'] }} <small>· {{ $photo['category'] }}</small></span></button>@endforeach</div>
            <dialog class="gallery-dialog" aria-label="Enlarged gallery photograph"><button type="button" class="dialog-close" aria-label="Close image">×</button><img alt=""><p></p></dialog>
        @else
            <div class="empty-panel"><span class="empty-icon">▧</span><h2>Photographs are being prepared</h2><p>Approved lodge photographs will be added to the gallery.</p></div>
        @endif
    </section>
@endsection
