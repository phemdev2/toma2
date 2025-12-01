<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Product Inventory')</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            transition: background-color 0.3s, color 0.3s;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #343a40;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            /* justify-content: space-between; */
        }
        .sidebar h4 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
        }
        .navbar {
            display: none;
        }
        .alert {
            margin-top: 20px;
        }

        /* Light Theme Styles */
        body.light-theme {
            background-color: #f8f9fa;
            color: #333;
        }
        .sidebar.light-theme {
            background-color: #343a40;
        }
        .sidebar.light-theme a {
            color: #fff;
        }
        .sidebar.light-theme a.active {
            background-color: #495057;
        }

        /* Dark Theme Styles */
        body.dark-theme {
            background-color: #212529;
            color: #f8f9fa;
        }
        .sidebar.dark-theme {
            background-color: #495057;
        }
        .sidebar.dark-theme a {
            color: #f8f9fa;
        }
        .sidebar.dark-theme a.active {
            background-color: #6c757d;
        }

        /* Toggle Switch Styles */
        .theme-toggle {
            display: flex;
            align-items: center;
            margin: 20px;
            color: #fff;
        }
        .theme-toggle input {
            margin-right: 10px;
        }

        /* Settings Button Styles */
        .settings-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
            background-color: #343a40;
            color: #fff;
            border: none;
            border-radius: 50%;
            padding: 10px;
            font-size: 20px;
            cursor: pointer;
        }
        .settings-btn:hover {
            background-color: #495057;
        }
    </style>
</head>
<body class="light-theme">
    <div class="sidebar light-theme">
        <h4>Inventory App</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="#">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('products') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <i class="fas fa-box"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('store_inventories/create') ? 'active' : '' }}" href="{{ route('store_inventories.create') }}">
                    <i class="fas fa-plus-circle"></i> Add Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('products/create') ? 'active' : '' }}" href="{{ route('products.create') }}">
                    <i class="fas fa-plus"></i> Create Product
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('products/cards') ? 'active' : '' }}" href="{{ route('products.cards') }}">
                    <i class="fas fa-id-card"></i> Product Cards
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('transactions') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                    <i class="fas fa-receipt"></i> Transactions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('store_inventories') ? 'active' : '' }}" href="{{ route('store_inventories.index') }}">
                    <i class="fas fa-warehouse"></i> Store Inventories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('inventory/top-up') ? 'active' : '' }}" href="{{ route('inventory.top-up') }}">
                    <i class="fas fa-sync"></i> Update Inventories
                </a>
            </li>
        </ul>

        <div class="theme-toggle">
            <input type="checkbox" id="theme-toggle">
            <label for="theme-toggle">Dark Theme</label>
        </div>
    </div>

    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </div>

    <button class="btn settings-btn">
        <i class="fas fa-cogs"></i>
    </button>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
                    body.classList.replace('light-theme', 'dark-theme');
                    sidebar.classList.replace('light-theme', 'dark-theme');
                    localStorage.setItem('theme', 'dark-theme');
                } else {
                    body.classList.replace('dark-theme', 'light-theme');
                    sidebar.classList.replace('dark-theme', 'light-theme');
                    localStorage.setItem('theme', 'light-theme');
                }
            });
        });
    </script>
</body>
</html>
