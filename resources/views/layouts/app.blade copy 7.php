<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Product Inventory')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">

    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        .main-content {
            overflow-y: auto;
            max-height: 100vh;
        }

        #sidebarToggle {
            z-index: 1000;
        }

        nav {
            z-index: 800;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.5rem;
            text-align: left;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Light mode styles */
        .light-theme th {
            background-color: #f9f9f9;
            color: #333;
        }

        .light-theme td {
            background-color: #fff;
            color: #333;
        }

        /* Dark mode styles */
        .dark-theme th {
            background-color: #2a2a2a;
            color: #ddd;
        }

        .dark-theme td {
            background-color: #1a1a1a;
            color: #ddd;
        }

        .subscription-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 0px;
            text-align: center;
        }
        .end-date {
            font-size: 18px;
            
            color: #4A5568; /* Gray-700 */
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 h-full">

    <div class="flex">
        <!-- Mobile Toggle Button -->
        <button id="sidebarToggle" class="md:hidden fixed top-5 left-5 bg-gray-800 text-white p-2 rounded shadow-lg transition duration-300 hover:bg-purple-700" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <nav id="sidebar" class="sidebar bg-purple-900 text-white h-screen w-64 p-5 fixed md:block transition-transform transform -translate-x-full md:translate-x-0">
            <h4 class="mb-4 text-2xl font-bold text-center bg-purple-800 p-2" style="font-family: 'Montserrat', sans-serif;">KNARF MART</h4>
            <ul class="space-y-2">
                @can('update-inventories')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('dashboard') ? 'bg-red-600' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                </li>

                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('user-totals') ? 'bg-red-600' : '' }}" href="{{ route('user.totals') }}">
                        <i class="fas fa-chart-pie mr-2"></i> Reports
                    </a>
                </li>
                @endcan

                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('pos') ? 'bg-red-600' : '' }}" 
                       href="{{ route('pos.index', ['user_id' => Auth::id(), 'store_id' => Auth::user()->store_id]) }}" 
                       target="_blank">
                        <i class="fas fa-cash-register mr-2"></i> POS
                    </a>
                </li>
                <li>
                <a href="{{ route('purchases.index') }}" class="text-white">Purchases</a>
                <a href="{{ route('purchases.create') }}" class="text-white">Add Purchase</a>
                </li>

                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('orders') ? 'bg-red-600' : '' }}" href="{{ route('orders.index') }}">
                        <i class="fas fa-shopping-cart mr-2"></i> Transactions
                    </a>
                </li>

                @can('view-products')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('products') ? 'bg-red-600' : '' }}" href="{{ route('products.index') }}">
                        <i class="fas fa-box mr-2"></i> Products
                    </a>
                </li>
                @endcan

                @can('add-inventory')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('store_inventories/create') ? 'bg-red-600' : '' }}" href="{{ route('store_inventories.create') }}">
                        <i class="fas fa-plus-circle mr-2"></i> Purchase
                    </a>
                </li>
                @endcan

                @can('create-product')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('products/create') ? 'bg-red-600' : '' }}" href="{{ route('products.create') }}">
                        <i class="fas fa-plus mr-2"></i> Create Product
                    </a>
                </li>
                @endcan

                @can('view-orders')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('orders') ? 'bg-red-600' : '' }}" href="{{ route('orders.index') }}">
                        <i class="fas fa-shopping-cart mr-2"></i> View Orders
                    </a>
                </li>
                @endcan

                @can('create-order')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('orders/create') ? 'bg-red-600' : '' }}" href="{{ route('orders.create') }}">
                        <i class="fas fa-plus-square mr-2"></i> Create Order
                    </a>
                </li>
                @endcan

                @can('view-order-details')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('orders/details') ? 'bg-red-600' : '' }}" href="{{ route('orders.details') }}">
                        <i class="fas fa-info-circle mr-2"></i> Order Details
                    </a>
                </li>
                @endcan

                @can('view-users')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('users*') ? 'bg-red-600' : '' }}" href="{{ route('users.index') }}">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                </li>
                @endcan

                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('stores*') ? 'bg-red-600' : '' }}" href="{{ route('stores.index') }}">
                        <i class="fa-regular fa-address-book mr-2"></i> Store Settings
                    </a>
                </li>

                @can('view-product-cards')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('cashout') ? 'bg-red-600' : '' }}" href="{{ route('cashout.index') }}">
                        <i class="fas fa-money-bill-wave mr-2"></i> Cashout
                    </a>
                </li>
                @endcan

                @can('view-store-inventories')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('store_inventories') ? 'bg-red-600' : '' }}" href="{{ route('store_inventories.index') }}">
                        <i class="fas fa-warehouse mr-2"></i> Store Inventories
                    </a>
                </li>
                @endcan

                @can('update-inventories')
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('admin/settings') ? 'bg-gray-600' : '' }}" href="{{ route('admin.settings.index') }}">
                        <i class="fas fa-cogs mr-2"></i> Over Selling
                    </a>
                </li>
                @endcan
            </ul>

            <div class="flex items-center mt-5">
                <input type="checkbox" id="theme-toggle" class="mr-2" aria-label="Toggle Dark Theme">
                <label for="theme-toggle" class="text-sm">Dark Theme</label>
            </div>
        </nav>

        <div class="main-content p-5 flex-1 ml-0 md:ml-64 transition-margin">
            <div class="flex justify-between items-center mb-5">
                @auth
                <div class="flex items-center text-purple-900">
                    <i class="fas fa-user-circle text-xl mr-2"></i>
                    <span class="font-bold">{{ ucwords(Auth::user()->name) }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <button type="submit" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-sign-out-alt"></i> Log Out
                    </button>
                </form>
                @else
                <div class="flex flex-col space-y-1">
                    <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Log In</a>
                    <a href="{{ route('register') }}" class="text-blue-500 hover:underline">Register</a>
                </div>
                @endauth

                
            </div>
            
            @if(session('success'))
            <div class="bg-green-500 text-white p-3 rounded mb-5">
                {{ session('success') }}
            </div>
            @endif

            @yield('content')
        </div>

        
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script>
        // Sidebar Toggle for Mobile
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        });

        document.addEventListener('DOMContentLoaded', function () {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            const sidebar = document.querySelector('.sidebar');

            // Check saved theme in local storage
            const savedTheme = localStorage.getItem('theme') || 'light-theme';
            body.classList.add(savedTheme);
            sidebar.classList.add(savedTheme);
            themeToggle.checked = savedTheme === 'dark-theme';

            themeToggle.addEventListener('change', function () {
                if (this.checked) {
                    body.classList.replace('bg-gray-100', 'bg-gray-900');
                    body.classList.replace('text-gray-800', 'text-gray-200');
                    sidebar.classList.replace('bg-purple-900', 'bg-purple-700'); // Sidebar dark theme
                    body.classList.replace('light-theme', 'dark-theme');
                } else {
                    body.classList.replace('bg-gray-900', 'bg-gray-100');
                    body.classList.replace('text-gray-200', 'text-gray-800');
                    sidebar.classList.replace('bg-purple-700', 'bg-purple-900'); // Sidebar light theme
                    body.classList.replace('dark-theme', 'light-theme');
                }
                // Toggle table styles
                document.querySelectorAll('table').forEach(table => {
                    table.classList.toggle('dark-theme', this.checked);
                });
                localStorage.setItem('theme', this.checked ? 'dark-theme' : 'light-theme');
            });
        });

    
    </script>
</body>

</html>