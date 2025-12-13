<!DOCTYPE html>
<html lang="en" class="light"> <!-- Explicitly set class="light" -->

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('img/space.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IPOS')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#1a1a1a',
                            surface: '#2a2a2a',
                            text: '#e5e7eb'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Alpine JS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #2d3748; }
        ::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
        [x-cloak] { display: none !important; }
        .sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 font-sans transition-colors duration-300 h-screen overflow-hidden flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
         class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>

    <!-- Sidebar -->
    <nav :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
         class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-purple-900 dark:bg-gray-800 text-white transform md:translate-x-0 md:relative flex flex-col h-full shadow-lg">
        
        <!-- DYNAMIC STORE NAME SECTION -->
        <div class="p-5 border-b border-purple-800 dark:border-gray-700 flex items-center justify-center min-h-[80px]">
            <h4 class="text-2xl font-bold text-center font-montserrat tracking-wide break-words uppercase">
                @auth
                    <!-- Uses Null Safe Operator (?->) to prevent crash if store is null -->
                    {{ Auth::user()->store?->company ?? 'IPOS SYSTEM' }}
                @else
                    IPOS SYSTEM
                @endauth
            </h4>
        </div>

        <!-- Menu Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            
            @can('update-inventories')
            <a href="{{ route('dashboard') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('dashboard') ? 'bg-red-600' : '' }}">
                <i class="fas fa-tachometer-alt w-6 text-center"></i> <span class="ml-2">Dashboard</span>
            </a>
            <a href="{{ route('user.totals') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('user-totals') ? 'bg-red-600' : '' }}">
                <i class="fas fa-chart-pie w-6 text-center"></i> <span class="ml-2">Reports</span>
            </a>
            @endcan

            <a href="{{ route('daily.index') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('daily') ? 'bg-red-600' : '' }}">
                <i class="fas fa-calendar-day w-6 text-center"></i> <span class="ml-2">Daily Records</span>
            </a>
            
             <a href="{{ route('daily.report') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('daily/report') ? 'bg-red-600' : '' }}">
                <i class="fas fa-chart-line w-6 text-center"></i> <span class="ml-2">Report</span>
            </a>

            <!-- Subscription Logic -->
            @if(isset($isSubscriptionExpired) && !$isSubscriptionExpired)
                <a href="{{ route('pos.index', ['user_id' => Auth::id(), 'store_id' => Auth::user()->store_id ?? 0]) }}" target="_blank" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('pos') ? 'bg-red-600' : '' }}">
                    <i class="fas fa-cash-register w-6 text-center"></i> <span class="ml-2">POS</span>
                </a>
                
                 <a href="{{ route('purchases.index') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('purchases') ? 'bg-red-600' : '' }}">
                    <i class="fas fa-truck-loading w-6 text-center"></i> <span class="ml-2">Purchases</span>
                </a>

                <a href="{{ route('orders.index') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('orders') ? 'bg-red-600' : '' }}">
                    <i class="fas fa-shopping-cart w-6 text-center"></i> <span class="ml-2">Transactions</span>
                </a>

                @can('view-products')
                <a href="{{ route('products.index') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('products') ? 'bg-red-600' : '' }}">
                    <i class="fas fa-box w-6 text-center"></i> <span class="ml-2">Products</span>
                </a>
                @endcan

                <!-- Subscription Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center w-full p-3 rounded hover:bg-purple-700 dark:hover:bg-gray-700 focus:outline-none justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-user-cog w-6 text-center"></i> <span class="ml-2">Subscription</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse x-cloak class="pl-8 space-y-1 mt-1">
                        <a href="{{ route('subscriptions.show', ['id' => Auth::user()->store_id ?? 0]) }}" class="block p-2 text-sm text-gray-200 hover:text-white hover:bg-purple-600 rounded">View Plan</a>
                        <a href="{{ route('subscription.index') }}" class="block p-2 text-sm text-gray-200 hover:text-white hover:bg-purple-600 rounded">All Plans</a>
                        <a href="{{ route('subscription.create') }}" class="block p-2 text-sm text-gray-200 hover:text-white hover:bg-purple-600 rounded">Renew</a>
                    </div>
                </div>

                @can('create-product')
                <a href="{{ route('products.create') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('products/create') ? 'bg-red-600' : '' }}">
                    <i class="fas fa-plus w-6 text-center"></i> <span class="ml-2">Create Product</span>
                </a>
                @endcan
            @else
                <div class="p-3 bg-red-800 bg-opacity-50 rounded text-sm text-gray-300">
                    <i class="fas fa-lock mr-1"></i> Feature Locked (Expired)
                </div>
            @endif

            <a href="{{ route('users.index') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('users*') ? 'bg-red-600' : '' }}">
                <i class="fas fa-users w-6 text-center"></i> <span class="ml-2">Users</span>
            </a>

            <a href="{{ route('stores.index') }}" class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('stores*') ? 'bg-red-600' : '' }}">
                <i class="fa-regular fa-address-book w-6 text-center"></i> <span class="ml-2">Store Settings</span>
            </a>
            
             @can('view-product-cards')
            <a class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('cashout') ? 'bg-red-600' : '' }}" href="{{ route('cashout.index') }}">
                <i class="fas fa-money-bill-wave w-6 text-center"></i> <span class="ml-2">Cashout</span>
            </a>
            @endcan
            
            @can('view-store-inventories')
            <a class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('store_inventories') ? 'bg-red-600' : '' }}" href="{{ route('store_inventories.index') }}">
                <i class="fas fa-warehouse w-6 text-center"></i> <span class="ml-2">Store Inventories</span>
            </a>
            @endcan

            @can('update-inventories')
            <a class="flex items-center p-3 rounded transition hover:bg-purple-700 dark:hover:bg-gray-700 {{ request()->is('admin/settings') ? 'bg-gray-600' : '' }}" href="{{ route('admin.settings.index') }}">
                <i class="fas fa-cogs w-6 text-center"></i> <span class="ml-2">Over Selling</span>
            </a>
            @endcan

        </div>

        <!-- Footer / Theme Toggle -->
        <div class="p-4 border-t border-purple-800 dark:border-gray-700 bg-purple-900 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <span class="text-sm">Dark Mode</span>
                <button id="theme-toggle" class="relative inline-flex items-center h-6 rounded-full w-11 focus:outline-none bg-gray-400 dark:bg-green-500 transition-colors">
                    <span class="sr-only">Enable dark mode</span>
                    <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform translate-x-1 dark:translate-x-6"></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Top Header -->
        <header class="bg-white dark:bg-gray-800 shadow-md h-16 flex items-center justify-between px-6 z-10 shrink-0">
            <!-- Mobile Toggle -->
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-600 dark:text-gray-300 text-2xl focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>

            <!-- User Info -->
            <div class="flex items-center justify-end w-full space-x-4">
                @auth
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-bold text-gray-800 dark:text-white">{{ ucwords(Auth::user()->name) }}</div>
                        @if(Auth::user()->store)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                {{ Auth::user()->store->name }}
                            </div>
                        @endif
                    </div>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-semibold border border-red-200 p-2 rounded hover:bg-red-50 dark:hover:bg-gray-700 transition">
                            <i class="fas fa-sign-out-alt mr-1"></i> Log Out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Log In</a>
                @endauth
            </div>
        </header>

        <!-- Scrollable Content Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6 relative">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm dark:bg-green-900 dark:text-green-200" role="alert">
                    <p class="font-bold">Success</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm dark:bg-red-900 dark:text-red-200" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </main>
    </div>

    <!-- Theme Logic Script -->
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;

        // 1. Load saved theme
        const savedTheme = localStorage.getItem('theme');

        // Logic: Only enable dark mode if explicitly saved as 'dark'. 
        // Otherwise, defaults to light (ignoring system preference).
        if (savedTheme === 'dark') {
            htmlElement.classList.add('dark');
        } else {
            htmlElement.classList.remove('dark');
        }

        // 2. Toggle functionality
        themeToggleBtn.addEventListener('click', () => {
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                htmlElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>
</html>