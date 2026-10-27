<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hard Rock Executive Lodge') | Hard Rock Executive Lodge</title>
    <meta name="description" content="@yield('description', 'Explore rooms, check availability, and contact Hard Rock Executive Lodge.')">
    <meta name="theme-color" content="#171916">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/lodge.css') }}">
    <script defer src="{{ asset('js/lodge.js') }}"></script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('public.home') }}" aria-label="Hard Rock Executive Lodge home">
                <span class="brand-mark" aria-hidden="true">HR</span>
                <span class="brand-text"><strong>HARD ROCK</strong><small>EXECUTIVE LODGE</small></span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
                <span class="sr-only">Open navigation</span><span></span><span></span><span></span>
            </button>
            <nav class="primary-nav" id="primary-navigation" aria-label="Main navigation">
                <a href="{{ route('public.home') }}" @class(['active' => request()->routeIs('public.home')])>Home</a>
                <a href="{{ route('public.rooms') }}" @class(['active' => request()->routeIs('public.rooms', 'public.room')])>Rooms &amp; Rates</a>
                <a href="{{ route('public.facilities') }}" @class(['active' => request()->routeIs('public.facilities')])>Facilities</a>
                <a href="{{ route('public.gallery') }}" @class(['active' => request()->routeIs('public.gallery')])>Gallery</a>
                <a href="{{ route('public.about') }}" @class(['active' => request()->routeIs('public.about')])>About</a>
                <a href="{{ route('public.contact') }}" @class(['active' => request()->routeIs('public.contact')])>Contact</a>
            @auth
                    <a class="nav-login" href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="nav-logout">@csrf<button type="submit">Logout</button></form>
                @else
                    <a class="nav-login" href="{{ route('login') }}">Login</a>
                @endauth
                <a class="button button-gold nav-book" href="{{ route('public.booking') }}">Book now <span aria-hidden="true">↗</span></a>
            </nav>
        </div>
    </header>

    @if(session('success'))
        <div class="flash-message" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="flash-message flash-error" role="alert">Please review the highlighted fields and try again.</div>
    @endif

    <main id="main">@yield('content')</main>

    <footer class="site-footer">
        <div class="footer-main wrap">
            <div class="footer-about">
                <a class="brand brand-footer" href="{{ route('public.home') }}">
                    <span class="brand-mark" aria-hidden="true">HR</span>
                    <span class="brand-text"><strong>HARD ROCK</strong><small>EXECUTIVE LODGE</small></span>
                </a>
                <p>Plan your stay with Hard Rock Executive Lodge. Our team is happy to help with room and booking questions.</p>
            </div>
            <div>
                <h2>Explore</h2>
                <a href="{{ route('public.rooms') }}">Rooms &amp; rates</a>
                <a href="{{ route('public.facilities') }}">Facilities &amp; services</a>
                <a href="{{ route('public.gallery') }}">Gallery</a>
                <a href="{{ route('public.about') }}">About us</a>
            </div>
            <div>
                <h2>Contact</h2>
                <a href="mailto:{{ $site['settings']->contact_email ?: config('website.email') }}">{{ $site['settings']->contact_email ?: config('website.email') }}</a>
                @if($site['phone'])<a href="tel:{{ $site['phone'] }}">{{ $site['phone'] }}</a>@endif
                @if($site['settings']->address)<p>{{ $site['settings']->address }}</p>@endif
            </div>
            <div>
                <h2>Policies</h2>
                <a href="{{ route('public.policy', 'booking') }}">Booking</a>
                <a href="{{ route('public.policy', 'cancellation') }}">Cancellation</a>
                <a href="{{ route('public.policy', 'payment') }}">Payment</a>
                <a href="{{ route('public.policy', 'privacy') }}">Privacy</a>
            </div>
        </div>
        <div class="footer-bottom wrap">
            <span>© {{ now()->year }} Hard Rock Executive Lodge</span>
            @if($site['settings']->whatsapp_number)
                @php($whatsappDigits = preg_replace('/\D+/', '', $site['settings']->whatsapp_number))
                @if($whatsappDigits)<a class="whatsapp-link" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">WhatsApp us ↗</a>@endif
            @endif
            @if(auth()->user()?->hasPermission('website.manage'))
                <a href="{{ route('website.admin.index') }}">Website management</a>
            @endif
        </div>
    </footer>
</body>
</html>
