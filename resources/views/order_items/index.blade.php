@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-5">
    <h1 class="text-2xl font-bold mb-4">Order Items</h1>
    <table class="min-w-full border-collapse border border-gray-200">
        <thead>
            <tr>
                <th class="border border-gray-300 px-2 py-1">ID</th>
                <th class="border border-gray-300 px-2 py-1">Product</th>
                <th class="border border-gray-300 px-2 py-1">Variant</th>
                <th class="border border-gray-300 px-2 py-1">Quantity</th>
                <th class="border border-gray-300 px-2 py-1">Price</th>
                <th class="border border-gray-300 px-2 py-1">Store ID</th>
                <th class="border border-gray-300 px-2 py-1">Created At</th>
                <th class="border border-gray-300 px-2 py-1">Updated At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderItems as $item)
                <tr>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->id }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->variant->name ?? 'N/A' }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->quantity }}</td>
                    <td class="border border-gray-300 px-2 py-1">&#8358;{{ number_format($item->price, 2) }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->store_id }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->created_at }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $item->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection