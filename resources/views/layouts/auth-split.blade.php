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
        <div class="min-h-screen bg-slate-950">
            <div class="grid min-h-screen grid-cols-1 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="relative hidden overflow-hidden lg:block">
                    <img
                        id="authSideImage"
                        src="{{ asset('img/rooms.jpg') }}"
                        alt="Hotel room interior"
                        class="absolute inset-0 h-full w-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/70 via-slate-950/30 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-12 text-white">
                        <div class="max-w-xl">
<p class="inline-block bg-yellow-400 px-4 py-2 text-4xl font-black uppercase text-black">
  Hard Rock Executive Lodge
</p>
                            <h1 class="mt-4 text-5xl font-bold leading-tight">Hotel service, rooms, guests, and payments in one calm workspace.</h1>
                            <div class="mt-8 grid grid-cols-3 gap-3 text-sm">
                                <div class="rounded-lg bg-white/15 p-4 backdrop-blur">
                                    <div class="text-2xl font-bold">Rooms</div>
                                    <div class="text-cyan-100">Availability</div>
                                </div>
                                <div class="rounded-lg bg-white/15 p-4 backdrop-blur">
                                    <div class="text-2xl font-bold">Kitchen</div>
                                    <div class="text-cyan-100">Live orders</div>
                                </div>
                                <div class="rounded-lg bg-white/15 p-4 backdrop-blur">
                                    <div class="text-2xl font-bold">Billing</div>
                                    <div class="text-cyan-100">Receipts</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <main class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.20),_transparent_32%),linear-gradient(135deg,_#f8fafc,_#eef2f7)] px-4 py-10 sm:px-6">
                    <div class="w-full max-w-md">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <script>
            (function() {
                const imageElement = document.getElementById('authSideImage');
                if (!imageElement) {
                    return;
                }

                const images = [
                    '{{ asset('img/rooms.jpg') }}',
                    '{{ asset('img/hotel-room-service.webp') }}',
                    '{{ asset('img/breakfast.jpeg') }}',
                    '{{ asset('img/menuatbed.jpg') }}'
                ];
                let currentIndex = 0;

                setInterval(() => {
                    currentIndex = (currentIndex + 1) % images.length;
                    imageElement.src = images[currentIndex];
                }, 6000);
            })();
        </script>
    </body>
</html>
