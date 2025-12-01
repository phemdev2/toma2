@extends('layouts.app') 

@section('content')
<div class="container mx-auto px-2 py-2 h-full">

    <!-- Grid of Cards for Totals -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach ([
            ['color' => 'purple', 'icon' => 'money-bill-wave', 'title' => 'Total Cash Orders', 'count' => $totalCash['count'], 'amount' => $totalCash['amount']],
            ['color' => 'green', 'icon' => 'credit-card', 'title' => 'Total POS Orders', 'count' => $totalPOS['count'], 'amount' => $totalPOS['amount']],
            ['color' => 'blue', 'icon' => 'university', 'title' => 'Total Bank Orders', 'count' => $totalBank['count'], 'amount' => $totalBank['amount']],
            ['color' => 'yellow', 'icon' => 'wallet', 'title' => 'Total Amount', 'count' => null, 'amount' => $totalAmount],
            ['color' => 'indigo', 'icon' => 'calendar-week', 'title' => 'Weekly Orders', 'count' => $totalWeeklyOrders, 'amount' => $totalWeeklyAmount],
            ['color' => 'red', 'icon' => 'calendar-alt', 'title' => 'Monthly Orders', 'count' => $totalMonthlyOrders, 'amount' => $totalMonthlyAmount]
        ] as $total)
        <div class="bg-{{ $total['color'] }}-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-{{ $total['icon'] }} text-{{ $total['color'] }}-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-{{ $total['color'] }}-800">{{ $total['title'] }}</h2>
                </div>
                <hr class="border-{{ $total['color'] }}-600 mb-2">
                <p class="text-xl font-bold text-gray-900">{{ $total['count'] ?? '' }}</p>
                @if (isset($total['amount']))
                <p class="text-sm font-medium text-gray-700">Total Amount: &#8358;{{ number_format($total['amount'], 2) }}</p>
                @endif
            </div>
        </div>
        @endforeach

        @foreach($storeTotals as $totals)
    <div class="bg-blue-200 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col justify-between">
        <div>
            <div class="flex items-center mb-2">
                <i class="fas fa-store text-blue-800 mr-2"></i>
                <h2 class="text-lg font-semibold text-blue-800">{{ $totals['name'] }}</h2>
            </div>
            <hr class="border-blue-600 mb-2">
            <p class="text-sm text-gray-700">Total Orders: <span class="font-bold">{{ $totals['total_orders'] }}</span></p>
            <p class="text-sm text-gray-700">Total Amount: <span class="font-bold">&#8358;{{ number_format($totals['total_amount'], 2) }}</span></p>
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
                        @foreach (['Order ID', 'Store', 'User Name', 'Payment Method', 'Amount', 'Order Date', 'Actions'] as $header)
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="orderItemsTable">
                    @foreach($orderItems as $item)
                    <tr data-payment-method="{{ $item->payment_method }}" class="hover:bg-gray-100 transition duration-150">
                        <td class="px-2 py-1 text-sm font-medium text-gray-900">POS/{{ $item->id }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700"> {{ $item->store ? $item->store->name : 'N/A' }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">{{ $item->user->name }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">{{ ucfirst($item->payment_method) }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">&#8358;{{ number_format($item->amount, 2) }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">{{ \Carbon\Carbon::parse($item->order_date)->format('Y-m-d H:i:s') }}</td>
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
