<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- Favicon -->
<link rel="icon" href="{{ asset('img/space.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IPOS')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
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
      background: #ffffff;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      padding: 5px;
      width: 100%;
      text-align: center;
      text-transform: capitalize;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .subscription-card:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .subscription-card .plan {
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 5px;
    }

    .subscription-card .plan .icon {
      margin-right: 5px;
      font-size: 2rem;
    }

    .subscription-card p {
      font-size: 1rem;
      color: #555;
      margin-bottom: 5px;
    }

    .subscription-card .expired {
      color: #ff6b6b;
    }

    .subscription-card .active {
      color: #4caf50;
    }

    .subscription-card button {
      background: #4caf50;
      color: #fff;
      font-size: 1rem;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .subscription-card button:hover {
      background: #43a047;
    }

    .subscription-card .renew-btn {
      background: #ff6b6b;
    }

    .subscription-card .renew-btn:hover {
      background: #e53935;
    }

    /* Icons */
    .icon-basic {
      color: #2196f3;
    }

    .icon-premium {
      color: #ff9800;
    }

    .icon-enterprise {
      color: #9c27b0;
    }

    .icon-default {
      color: #333;
    }

    .card {
    border-radius: 15px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #ddd;
}

.card-body {
    padding: 20px;
    background-color: #fff;
}

h2 {
    font-size: 2rem;
    color: #333;
    font-weight: bold;
}

h3 {
    font-size: 1.5rem;
    color: #444;
}

.status-info {
    margin-bottom: 20px;
}

.status-info .d-flex {
    font-size: 1.1rem;
    padding: 8px;
}

.status-info .fw-bold {
    color: #555;
}

.text-success {
    color: green;
    font-weight: bold;
}

.text-danger {
    color: red;
    font-weight: bold;
}

.alert {
    font-size: 1.1rem;
    border-radius: 5px;
    padding: 15px;
}

.alert-warning {
    background-color: #ffeb3b;
    color: #333;
}

.alert-success {
    background-color: #4caf50;
    color: #fff;
}

.alert-danger {
    background-color: #f44336;
    color: #fff;
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
    <h4 class="mb-4 text-2xl font-bold text-center bg-purple-800 p-2" style="font-family: 'Montserrat', sans-serif;">DEOMEZE</h4>
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
    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('daily') ? 'bg-red-600' : '' }}" 
       href="{{ route('daily.index') }}">
        <i class="fas fa-calendar-day mr-2"></i> Daily Records
    </a>
</li>
<li>
    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('daily/report') ? 'bg-red-600' : '' }}" 
       href="{{ route('daily.report') }}">
        <i class="fas fa-chart-line mr-2"></i> Report
    </a>
</li>


      

        <!-- Conditionally disable menu items if subscription is expired -->
        @if(!$isSubscriptionExpired)

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
          
            
            <li x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center p-2 w-full text-white rounded hover:bg-purple-700 focus:outline-none">
        <i class="fas fa-user-cog mr-2"></i> Subscription
        <i class="fas fa-chevron-down ml-2"></i>
    </button>
    <ul x-show="open" x-transition @click.away="open = false" class="absolute left-0 w-full mt-2 space-y-2 bg-white shadow-lg rounded-md hidden" :class="{'block': open, 'hidden': !open}">
        <!-- View Subscription -->
        <li>
            <a class="flex items-center p-2 text-gray-800 hover:bg-gray-200 rounded" href="{{ route('subscriptions.show', ['id' => Auth::user()->store_id]) }}">
                <i class="fas fa-eye mr-3 text-blue-500"></i> View Subscription
            </a>
        </li>
        <!-- All Subscriptions (Subscription Index) -->
        <li>
            <a class="flex items-center p-2 text-gray-800 hover:bg-gray-200 rounded" href="{{ route('subscription.index') }}">
                <i class="fas fa-list mr-3 text-green-500"></i> All Subscriptions
            </a>
        </li>
        <!-- Renew Subscription -->
        <li>
            <a class="flex items-center p-2 text-gray-800 hover:bg-gray-200 rounded" href="{{ route('subscription.create') }}">
                <i class="fas fa-redo mr-3 text-yellow-500"></i> Renew Subscription
            </a>
        </li>
    </ul>
</li>


            @can('create-product')
            <li>
                <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('products/create') ? 'bg-red-600' : '' }}" href="{{ route('products.create') }}">
                    <i class="fas fa-plus mr-2"></i> Create Product
                </a>
            </li>
            @endcan
        @else
            <!-- Optionally, show a message or just disable the links -->
            <li>
                <a href="#" class="text-gray-500 cursor-not-allowed">
                    <i class="fas fa-shopping-cart mr-2"></i> Transactions (Subscription Expired)
                </a>
            </li>
            <li>
                <a href="#" class="text-gray-500 cursor-not-allowed">
                    <i class="fas fa-box mr-2"></i> Products (Subscription Expired)
                </a>
            </li>
        @endif
    
                <li>
                    <a class="flex items-center p-2 rounded hover:bg-purple-700 {{ request()->is('users*') ? 'bg-red-600' : '' }}" href="{{ route('users.index') }}">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                </li>
               
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


<div class="main-content p-5 flex-1 ml-0 md:ml-64 transition-margin" style="position: relative; padding-top: 2px; width: 100%; max-width: 100%;">
    <div class="flex justify-between items-center mb-5" style="position: sticky; top: 0; z-index: 10; background-color: #fff; padding: 10px 20px; border-bottom: 1px solid #ddd; width: 100%; box-sizing: border-box;">
        @auth
      <div class="flex flex-col md:flex-row md:items-center text-purple-900 space-y-1 md:space-y-0 md:space-x-4">
    <div class="flex items-center">
        <i class="fas fa-user-circle text-xl mr-2"></i>
        <span class="font-bold" style="font-size: 1.1rem;">
            {{ ucwords(Auth::user()->name) }}
        </span>
    </div>

    @if(Auth::user()->store)
    <div class="flex items-center text-sm text-gray-700 md:ml-4">
        <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
        <span>
            {{ Auth::user()->store->name }} 
            @if(Auth::user()->store->location)
                — {{ Auth::user()->store->location }}
            @endif
        </span>
    </div>
    @endif
</div>


        <form method="POST" action="{{ route('logout') }}" x-data>
            @csrf
            <button type="submit" class="text-red-500 hover:text-red-700 text-lg">
                <i class="fas fa-sign-out-alt mr-1"></i> Log Out
            </button>
        </form>
        @else
        <div class="flex flex-col space-y-1">
            <a href="{{ route('login') }}" class="text-blue-500 hover:underline text-lg">Log In</a>
            <a href="{{ route('register') }}" class="text-blue-500 hover:underline text-lg">Register</a>
        </div>
        @endauth
    </div>
    
    <!-- Success message section -->
    @if(session('success'))
    <div class="bg-green-500 text-white p-3 rounded mb-5" style="width: 100%; max-width: 100%; font-size: 1.1rem;">
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