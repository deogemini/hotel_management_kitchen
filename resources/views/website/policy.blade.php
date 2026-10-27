@extends('website.layout')
@section('title', $activePolicy['title'])
@section('description', $activePolicy['title'].' for Hard Rock Executive Lodge.')

@section('content')
    <section class="page-hero page-hero-compact"><div class="wrap"><p class="eyebrow eyebrow-light">GUEST INFORMATION</p><h1>{{ $activePolicy['title'] }}</h1><p>Policy information for planning your stay.</p></div></section>
    <section class="section wrap policy-page">
        @unless($activePolicy['approved'])<div class="draft-notice"><strong>Draft policy — please confirm the terms with the lodge.</strong><p>This policy has not been approved for publication. Any online booking request remains pending confirmation.</p></div>@endunless
        @if($activePolicy['content'])<div class="policy-content">{!! nl2br(e($activePolicy['content'])) !!}</div>@else<p class="policy-content">Policy details have not yet been supplied. Please contact the lodge to confirm the terms that apply to your stay.</p>@endif
        <a class="text-link" href="{{ route('public.contact') }}">Ask us about this policy <span aria-hidden="true">→</span></a>
    </section>
@endsection
