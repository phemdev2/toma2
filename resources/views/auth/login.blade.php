<x-guest-layout>
    <!-- Root Container: Full screen background with Alpine Data -->
    <div class="relative min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8" 
         x-data="{ loginMethod: 'password' }">

        <!-- Background Image -->
        <!-- Fixed position ensures it doesn't scroll with content on short screens -->
        <div class="fixed inset-0 z-0">
            <img src="https://media.istockphoto.com/id/1282636502/vector/business-people-studying-list-of-rules.jpg?s=612x612&w=0&k=20&c=XkjLnnQS9PfaVfRxENtaz4_Km9h9u5A3SgeHar7-Mwc=" 
                 alt="Background" 
                 class="w-full h-full object-cover" />
            <!-- Dark Overlay + Blur: Essential for text readability -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        </div>

        <!-- Centered Glass Card -->
        <div class="relative z-10 w-full max-w-md bg-white/90 dark:bg-gray-900/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden transition-all duration-300">
            
            <!-- Top Decorative Bar -->
            <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 w-full"></div>

            <div class="p-6 sm:p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="flex justify-center mb-4">
                        <x-authentication-card-logo />
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 hover:underline transition-all">
                            Sign up
                        </a>
                    </p>
                </div>

                <!-- Login Method Toggle Tabs -->
                <div class="relative mb-8 p-1 bg-gray-100/80 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-1 relative z-10">
                        <button @click="loginMethod = 'password'"
                                :class="loginMethod === 'password' ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-500 dark:text-gray-400 font-medium hover:text-gray-700 dark:hover:text-gray-300'"
                                class="w-full py-2.5 text-sm rounded-lg transition-colors duration-200 focus:outline-none">
                            Password
                        </button>
                        <button @click="loginMethod = 'otp'"
                                :class="loginMethod === 'otp' ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-500 dark:text-gray-400 font-medium hover:text-gray-700 dark:hover:text-gray-300'"
                                class="w-full py-2.5 text-sm rounded-lg transition-colors duration-200 focus:outline-none">
                            One-Time PIN
                        </button>
                    </div>
                    
                    <!-- Animated Slider Background -->
                    <div class="absolute top-1 bottom-1 w-[calc(50%-4px)] bg-white dark:bg-gray-700 rounded-lg shadow-sm transition-all duration-300 ease-spring"
                         :class="loginMethod === 'password' ? 'left-1' : 'left-[calc(50%)]'">
                    </div>
                </div>

                <x-validation-errors class="mb-4" />

                @if (session('status'))
                    <div class="mb-4 p-3 rounded-lg bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 flex items-center gap-3">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Password Login Form -->
                <div x-show="loginMethod === 'password'" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0">
                    
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div class="space-y-1">
                            <x-label for="email" value="{{ __('Email') }}" class="dark:text-gray-300 font-medium" />
                            <x-input id="email" class="block w-full bg-white/50 dark:bg-gray-800/50 border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl py-3" 
                                     type="email" name="email" :value="old('email')" required autofocus />
                        </div>

                        <div class="space-y-1">
                            <div class="flex justify-between items-center">
                                <x-label for="password" value="{{ __('Password') }}" class="dark:text-gray-300 font-medium" />
                                @if (Route::has('password.request'))
                                    <a class="text-xs text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 font-medium transition-colors" 
                                       href="{{ route('password.request') }}">
                                        Forgot?
                                    </a>
                                @endif
                            </div>
                            <x-input id="password" class="block w-full bg-white/50 dark:bg-gray-800/50 border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl py-3" 
                                     type="password" name="password" required autocomplete="current-password" />
                        </div>

                        <div class="flex items-center">
                            <x-checkbox id="remember_me" name="remember" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500" />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Remember me') }}</span>
                        </div>

                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-[1.02]">
                            {{ __('Sign in') }}
                        </button>
                    </form>
                </div>

                <!-- OTP Login Form -->
                <div x-show="loginMethod === 'otp'" x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0">
                    
                    <form method="POST" action="{{ route('login.otp.send') }}" class="space-y-5">
                        @csrf
                        <div class="space-y-1">
                            <x-label for="otp_identifier" value="{{ __('Email or Phone') }}" class="dark:text-gray-300 font-medium" />
                            <x-input id="otp_identifier" class="block w-full bg-white/50 dark:bg-gray-800/50 border-gray-300 dark:border-gray-600 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl py-3" 
                                     type="text" name="identifier" :value="old('identifier')" required placeholder="e.g. john@example.com" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                We'll send a secure code to your device. No password required.
                            </p>
                        </div>

                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all transform hover:scale-[1.02]">
                            {{ __('Send Code') }}
                        </button>
                    </form>
                </div>

            </div>
            
            <!-- Bottom Footer inside card -->
            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700/50 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Protected by reCAPTCHA and subject to the Privacy Policy and Terms of Service.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>