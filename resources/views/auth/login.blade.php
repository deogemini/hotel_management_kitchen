<x-auth-split-layout>
    <div class="overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
        <div class="h-36 bg-slate-900 sm:h-44">
            <img src="{{ asset('img/hotel-room-service.webp') }}" alt="Hotel room service" class="h-full w-full object-cover opacity-85">
        </div>

        <div class="p-6 sm:p-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="mb-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-600 text-white shadow-lg shadow-cyan-600/25">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M4 21V9l8-6 8 6v12M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Hotel Management System</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">Welcome back</h1>
                <p class="mt-2 text-sm text-slate-600">Sign in to manage guests, rooms, bookings, restaurant orders, and payments.</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="login" :value="__('Email or Phone Number')" />
                    <x-text-input id="login" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-cyan-600 focus:ring-cyan-600" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" placeholder="frontdesk@hotel.com or 0712345678" />
                    <x-input-error :messages="$errors->get('login')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <div class="relative">
                        <x-text-input id="password" class="mt-1 block w-full rounded-lg border-slate-300 pr-11 focus:border-cyan-600 focus:ring-cyan-600"
                                      type="password"
                                      name="password"
                                      required autocomplete="current-password" />
                        <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-slate-800"
                                aria-label="Show or hide password">
                            <svg id="iconEye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                <circle cx="12" cy="12" r="3" stroke-width="2" stroke="currentColor"></circle>
                            </svg>
                            <svg id="iconEyeOff" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.06 10.06 0 012.223-3.592m3.68-2.507A9.967 9.967 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.996 9.996 0 01-4.122 5.225M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-cyan-600 shadow-sm focus:ring-cyan-600" name="remember">
                        <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-cyan-700 hover:text-cyan-900" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-cyan-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-700/20 transition hover:bg-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:ring-offset-2">
                    <span>{{ __('Log in') }}</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const input = document.getElementById('password');
            const btn = document.getElementById('togglePassword');
            const eye = document.getElementById('iconEye');
            const eyeOff = document.getElementById('iconEyeOff');

            if (btn && input) {
                btn.addEventListener('click', function() {
                    const showing = input.type === 'text';
                    input.type = showing ? 'password' : 'text';
                    eye.classList.toggle('hidden', !showing);
                    eyeOff.classList.toggle('hidden', showing);
                });
            }
        })();
    </script>
</x-auth-split-layout>
