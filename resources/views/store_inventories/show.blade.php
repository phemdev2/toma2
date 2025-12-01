@extends('layouts.app')

@section('title', 'Store Inventory Details')

@section('content')

<div class="container mx-auto p-4">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-purple-500">{{ $store->name }} Inventory</h1>

        <!-- Display Success or Error Messages -->
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

        <!-- Inventory List -->
        <h3 class="text-xl font-semibold mb-4">Inventory:</h3>
        <table class="min-w-full table-auto">
            <thead>
                <tr class="border-b">
                    <th class="px-4 py-2 text-left">Product</th>
                    <th class="px-4 py-2 text-left">Quantity</th>
                    <th class="px-4 py-2 text-left">Last Updated By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventories as $inventory)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $inventory->product->name }}</td>
                        <td class="px-4 py-2">{{ $inventory->quantity }}</td>
                        <td class="px-4 py-2">{{ $inventory->user ? $inventory->user->name : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Add Purchase/Deduction Form -->
        <h3 class="text-xl font-semibold mt-6 mb-4">Add Purchase or Deduction:</h3>
        <form action="{{ route('store-inventories.add-purchase', $store->id) }}" method="POST">
            @csrf
            <div class="flex space-x-4 mb-4">
                <!-- Select Product -->
                <div class="flex-1">
                    <label for="product_id" class="sr-only">Select Product</label>
                    <select name="product_id" id="product_id" class="form-select block w-full border-gray-300 rounded-md shadow-sm py-3 pl-4 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" required>
                        <option value="" disabled selected>Select a product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Quantity Input (Accepts Positive or Negative Values) -->
                <div class="flex-1">
                    <label for="quantity" class="sr-only">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-4 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" placeholder="Enter quantity (negative for deduction)" step="any" required>
                    @error('quantity')
                        <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150 ease-in-out">Submit</button>
        </form>

        <!-- Purchase History -->
        <h3 class="text-xl font-semibold mt-6 mb-4">Recent Purchases/Deductions:</h3>
        <table class="min-w-full table-auto">
            <thead>
                <tr class="border-b">
                    <th class="px-4 py-2 text-left">Product</th>
                    <th class="px-4 py-2 text-left">Quantity</th>
                    <th class="px-4 py-2 text-left">Updated By</th>
                    <th class="px-4 py-2 text-left">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseHistories as $purchase)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $purchase->product->name }}</td>
                        <td class="px-4 py-2">{{ $purchase->quantity }}</td>
                        <td class="px-4 py-2">{{ $purchase->user->name }}</td>
                        <td class="px-4 py-2">{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6">
            <a href="{{ route('store-inventories.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back to Inventories</a>
        </div>
    </div>
</div>

@endsection
