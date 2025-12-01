@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container mx-auto p-6 space-y-8">

    <!-- Product Header -->
    <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-x-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
            <div class="text-sm text-gray-500 space-y-1">
                <p><strong>Barcode:</strong> {{ $product->barcode }}</p>
                <p><strong>Cost:</strong> &#8358;{{ number_format($product->cost, 2) }}</p>
                <p><strong>Sale Price:</strong> &#8358;{{ number_format($product->sale ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Variants Section -->
    <div class="bg-white shadow-lg rounded-lg p-6 space-y-4">
        <h3 class="text-2xl font-semibold text-gray-900">Variants</h3>
        <ul class="list-disc pl-5 space-y-2 text-gray-700">
            @forelse($product->variants as $variant)
                <li>{{ $variant->unit_type }} - {{ $variant->unit_qty }} units - &#8358;{{ number_format($variant->price, 2) }}</li>
            @empty
                <li class="text-gray-500">No variants available.</li>
            @endforelse
        </ul>
    </div>

    <!-- Store Inventory Section -->
    <div class="bg-white shadow-lg rounded-lg p-6 space-y-4">
        <h3 class="text-2xl font-semibold text-gray-900">Store Inventory</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 table-auto rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-left">Store</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Total Quantity</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Batch Number</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Expiry Date</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Last Updated By</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($quantitiesByStore as $data)
                        @php
                            $store = $data['store'];
                            $totalQuantity = $data['totalQuantity'];
                            $lastUpdatedBy = $data['lastUpdatedBy'];
                            $batches = $data['batches'];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-2">{{ $store->name ?? 'Unknown Store' }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ number_format($totalQuantity, 0) . ' units' }}</td>
                            <td colspan="3" class="border border-gray-300 px-4 py-2">
                                @foreach($batches as $batch)
                                    <div class="text-sm">
                                        <strong>Batch Number:</strong> {{ $batch['batch_number'] ?? 'N/A' }} - 
                                        <strong>Total Quantity:</strong> {{ number_format($batch['totalQuantity'], 0) }} units - 
                                        <strong>Expiry Date:</strong> {{ $batch['expiry_date'] ? \Carbon\Carbon::parse($batch['expiry_date'])->format('d/m/Y') : 'N/A' }}
                                    </div>
                                @endforeach
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ $lastUpdatedBy ? $lastUpdatedBy->name . ' (ID: ' . $lastUpdatedBy->id . ')' : 'N/A' }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <form action="{{ route('store-inventory.add', ['store_id' => $store->id, 'product_id' => $product->id]) }}" method="POST" class="inline-flex items-center space-x-2">
                                    @csrf
                                    <input type="number" name="quantity" placeholder="Add Qty" required class="border rounded-md px-4 py-2 w-full max-w-xs focus:outline-none focus:ring focus:ring-green-500" title="Enter quantity to add">
                                    <button type="submit" class="bg-green-500 text-white hover:bg-green-600 rounded-md px-4 py-2 transition duration-200">Add</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border border-gray-300 px-4 py-2 text-center text-gray-500">No inventory data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Total Quantity Section -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h4 class="text-xl font-semibold text-gray-900">Total Quantity Across All Stores</h4>
        <p class="text-lg text-gray700">{{ number_format($totalQuantity, 0) . ' units' }}</p>
    </div>

    <!-- Success/Failure Message -->
    @if(session('success'))
        <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

</div>

<!-- Accessibility Enhancements -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.querySelector('table');
        table.setAttribute('role', 'table');
        table.querySelector('thead').setAttribute('role', 'rowgroup');
        table.querySelectorAll('thead th').forEach(th => th.setAttribute('role', 'columnheader'));
        table.querySelector('tbody').setAttribute('role', 'rowgroup');
        table.querySelectorAll('tbody tr').forEach(row => {
            row.setAttribute('role', 'row');
            row.querySelectorAll('td').forEach(td => td.setAttribute('role', 'cell'));
        });
    });
</script>

@endsection
