<x-guest-layout>
    <div class="relative h-screen flex flex-col lg:flex-row transition-all duration-300">
        <!-- Background image for small screens -->
        <div class="absolute inset-0 lg:hidden bg-cover bg-center opacity-30 dark:opacity-40 transition-all duration-300" 
            style="background-image: url('https://media.istockphoto.com/id/1282636502/vector/business-people-studying-list-of-rules.jpg?s=612x612&w=0&k=20&c=XkjLnnQS9PfaVfRxENtaz4_Km9h9u5A3SgeHar7-Mwc=');">
        </div>

        <!-- Left side image for large screens -->
        <div class="hidden lg:block lg:w-1/2 bg-cover bg-center transition-all duration-300"
            style="background-image: url('https://media.istockphoto.com/id/1282636502/vector/business-people-studying-list-of-rules.jpg?s=612x612&w=0&k=20&c=XkjLnnQS9PfaVfRxENtaz4_Km9h9u5A3SgeHar7-Mwc=');">
        </div>

        <!-- Right side (login form) -->
        <div class="relative z-10 w-full lg:w-1/2 flex justify-center items-center p-6 bg-white/90 dark:bg-gray-900/80 lg:bg-white dark:lg:bg-gray-900 transition-all duration-300">
            <x-authentication-card>
                <x-slot name="logo">
                    <x-authentication-card-logo />
                </x-slot>

                <x-validation-errors class="mb-4" />

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="transition-all duration-300">
                    @csrf

                    <div>
                        <x-label for="email" value="{{ __('Email') }}" class="dark:text-gray-200" />
                        <x-input id="email" class="block mt-1 w-full dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" 
                            type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    </div>

                    <div class="mt-4">
                        <x-label for="password" value="{{ __('Password') }}" class="dark:text-gray-200" />
                        <x-input id="password" class="block mt-1 w-full dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" 
                            type="password" name="password" required autocomplete="current-password" />
                    </div>

                    <div class="block mt-4">
                        <label for="remember_me" class="flex items-center">
                            <x-checkbox id="remember_me" name="remember" class="dark:border-gray-600" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-3">
                        <div class="flex items-center gap-3">
                            @if (Route::has('password.request'))
                                <a class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition-colors" 
                                   href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif

                            @if (Route::has('register'))
                                <a class="underline text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors" 
                                   href="{{ route('register') }}">
                                    {{ __('Register') }}
                                </a>
                            @endif
                        </div>

                        <x-button class="ms-0 sm:ms-4 transition-all duration-300 hover:scale-105">
                            {{ __('Log in') }}
                        </x-button>
                    </div>
                </form>
            </x-authentication-card>
        </div>
    </div>
</x-guest-layout>
