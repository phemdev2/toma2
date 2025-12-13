@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Main Container -->
<div class="space-y-6 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- 1. Header & Quick Actions -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white font-montserrat tracking-tight">
                @php
                    $hour = date('H');
                    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                    $firstName = explode(' ', Auth::user()->name)[0];
                @endphp
                {{ $greeting }}, {{ $firstName }} 👋
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Here's your store performance overview.</p>
        </div>

        <div class="flex flex-wrap gap-3 w-full lg:w-auto">
            <a href="{{ route('pos.index', ['user_id' => Auth::id(), 'store_id' => Auth::user()->store_id ?? 0]) }}" 
               target="_blank"
               class="flex-1 lg:flex-none flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-indigo-200 dark:shadow-none transition transform hover:-translate-y-0.5">
                <i class="fas fa-cash-register"></i>
                <span>POS</span>
            </a>
            
            @can('create-product')
            <a href="{{ route('products.create') }}" 
               class="flex-1 lg:flex-none flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="fas fa-plus text-gray-400"></i>
                <span class="hidden sm:inline">Product</span>
            </a>
            @endcan

             <a href="{{ route('daily.report') }}" 
               class="flex-1 lg:flex-none flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="fas fa-file-invoice text-gray-400"></i>
            </a>
        </div>
    </div>

    <!-- 2. Subscription Hero Card -->
    <div x-data="subscriptionTimer('{{ $subscriptionExpiryDate }}')" 
         class="relative w-full rounded-2xl p-6 overflow-hidden shadow-sm transition-all duration-300 {{ $isSubscriptionExpired ? 'bg-gradient-to-r from-red-500 to-orange-600 text-white' : 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' }}">
        
        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 rounded-full bg-white opacity-10 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 rounded-full bg-white opacity-10 blur-2xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                    <i class="fas {{ $isSubscriptionExpired ? 'fa-exclamation-triangle' : 'fa-crown' }} text-2xl"></i>
                </div>
                <div>
                    <p class="text-white/80 text-xs font-semibold uppercase tracking-wider">Current Plan</p>
                    <h2 class="text-2xl font-bold capitalize">{{ $subscriptionType }} Membership</h2>
                    <div class="flex items-center gap-2 mt-1 text-sm text-white/90 font-mono bg-black/10 px-2 py-1 rounded-lg inline-flex">
                        <i class="far fa-clock"></i>
                        <span x-text="timeLeft">Loading...</span>
                    </div>
                </div>
            </div>

            <div>
                <button class="px-6 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/30 rounded-lg font-medium text-sm transition">
                    {{ $isSubscriptionExpired ? 'Renew Now' : 'Manage Plan' }}
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Stats Grid (5 Columns for AOV) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach ([
            ['label' => 'Total Revenue', 'value' => number_format($totalAmount, 2), 'prefix' => '₦', 'icon' => 'wallet', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
            ['label' => 'Orders Today', 'value' => $totalCash['count'] + $totalPOS['count'] + $totalBank['count'], 'prefix' => '', 'icon' => 'shopping-bag', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50 dark:bg-blue-900/20'],
            ['label' => 'Avg. Order Value', 'value' => number_format($averageOrderValue, 0), 'prefix' => '₦', 'icon' => 'tags', 'color' => 'text-pink-500', 'bg' => 'bg-pink-50 dark:bg-pink-900/20'],
            ['label' => 'Monthly Sales', 'value' => number_format($totalMonthlyAmount, 2), 'prefix' => '₦', 'icon' => 'calendar-check', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50 dark:bg-purple-900/20'],
            ['label' => 'Active Stores', 'value' => count($storeTotals), 'prefix' => '', 'icon' => 'store', 'color' => 'text-orange-500', 'bg' => 'bg-orange-50 dark:bg-orange-900/20'],
        ] as $stat)
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $stat['label'] }}</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                        <span class="text-sm text-gray-400 mr-0.5">{{ $stat['prefix'] }}</span>{{ $stat['value'] }}
                    </h3>
                </div>
                <div class="p-3 rounded-xl {{ $stat['bg'] }} {{ $stat['color'] }}">
                    <i class="fas fa-{{ $stat['icon'] }} text-lg"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- 4. Visual Analytics: Sales & Payments -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Trend -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-chart-line text-indigo-500"></i> Revenue Trend
                </h3>
                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded">Last 7 Days</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Payment Distribution -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">
            <h3 class="font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                <i class="fas fa-wallet text-indigo-500"></i> Payment Method
            </h3>
            <div class="relative flex-1 flex items-center justify-center" style="min-height: 200px;">
                <canvas id="paymentChart"></canvas>
            </div>
            <div class="mt-4 flex justify-between text-sm px-2">
                <span class="text-emerald-500 font-bold">Cash: {{ $totalCash['count'] }}</span>
                <span class="text-blue-500 font-bold">POS: {{ $totalPOS['count'] }}</span>
                <span class="text-orange-500 font-bold">Bank: {{ $totalBank['count'] }}</span>
            </div>
        </div>
    </div>

    <!-- 5. Advanced Analytics: Hourly & Staff -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Hourly Traffic -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-clock text-indigo-500"></i> Peak Business Hours
                </h3>
                <span class="text-xs text-gray-400">Today</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Top Staff -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">
            <h3 class="font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                <i class="fas fa-user-tie text-indigo-500"></i> Top Staff Today
            </h3>
            <div class="flex-1 overflow-auto space-y-4">
                @forelse($topStaff as $index => $staff)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ substr($staff->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $staff->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $staff->total_orders }} orders</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">₦{{ number_format($staff->total_sales) }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-sm text-gray-400 mt-4">No staff activity yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 6. Operational Lists: Low Stock & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col h-full">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-amber-500"></i> Low Stock
                </h3>
            </div>
            <div class="flex-1 overflow-auto">
                <table class="w-full text-left">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($lowStockProducts ?? [] as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $product->name }}</td>
                            <td class="px-6 py-3 text-right">
                                <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $product->quantity == 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $product->quantity }} Left
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="p-6 text-center text-sm text-gray-500">Inventory levels are healthy!</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Best Sellers -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col h-full">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800">
                <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-trophy text-yellow-500"></i> Best Sellers
                </h3>
            </div>
            <div class="flex-1 overflow-auto">
                <table class="w-full text-left">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($topProducts ?? [] as $index => $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-3 w-10 text-center text-xs font-bold text-gray-400">#{{ $index + 1 }}</td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $product->name }}</td>
                            <td class="px-6 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">{{ $product->total_sold }} <span class="font-normal text-xs text-gray-400">sold</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-6 text-center text-sm text-gray-500">No sales data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 7. Recent Transactions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden" x-data="{ filter: 'all' }">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h3 class="font-bold text-gray-800 dark:text-white text-lg">Recent Transactions</h3>
            
            <div class="flex p-1 bg-gray-100 dark:bg-gray-700 rounded-lg">
                <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white' : 'text-gray-500'" class="px-4 py-1.5 text-xs font-medium rounded-md transition-all">All</button>
                <button @click="filter = 'cash'" :class="filter === 'cash' ? 'bg-white dark:bg-gray-600 shadow text-green-600' : 'text-gray-500'" class="px-4 py-1.5 text-xs font-medium rounded-md transition-all">Cash</button>
                <button @click="filter = 'pos'" :class="filter === 'pos' ? 'bg-white dark:bg-gray-600 shadow text-blue-600' : 'text-gray-500'" class="px-4 py-1.5 text-xs font-medium rounded-md transition-all">POS</button>
                <button @click="filter = 'bank'" :class="filter === 'bank' ? 'bg-white dark:bg-gray-600 shadow text-orange-600' : 'text-gray-500'" class="px-4 py-1.5 text-xs font-medium rounded-md transition-all">Bank</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold tracking-wider">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Store</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Method</th>
                        <th class="px-6 py-4">Time</th>
                        <th class="px-6 py-4 text-right">View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($orderItems as $item)
                    <tr x-show="filter === 'all' || filter === '{{ strtolower($item->payment_method) }}'" 
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4 text-sm font-mono text-gray-500 dark:text-gray-400">#{{ $item->id }}</td>
                        <td class="px-6 py-4"><span class="px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $item->store->name ?? 'Main' }}</span></td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">₦{{ number_format($item->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            @php
                                $method = strtolower($item->payment_method);
                                $bgClass = match($method) {
                                    'cash' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200',
                                    'pos' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200',
                                    'bank' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 border-orange-200',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $bgClass }}">{{ ucfirst($item->payment_method) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($item->order_date)->format('H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('orders.show', $item->id) }}" class="text-gray-400 hover:text-indigo-600"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No transactions today.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800">
            {{ $orderItems->links() }} 
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    // 1. Subscription Timer
    document.addEventListener('alpine:init', () => {
        Alpine.data('subscriptionTimer', (expiryDate) => ({
            expiryTime: new Date(expiryDate).getTime(),
            timeLeft: 'Checking...',
            init() {
                this.update();
                setInterval(() => this.update(), 1000);
            },
            update() {
                const now = new Date().getTime();
                const distance = this.expiryTime - now;
                if (distance < 0) {
                    this.timeLeft = "Expired";
                } else {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    this.timeLeft = `${days}d ${hours}h left`;
                }
            }
        }));
    });

    // 2. Charts
    document.addEventListener('DOMContentLoaded', function() {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: document.documentElement.classList.contains('dark') ? '#374151' : '#f3f4f6' }, ticks: { display: false } },
                x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 10 } } }
            }
        };

        // A. Sales Trend
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        let gradient = ctxSales.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: @json($chartLabels), 
                datasets: [{ label: 'Revenue', data: @json($chartData), borderColor: '#6366f1', backgroundColor: gradient, borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3 }]
            },
            options: commonOptions
        });

        // B. Hourly Traffic
        new Chart(document.getElementById('hourlyChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                datasets: [{ 
                    label: 'Orders', 
                    data: @json($hourlyTraffic), 
                    backgroundColor: (ctx) => new Date().getHours() === ctx.dataIndex ? '#f59e0b' : '#e0e7ff',
                    borderRadius: 3 
                }]
            },
            options: commonOptions
        });

        // C. Payments
        new Chart(document.getElementById('paymentChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'POS', 'Bank'],
                datasets: [{
                    data: [{{ $totalCash['count'] }}, {{ $totalPOS['count'] }}, {{ $totalBank['count'] }}],
                    backgroundColor: ['#10b981', '#3b82f6', '#f97316'],
                    borderWidth: 0, hoverOffset: 5
                }]
            },
            options: { ...commonOptions, cutout: '75%', scales: { x: { display: false }, y: { display: false } } }
        });
    });
</script>
@endsection