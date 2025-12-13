<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <x-authentication-card>
            <x-slot name="logo">
                <x-authentication-card-logo />
            </x-slot>

            <div class="mb-4 text-sm text-gray-600 dark:text-gray-400 text-center">
                {{ __('Please enter the 6-digit code sent to') }} 
                <span class="font-bold">{{ session('identifier') ?? old('identifier') }}</span>
            </div>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('login.otp.verify') }}">
                @csrf

                <!-- Hidden Identifier Field -->
                <input type="hidden" name="identifier" value="{{ session('identifier') ?? old('identifier') }}">

                <div class="mt-4">
                    <x-label for="otp" value="{{ __('Verification Code') }}" />
                    <x-input id="otp" class="block mt-1 w-full text-center text-2xl tracking-widest" 
                             type="text" name="otp" required autofocus autocomplete="one-time-code" 
                             maxlength="6" placeholder="123456" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a href="{{ route('login') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('Cancel') }}
                    </a>

                    <x-button class="ms-4">
                        {{ __('Verify & Login') }}
                    </x-button>
                </div>
            </form>
        </x-authentication-card>
    </div>
</x-guest-layout>