<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Hotel Management System') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="relative min-h-screen overflow-hidden bg-slate-950">
            <img src="{{ asset('img/menuatserve.jpg') }}" alt="Hotel service" class="absolute inset-0 h-full w-full object-cover opacity-35">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950/90 via-slate-900/75 to-cyan-950/70"></div>

            <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-8">
                <a href="/" class="mb-6 text-center text-white">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-600 shadow-lg shadow-cyan-600/25">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M4 21V9l8-6 8 6v12M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01" />
                        </svg>
                    </div>
                    <div class="text-lg font-bold">{{ config('app.name', 'Hotel Management System') }}</div>
                </a>

                <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-white/20 sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
