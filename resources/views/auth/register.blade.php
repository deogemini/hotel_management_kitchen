<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="0712345678" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10"
                              type="password"
                              name="password"
                              required autocomplete="new-password" />
                <button type="button" id="togglePasswordReg"
                        class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-700"
                        aria-label="Onyesha/KFicha Nywila">
                    <svg id="iconEyeReg" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <circle cx="12" cy="12" r="3" stroke-width="2" stroke="currentColor"></circle>
                    </svg>
                    <svg id="iconEyeOffReg" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.06 10.06 0 012.223-3.592m3.68-2.507A9.967 9.967 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.996 9.996 0 01-4.122 5.225M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative">
                <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10"
                              type="password"
                              name="password_confirmation" required autocomplete="new-password" />
                <button type="button" id="togglePasswordConfirm"
                        class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-700"
                        aria-label="Onyesha/KFicha Nywila">
                    <svg id="iconEyeConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <circle cx="12" cy="12" r="3" stroke-width="2" stroke="currentColor"></circle>
                    </svg>
                    <svg id="iconEyeOffConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.06 10.06 0 012.223-3.592m3.68-2.507A9.967 9.967 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.996 9.996 0 01-4.122 5.225M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
    <script>
        (function() {
            function bindToggle(inputId, btnId, eyeId, eyeOffId) {
                const input = document.getElementById(inputId);
                const btn = document.getElementById(btnId);
                const eye = document.getElementById(eyeId);
                const eyeOff = document.getElementById(eyeOffId);
                if (btn && input) {
                    btn.addEventListener('click', function() {
                        const showing = input.type === 'text';
                        input.type = showing ? 'password' : 'text';
                        eye.classList.toggle('hidden', !showing);
                        eyeOff.classList.toggle('hidden', showing);
                    });
                }
            }
            bindToggle('password', 'togglePasswordReg', 'iconEyeReg', 'iconEyeOffReg');
            bindToggle('password_confirmation', 'togglePasswordConfirm', 'iconEyeConfirm', 'iconEyeOffConfirm');
        })();
    </script>
</x-guest-layout>
