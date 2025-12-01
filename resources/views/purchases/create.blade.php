@extends('layouts.app')

@section('title', 'Add Inventory')

@section('content')
    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6 text-purple-500">Add Purchase</h1>

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

            <form action="{{ route('purchases.store') }}" method="POST">
                @csrf

                <div class="flex space-x-4 mb-4">
                    <div class="flex-1">
                        <label for="store_id" class="sr-only">Select Store</label>
                        <div class="relative">
                            <select name="store_id" id="store_id" class="form-select block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4" required>
                                <option value="" disabled selected>Select a store</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('store_id')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div id="items-container">
                    <!-- Dynamic Product and Quantity Fields will go here -->
                </div>

                <button type="button" class="mt-4 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600" onclick="addItem()">Add Another Item</button>
                <button type="submit" class="mt-4 bg-purple-500 text-white py-2 px-4 rounded hover:bg-purple-600">Submit Purchase</button>
            </form>
        </div>
    </div>

    <script>
        let itemIndex = 0;

        // Function to add more items to the form dynamically
        function addItem() {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.classList.add('item-entry', 'mb-4', 'flex', 'space-x-4');
            newItem.innerHTML = `
                <div class="flex-1">
                    <label for="product_search_${itemIndex}" class="sr-only">Search Product</label>
                    <input type="text" name="products[${itemIndex}][name]" id="product_search_${itemIndex}" class="form-input block w-full border-gray-300 rounded-md shadow-sm" placeholder="Search by name or barcode">
                    <input type="hidden" name="products[${itemIndex}][product_id]" id="product_id_${itemIndex}">
                </div>
                <div class="flex-1">
                    <label for="quantity_${itemIndex}" class="sr-only">Quantity</label>
                    <input type="number" name="products[${itemIndex}][quantity]" id="quantity_${itemIndex}" placeholder="Quantity" class="form-input block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <button type="button" class="remove-item text-red-500" onclick="removeItem(${itemIndex})">Remove</button>
            `;
            container.appendChild(newItem);
            itemIndex++;
        }

        // Function to remove an item
        function removeItem(index) {
            const itemToRemove = document.querySelector(`#items-container .item-entry:nth-child(${index + 1})`);
            itemToRemove.remove();
        }
    </script>
@endsection
