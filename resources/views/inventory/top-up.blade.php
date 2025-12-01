@extends('layouts.app')

@section('title', 'Top Up Inventory')

@section('content')
    <div class="container mx-auto mt-5 px-4">
        <h1 class="text-3xl font-semibold mb-4">Top Up Inventory</h1>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6">
            <h5 class="text-xl font-semibold mb-4">Add Inventory</h5>

            <form action="{{ route('inventory.top-up') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="store_id" class="block text-sm font-medium text-gray-700">Store</label>
                        <select id="store_id" name="store_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="">Select Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700">Product</label>
                        <select id="product_id" name="product_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" id="quantity" name="quantity" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" min="1" required>
                    @error('quantity')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div id="stock-card" class="bg-gray-100 p-4 rounded-lg hidden">
                    <h5 class="text-lg font-semibold mb-2">Current Stock</h5>
                    <p id="stock-quantity" class="text-xl">0</p>
                </div>

                <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Top Up</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#store_id, #product_id').change(function() {
                var storeId = $('#store_id').val();
                var productId = $('#product_id').val();

                if (storeId && productId) {
                    $.ajax({
                        url: '{{ route('inventory.get-stock') }}',
                        method: 'GET',
                        data: {
                            store_id: storeId,
                            product_id: productId
                        },
                        success: function(response) {
                            $('#stock-quantity').text(response.quantity);
                            $('#stock-card').removeClass('hidden');
                        },
                        error: function() {
                            $('#stock-quantity').text('Error retrieving stock');
                            $('#stock-card').removeClass('hidden');
                        }
                    });
                } else {
                    $('#stock-card').addClass('hidden');
                }
            });
        });
    </script>
@endsection
