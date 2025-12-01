@extends('layouts.app')

@section('title', 'Edit Inventory')

@section('content')
<div class="container mx-auto px-4">
    <div class="bg-purple-200 p-6 rounded-lg mb-4">
        <h1 class="text-2xl font-bold mb-4">Edit Inventory</h1>

        <form method="POST" action="{{ route('store-inventories.update', $inventory->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="store_id" class="block text-sm font-medium text-gray-700">Store</label>
                <select name="store_id" id="store_id" required class="border rounded-md px-2 py-1 w-full">
                    <option value="">Select a store</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $store->id == $inventory->store_id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="product_id" class="block text-sm font-medium text-gray-700">Product</label>
                <select name="product_id" id="product_id" required class="border rounded-md px-2 py-1 w-full">
                    <option value="">Select a product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ $product->id == $inventory->product_id ? 'selected' : '' }}>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="quantity" id="quantity" required min="1" value="{{ $inventory->quantity }}" class="border rounded-md px-2 py-1 w-full">
            </div>

            <button type="submit" class="bg-blue-600 text-white rounded-md px-4 py-2">Update Inventory</button>
        </form>
    </div>
</div>
@endsection