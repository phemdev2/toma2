<!-- resources/views/store_inventories/purchase.blade.php -->

@extends('layouts.app')

@section('title', 'Add Purchase')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Add Purchase</h1>

        <!-- Display success or error messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('store-inventories.add-purchase', $inventory->id) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="store_id" class="block text-gray-700">Store</label>
                <select name="store_id" id="store_id" class="form-select mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $store->id == $inventory->store_id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
                @error('store_id')
                    <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="product_id" class="block text-gray-700">Product</label>
                <select name="product_id" id="product_id" class="form-select mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ $product->id == $inventory->product_id ? 'selected' : '' }}>{{ $product->name }}</option>
                    @endforeach
                </select>
                @error('product_id')
                    <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="purchase_quantity" class="block text-gray-700">Quantity</label>
                <input type="number" name="purchase_quantity" id="purchase_quantity" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @error('purchase_quantity')
                    <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Add Purchase</button>
        </form>
    </div>
@endsection
