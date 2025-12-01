<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Product Inventory')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="flex">
        <nav class="sidebar bg-gray-800 text-white h-screen w-64 p-5 fixed">
            <h4 class="text-center mb-5">Inventory App</h4>
            <ul class="space-y-2">
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('dashboard') ? 'bg-gray-600' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('products') ? 'bg-gray-600' : '' }}" href="{{ route('products.index') }}">
                        <i class="fas fa-box mr-2"></i> Products
                    </a>
                </li>
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('store_inventories/create') ? 'bg-gray-600' : '' }}" href="{{ route('store_inventories.create') }}">
                        <i class="fas fa-plus-circle mr-2"></i> Add Inventory
                    </a>
                </li>
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('products/create') ? 'bg-gray-600' : '' }}" href="{{ route('products.create') }}">
                        <i class="fas fa-plus mr-2"></i> Create Product
                    </a>
                </li>
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('products/cards') ? 'bg-gray-600' : '' }}" href="{{ route('products.cards') }}">
                        <i class="fas fa-id-card mr-2"></i> Product Cards
                    </a>
                </li>
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('transactions') ? 'bg-gray-600' : '' }}" href="{{ route('transactions.index') }}">
                        <i class="fas fa-receipt mr-2"></i> Transactions
                    </a>
                </li>
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('store_inventories') ? 'bg-gray-600' : '' }}" href="{{ route('store_inventories.index') }}">
                        <i class="fas fa-warehouse mr-2"></i> Store Inventories
                    </a>
                </li>
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('inventory/top-up') ? 'bg-gray-600' : '' }}" href="{{ route('inventory.top-up') }}">
                        <i class="fas fa-sync mr-2"></i> Update Inventories
                    </a>
                </li>
            </ul>

            <div class="flex items-center mt-5">
                <input type="checkbox" id="theme-toggle" class="mr-2">
                <label for="theme-toggle" class="text-sm">Dark Theme</label>
            </div>
        </nav>

        <div class="main-content ml-64 p-5 flex-1">
            <div class="flex justify-between items-center mb-5">
                @auth
                    <div class="flex items-center">
                        <i class="fas fa-user-circle text-xl mr-2"></i>
                        <span class="font-bold">{{ Auth::user()->name }}</span>
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
                <div class="alert alert-success bg-green-500 text-white p-3 rounded mb-5">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <button class="btn settings-btn fixed bottom-5 left-5 bg-gray-800 text-white rounded-full p-3 hover:bg-gray-700">
        <i class="fas fa-cogs"></i>
    </button>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script>
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
                    sidebar.classList.replace('bg-gray-800', 'bg-gray-700');
                    localStorage.setItem('theme', 'dark-theme');
                } else {
                    body.classList.replace('bg-gray-900', 'bg-gray-100');
                    body.classList.replace('text-gray-200', 'text-gray-800');
                    sidebar.classList.replace('bg-gray-700', 'bg-gray-800');
                    localStorage.setItem('theme', 'light-theme');
                }
            });
        });
    </script>
</body>
</html>