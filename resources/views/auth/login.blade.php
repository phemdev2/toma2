<x-guest-layout>
    <div class="relative min-h-screen flex flex-col lg:flex-row transition-all duration-300" x-data="{ loginMethod: 'password' }">
        
        <!-- Left Side: Decorative Background -->
        <!-- Optimized: Added lazy loading and object-cover to prevent stretching -->
        <div class="absolute inset-0 lg:relative lg:w-1/2 bg-cover bg-center transition-all duration-300"
            style="background-image: url('https://media.istockphoto.com/id/1282636502/vector/business-people-studying-list-of-rules.jpg?s=612x612&w=0&k=20&c=XkjLnnQS9PfaVfRxENtaz4_Km9h9u5A3SgeHar7-Mwc=');">
            <!-- Overlay for text readability on mobile -->
            <div class="absolute inset-0 bg-gray-900/40 lg:bg-transparent lg:hidden"></div>
            
            <!-- Branding Text (Optional) -->
            <div class="hidden lg:flex absolute bottom-10 left-10 text-white flex-col drop-shadow-md">
                <h1 class="text-4xl font-bold">Welcome Back</h1>
                <p class="text-lg mt-2 opacity-90">Securely access your dashboard.</p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="relative z-10 w-full lg:w-1/2 flex flex-col justify-center items-center p-6 lg:p-12 bg-white dark:bg-gray-900 shadow-xl lg:shadow-none h-full overflow-y-auto">
            
            <div class="w-full max-w-md space-y-6">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <x-authentication-card-logo />
                </div>

                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Sign in to your account</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Or <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">create a new account</a>
                    </p>
                </div>

                <!-- Google Login Button -->
                <div>
                    <a href="{{ route('auth.google') }}" 
                       class="w-full flex items-center justify-center gap-3 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                            <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                                <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z" />
                                <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z" />
                                <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z" />
                                <path fill="#EA4335" d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z" />
                            </g>
                        </svg>
                        <span class="block">Continue with Google</span>
                    </a>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-gray-900 text-gray-500">Or continue with</span>
                    </div>
                </div>

                <!-- Tabs for Login Method -->
                <div class="flex p-1 space-x-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                    <button @click="loginMethod = 'password'" 
                            :class="{ 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white': loginMethod === 'password', 'text-gray-500 hover:text-gray-700 dark:text-gray-400': loginMethod !== 'password' }"
                            class="w-full py-2.5 text-sm font-medium leading-5 rounded-lg focus:outline-none transition-all duration-200">
                        Password
                    </button>
                    <button @click="loginMethod = 'otp'" 
                            :class="{ 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white': loginMethod === 'otp', 'text-gray-500 hover:text-gray-700 dark:text-gray-400': loginMethod !== 'otp' }"
                            class="w-full py-2.5 text-sm font-medium leading-5 rounded-lg focus:outline-none transition-all duration-200">
                        OTP (One-Time PIN)
                    </button>
                </div>

                <x-validation-errors class="mb-4" />

                @if (session('status'))
                    <div class="mb-4 p-4 rounded-md bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                    {{ session('status') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Password Login Form -->
                <div x-show="loginMethod === 'password'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div>
                            <x-label for="email" value="{{ __('Email Address') }}" class="dark:text-gray-200" />
                            <x-input id="email" class="block mt-1 w-full dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 focus:ring-indigo-500" 
                                type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        </div>

                        <div class="mt-4">
                            <div class="flex justify-between items-center">
                                <x-label for="password" value="{{ __('Password') }}" class="dark:text-gray-200" />
                                @if (Route::has('password.request'))
                                    <a class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors" 
                                       href="{{ route('password.request') }}">
                                        {{ __('Forgot password?') }}
                                    </a>
                                @endif
                            </div>
                            <x-input id="password" class="block mt-1 w-full dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 focus:ring-indigo-500" 
                                type="password" name="password" required autocomplete="current-password" />
                        </div>

                        <div class="block mt-4">
                            <label for="remember_me" class="flex items-center">
                                <x-checkbox id="remember_me" name="remember" class="dark:border-gray-600 text-indigo-600" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Remember me') }}</span>
                            </label>
                        </div>

                        <div class="mt-6">
                            <x-button class="w-full justify-center py-3 text-base">
                                {{ __('Sign in with Password') }}
                            </x-button>
                        </div>
                    </form>
                </div>

                <!-- OTP Login Form -->
                <div x-show="loginMethod === 'otp'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <!-- Note: This form requests the OTP. Use a different route for handling OTP verification steps -->
                    <form method="POST" action="{{ route('login.otp.send') }}">
                        @csrf
                        <div>
                            <x-label for="otp_identifier" value="{{ __('Email or Phone Number') }}" class="dark:text-gray-200" />
                            <x-input id="otp_identifier" class="block mt-1 w-full dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 focus:ring-indigo-500" 
                                type="text" name="identifier" :value="old('identifier')" required autofocus placeholder="e.g., user@example.com" />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">We will send a one-time verification code to this address.</p>
                        </div>

                        <div class="mt-6">
                            <x-button class="w-full justify-center py-3 text-base bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-900 border-emerald-600 focus:ring-emerald-500">
                                {{ __('Send Login Code') }}
                            </x-button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>