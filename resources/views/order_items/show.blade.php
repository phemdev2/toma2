@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-5">
    <h1 class="text-2xl font-bold mb-4">Order Details</h1>
    <p>Store ID: {{ $storeId }}</p>
    <h2 class="text-xl mt-4">Order Items</h2>
    <table class="min-w-full border-collapse border border-gray-200">
        <thead>
            <tr>
                <th class="border border-gray-300 px-2 py-1">ID</th>
                <th class="border border-gray-300 px-2 py-1">Product</th>
                <th class="border border-gray-300 px-2 py-1">Variant</th>
                <th class="border border-gray-300 px-2 py-1">Quantity</th>
                <th class="border border-gray-300 px-2 py-1">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->id }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->variant->name ?? 'N/A' }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->quantity }}</td>
                    <td class="border border-gray-300 px-2 py-1">&#8358;{{ number_format($item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection