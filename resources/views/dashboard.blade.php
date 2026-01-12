@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ filter: 'all' }">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Financial Overview</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400"> comprehensive summary of sales and performance.</p>
        </div>
        <div class="flex gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                <i class="fas fa-calendar mr-1.5"></i> {{ now()->format('M d, Y') }}
            </span>
        </div>
    </div>

    <!-- 1. Hero Metrics (Total, Weekly, Monthly) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Revenue -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-full w-1 bg-gradient-to-b from-indigo-500 to-purple-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Revenue</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        &#8358;{{ number_format($totalAmount, 2) }}
                    </h3>
                </div>
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Weekly Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-full w-1 bg-gradient-to-b from-blue-400 to-cyan-400"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">This Week</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                        &#8358;{{ number_format($totalWeeklyAmount, 2) }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-arrow-up text-green-500"></i> {{ $totalWeeklyOrders }} Orders</p>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-week text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-full w-1 bg-gradient-to-b from-emerald-400 to-teal-400"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">This Month</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                        &#8358;{{ number_format($totalMonthlyAmount, 2) }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-arrow-up text-green-500"></i> {{ $totalMonthlyOrders }} Orders</p>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Charts & Detailed Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Payment Methods Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6">Payment Distribution</h3>
            <div class="relative flex-1 flex items-center justify-center">
                <canvas id="paymentChart"></canvas>
            </div>
            
            <!-- Legend / Mini Stats -->
            <div class="grid grid-cols-3 gap-2 mt-6 text-center">
                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="text-xs text-gray-500 mb-1">Cash</p>
                    <p class="font-bold text-gray-800 dark:text-white">{{ $totalCash['count'] }}</p>
                </div>
                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="text-xs text-gray-500 mb-1">POS</p>
                    <p class="font-bold text-gray-800 dark:text-white">{{ $totalPOS['count'] }}</p>
                </div>
                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="text-xs text-gray-500 mb-1">Bank</p>
                    <p class="font-bold text-gray-800 dark:text-white">{{ $totalBank['count'] }}</p>
                </div>
            </div>
        </div>

        <!-- Store Performance List -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Store Performance</h3>
                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-500">Revenue</span>
            </div>
            
            <div class="space-y-5 overflow-y-auto max-h-[300px] pr-2 custom-scrollbar">
                @foreach($storeTotals as $store)
                @php
                    // Calculate percentage for progress bar (prevent division by zero)
                    $percent = $totalAmount > 0 ? ($store['total_amount'] / $totalAmount) * 100 : 0;
                @endphp
                <div class="group">
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-store text-gray-400 text-xs"></i> {{ $store['name'] }}
                        </span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                            &#8358;{{ number_format($store['total_amount'], 2) }}
                            <span class="text-xs font-normal text-gray-400 ml-1">({{ $store['total_orders'] }} orders)</span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-1000 ease-out" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 3. Transactions Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">Transaction History</h2>
            
            <!-- Alpine JS Filter -->
            <div class="relative">
                <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <select 
                    x-model="filter"
                    class="pl-8 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border-none rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                >
                    <option value="all">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="pos">POS</option>
                    <option value="bank">Bank Transfer</option>
                </select>
            </div>
        </div>

        @if($orderItems->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        @foreach (['ID', 'Store', 'Staff', 'Method', 'Amount', 'Date', ''] as $header)
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($orderItems as $item)
                    <tr x-show="filter === 'all' || filter === '{{ strtolower($item->payment_method) }}'" 
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                        
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">
                            #{{ $item->id }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $item->store->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-[10px] font-bold text-gray-600 dark:text-gray-300">
                                    {{ substr($item->user->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->user->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $method = strtolower($item->payment_method);
                                $classes = match($method) {
                                    'cash' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border-emerald-200',
                                    'pos' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 border-blue-200',
                                    'bank' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400 border-orange-200',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                                $icon = match($method) {
                                    'cash' => 'fa-money-bill',
                                    'pos' => 'fa-credit-card',
                                    'bank' => 'fa-university',
                                    default => 'fa-circle'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $classes }}">
                                <i class="fas {{ $icon }} text-[10px]"></i> {{ ucfirst($item->payment_method) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">&#8358;{{ number_format($item->amount, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($item->order_date)->format('M d, H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('orders.show', $item->id) }}" class="text-gray-400 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800">
                {{ $orderItems->links() }}
            </div>
        </div>
        @else
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-inbox text-3xl mb-3 text-gray-300"></i>
                <p>No transactions found for this period.</p>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('paymentChart').getContext('2d');
        
        // Data from PHP
        const cashAmount = {{ $totalCash['amount'] ?? 0 }};
        const posAmount = {{ $totalPOS['amount'] ?? 0 }};
        const bankAmount = {{ $totalBank['amount'] ?? 0 }};

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'POS', 'Bank'],
                datasets: [{
                    data: [cashAmount, posAmount, bankAmount],
                    backgroundColor: [
                        '#10b981', // Emerald-500
                        '#3b82f6', // Blue-500
                        '#f97316'  // Orange-500
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: false // Using custom legend HTML below
                    }
                }
            }
        });
    });
</script>

<style>
    /* Custom Scrollbar for the Store list */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #e5e7eb;
        border-radius: 20px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #4b5563;
    }
</style>
@endsection