@extends('layouts.app')

@section('content')
<div class="container mx-auto px-2 py-2 h-full">

    <!-- Grid of Cards for Totals -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <!-- Total Cash Orders Card -->
        <div class="bg-purple-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-money-bill-wave text-purple-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-purple-800">Total Cash Orders</h2>
                </div>
                <hr class="border-purple-600 mb-2">
                <p class="text-xl font-bold text-gray-900">{{ $totalCash['count'] }}</p>
                <p class="text-sm font-medium text-gray-700">Total Amount: &#8358;{{ number_format($totalCash['amount'], 2) }}</p>
            </div>
        </div>

        <!-- Total POS Orders Card -->
        <div class="bg-green-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-credit-card text-green-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-green-800">Total POS Orders</h2>
                </div>
                <hr class="border-green-600 mb-2">
                <p class="text-xl font-bold text-gray-900">{{ $totalPOS['count'] }}</p>
                <p class="text-sm font-medium text-gray-700">Total Amount: &#8358;{{ number_format($totalPOS['amount'], 2) }}</p>
            </div>
        </div>

        <!-- Total Bank Orders Card -->
        <div class="bg-blue-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-university text-blue-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-blue-800">Total Bank Orders</h2>
                </div>
                <hr class="border-blue-600 mb-2">
                <p class="text-xl font-bold text-gray-900">{{ $totalBank['count'] }}</p>
                <p class="text-sm font-medium text-gray-700">Total Amount: &#8358;{{ number_format($totalBank['amount'], 2) }}</p>
            </div>
        </div>

        <!-- Total Amount Card -->
        <div class="bg-yellow-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-wallet text-yellow-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-yellow-800">Total Amount</h2>
                </div>
                <hr class="border-yellow-600 mb-2">
                <p class="text-xl font-bold text-gray-900">&#8358;{{ number_format($totalAmount, 2) }}</p>
            </div>
        </div>

        <!-- Weekly Orders Card -->
        <div class="bg-indigo-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-calendar-week text-indigo-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-indigo-800">Weekly Orders</h2>
                </div>
                <hr class="border-indigo-600 mb-2">
                <p class="text-xl font-bold text-gray-900">{{ $totalWeeklyOrders }}</p>
                <p class="text-sm font-small text-gray-700">Total Amount: &#8358;{{ number_format($totalWeeklyAmount, 2) }}</p>
            </div>
        </div>

        <!-- Monthly Orders Card -->
        <div class="bg-red-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-calendar-alt text-red-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-red-800">Monthly Orders</h2>
                </div>
                <hr class="border-red-600 mb-2">
                <p class="text-xl font-bold text-gray-900">{{ $totalMonthlyOrders }}</p>
                <p class="text-sm font-small text-gray-700">Total Amount: &#8358;{{ number_format($totalMonthlyAmount, 2) }}</p>
            </div>
        </div>

        @foreach($storeTotals as $totals)
        <div class="bg-blue-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-store text-blue-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-blue-800">{{ $totals['name'] }}</h2>
                </div>
                <hr class="border-blue-600 mb-2">
                <p class="text-sm font-small text-gray-700">Total Orders: {{ $totals['totalOrders'] }}</p>
                <p class="text-sm font-small text-gray-700">Total Amount: &#8358;{{ number_format($totals['totalAmount'], 2) }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Order Items Table -->
    <div class="bg-white shadow-lg rounded-lg p-6 mt-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Order Items</h2>
        <div class="mb-4">
            <label for="filter" class="block text-sm font-medium text-gray-700">Filter by Payment Method:</label>
            <select id="filter" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="all">All</option>
                <option value="cash">Cash</option>
                <option value="pos">POS</option>
                <option value="bank">Bank</option>
            </select>
        </div>

        @if($orderItems->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Store</th> 
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User Name</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Payment Method</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Order Date</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="orderItemsTable">
                    @foreach($orderItems as $item)
                    <tr data-payment-method="{{ $item->payment_method }}" class="hover:bg-gray-100 transition duration-150">
                        <td class="px-2 py-1 text-sm font-medium text-gray-900">POS/{{ $item->id }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">{{ $item->store->name }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">{{ $item->user->name }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">{{ ucfirst($item->payment_method) }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">{{ ucfirst($item->amount) }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($item->order_date)->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-2 py-1 text-sm text-gray-700">
                            <a href="{{ route('orders.show', $item->id) }}" class="flex items-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-150">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                {{ $orderItems->links() }}
            </div>
        </div>
        @else
        <p class="text-gray-600">No order items available.</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterSelect = document.getElementById('filter');
        const orderItemsTable = document.getElementById('orderItemsTable');

        if (filterSelect && orderItemsTable) {
            filterSelect.addEventListener('change', function () {
                const selectedValue = this.value;
                const rows = orderItemsTable.querySelectorAll('tr');

                rows.forEach(row => {
                    const paymentMethod = row.dataset.paymentMethod;
                    if (selectedValue === 'all' || paymentMethod === selectedValue) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection
