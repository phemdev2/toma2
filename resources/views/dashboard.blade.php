@extends('layouts.app')

@section('content')
<div class="subscription-card mb-8 p-6 bg-gray-100 rounded shadow">
    <div class="plan flex items-center gap-2 text-lg font-semibold mb-4">
        @php
            $icons = [
                'trial' => '⏳',
                'basic' => '💼',
                'etc' => '💼',
                'premium' => '✨'
            ];
        @endphp

        <span class="icon">{{ $icons[$subscriptionType] ?? '🌟' }}</span>
        <span>{{ ucfirst($subscriptionType) }} Plan</span>
    </div>

    @if ($isSubscriptionExpired)
        <p class="text-red-600 font-medium">Your subscription has expired. Please renew to access more features.</p>
        <button class="renew-btn mt-2 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">Renew Now</button>
    @else
        <p class="text-green-600">Your {{ ucfirst($subscriptionType) }} plan is active until: 
            <strong>{{ $subscriptionExpiryDate }}</strong>
        </p>

        <div id="countdown" class="mt-2 text-gray-800 text-sm"></div>

        <script>
            var countDownDate = new Date("{{ $subscriptionExpiryDate }}").getTime();

            var x = setInterval(function() {
                var now = new Date().getTime();
                var distance = countDownDate - now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (distance > 0) {
                    document.getElementById("countdown").innerHTML = 
                        days + "D " + hours + "h " + minutes + "m " + seconds + "s ";
                } else {
                    clearInterval(x);
                    document.getElementById("countdown").innerHTML = "EXPIRED";
                    document.querySelector('.renew-btn').style.display = 'block';
                }
            }, 1000);
        </script>
    @endif
</div>

<div class="container mx-auto px-2 py-2 h-full">
    {{-- Summary Totals Cards --}}
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
                    <p class="text-sm font-medium text-gray-700">
                        Total Amount: &#8358;{{ number_format($total['amount'], 2) }}
                    </p>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Store Totals --}}
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

    {{-- Order Items Table --}}
    <div class="bg-white shadow-lg rounded-lg p-6 mt-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Order Items</h2>

        {{-- Filter Dropdown --}}
        <div class="mb-4">
            <label for="filter" class="block text-sm font-medium text-gray-700">Filter by Payment Method:</label>
            <select 
                id="filter"
                class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                onchange="filterOrders()"
            >
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
                    <tr data-payment-method="{{ strtolower($item->payment_method) }}" class="hover:bg-gray-100 transition duration-150">
                        <td class="px-2 py-1 text-sm font-medium text-gray-900">POS/{{ $item->id }}</td>
                        <td class="px-2 py-1 text-sm text-gray-700">{{ $item->store->name ?? 'N/A' }}</td>
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

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $orderItems->links() }}
            </div>
        </div>
        @else
            <p class="text-gray-600">No order items available.</p>
        @endif
    </div>
</div>

{{-- JavaScript: Filter Table Rows by Payment Method --}}
<script>
    function filterOrders() {
        const filter = document.getElementById("filter").value.toLowerCase();
        const rows = document.querySelectorAll("#orderItemsTable tr");

        rows.forEach(row => {
            const paymentMethod = row.getAttribute("data-payment-method");
            if (filter === "all" || paymentMethod === filter) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
</script>
@endsection

