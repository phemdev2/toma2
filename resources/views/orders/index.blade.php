@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-5 px-4">
    <!-- Orders Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Orders List</h1>
    </div>

    <!-- Orders Filter Form -->
    <form method="GET" action="{{ route('orders.index') }}" class="mb-6 bg-purple-100 p-4 rounded-lg shadow-md flex flex-wrap gap-4">
        <div>
            <label for="payment-method" class="block text-sm font-medium">Payment Method</label>
            <select name="payment_method" id="payment-method" class="border rounded px-2 py-1 text-sm">
                <option value="">All</option>
                <option value="Cash" {{ request('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                <option value="POS" {{ request('payment_method') == 'POS' ? 'selected' : '' }}>POS</option>
                <option value="Bank" {{ request('payment_method') == 'Bank' ? 'selected' : '' }}>Bank</option>
            </select>
        </div>
        <div>
            <label for="start-date" class="block text-sm font-medium">Start Date</label>
            <input type="date" name="start_date" id="start-date" value="{{ request('start_date') }}" class="border rounded px-2 py-1 text-sm">
        </div>
        <div>
            <label for="end-date" class="block text-sm font-medium">End Date</label>
            <input type="date" name="end_date" id="end-date" value="{{ request('end_date') }}" class="border rounded px-2 py-1 text-sm">
        </div>
        <div>
            <label for="sort_by" class="block text-sm font-medium">Sort By</label>
            <select name="sort_by" id="sort_by" class="border rounded px-2 py-1 text-sm">
                <option value="desc" {{ request('sort_by') == 'desc' ? 'selected' : '' }}>Newest First</option>
                <option value="asc" {{ request('sort_by') == 'asc' ? 'selected' : '' }}>Oldest First</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                Apply Filters
            </button>
        </div>
    </form>

    <!-- Order Receipts -->
    <div>
        @forelse($orders as $order)
        <div id="receipt-{{ $order->id }}" class="bg-white shadow-md rounded-lg p-6 mb-6">
            <!-- Receipt Header -->
            <h2 class="text-xl font-bold mb-1">Order Receipt #{{ $order->id }}</h2>
            <p class="text-sm mb-3">
                <strong>Store:</strong> {{ $order->store->name ?? 'N/A' }} |
                <strong>Order Date:</strong> {{ $order->order_date }} |
                <strong>Served By:</strong> {{ $order->user->name ?? 'N/A' }}
            </p>

            <!-- Order Items Table -->
            <table class="w-full border border-gray-300 mb-4">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-2 py-1 text-left text-sm">Product</th>
                        <th class="border px-2 py-1 text-left text-sm">Variant</th>
                        <th class="border px-2 py-1 text-left text-sm">Qty</th>
                        <th class="border px-2 py-1 text-left text-sm">Price</th>
                        <th class="border px-2 py-1 text-left text-sm">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="border px-2 py-1 text-sm">{{ $item->product->name ?? 'N/A' }}</td>
                        <td class="border px-2 py-1 text-sm">{{ $item->variant->variant_name ?? 'Unit' }}</td>
                        <td class="border px-2 py-1 text-sm">{{ $item->quantity }}</td>
                        <td class="border px-2 py-1 text-sm">&#8358;{{ number_format($item->price, 2) }}</td>
                        <td class="border px-2 py-1 text-sm">&#8358;{{ number_format($item->totalPrice(), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Footer Section -->
            <div class="flex justify-between items-center">
                <strong class="text-lg">Total: &#8358;{{ number_format($order->totalPrice(), 2) }}</strong>
                <button onclick="printReceipt({{ $order->id }})" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                    Print Receipt
                </button>
            </div>
        </div>
        @empty
        <p class="text-gray-600">No orders found for the selected filters.</p>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    function printReceipt(orderId) {
        const receipt = document.getElementById('receipt-' + orderId);
        if (!receipt) {
            alert('Receipt not found!');
            return;
        }

        const printWindow = window.open('', '_blank', 'width=800,height=600');
        const styles = `
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h1, h2, p { margin: 0 0 10px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 14px; }
                .total { font-weight: bold; margin-top: 10px; }
                button { display: none; }
            </style>
        `;
        printWindow.document.write(`
            <html>
            <head>
                <title>Print Receipt #${orderId}</title>
                ${styles}
            </head>
            <body>${receipt.innerHTML}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>
@endpush
