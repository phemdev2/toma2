@extends('layouts.app')

@section('title', 'Add Inventory')

@section('content')
    <!-- Available Quantities Section -->
    <div id="available_quantities" class="mb-4">
        <h3 class="font-bold">Available Quantities in Stores:</h3>
        <ul id="quantity_list" class="list-disc pl-5"></ul>
    </div>

    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6 text-purple-500">Add Purchase</h1>

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

            <!-- Form for Adding Inventory -->
            <form action="{{ route('store-inventories.store') }}" method="POST">
                @csrf

                <div class="flex space-x-4 mb-4">
                    <!-- Store Selection -->
                    <div class="flex-1">
                        <label for="store_id" class="sr-only">Select Store</label>
                        <div class="relative">
                            <i class="fas fa-store absolute ml-3 text-gray-400 top-3 left-0"></i>
                            <select name="store_id" id="store_id" class="form-select block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" required>
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

                <!-- Dynamic Items Section -->
                <div id="items-container">
                    <div class="item-entry mb-4 flex space-x-4">
                        <!-- Product Search Input -->
                        <div class="flex-1">
                            <label for="product_search" class="sr-only">Search Product</label>
                            <div class="relative">
                                <i class="fas fa-search absolute ml-3 text-gray-400 top-3 left-0"></i>
                                <input type="text" name="products[0][name]" id="product_search_0" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" placeholder="Search by name or barcode" aria-describedby="product_search_help">
                                <div id="product_results_0" class="mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden"></div>
                            </div>
                            @error('products.0.product_id')
                                <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quantity Input -->
                        <div class="flex-1">
                            <label for="quantity_0" class="sr-only">Quantity</label>
                            <div class="relative">
                                <input type="number" name="products[0][quantity]" id="quantity_0" placeholder="Quantity" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" required>
                            </div>
                            @error('products.0.quantity')
                                <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remove Item Button -->
                        <div class="flex items-center">
                            <button type="button" class="remove-item text-red-500" onclick="removeItem(0)">Remove</button>
                        </div>
                    </div>
                </div>

                <!-- Add Another Item Button -->
                <button type="button" class="mt-4 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150 ease-in-out" onclick="addItem()">Add Another Item</button>

                <!-- Submit Button -->
                <button type="submit" class="mt-4 px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150 ease-in-out">Purchase</button>
            </form>
        </div>
    </div>

    <script>
        let itemIndex = 1;

        // Add a new item entry
        function addItem() {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.classList.add('item-entry', 'mb-4', 'flex', 'space-x-4');
            newItem.innerHTML = `
                <div class="flex-1">
                    <label for="product_search_${itemIndex}" class="sr-only">Search Product</label>
                    <div class="relative">
                        <i class="fas fa-search absolute ml-3 text-gray-400 top-3 left-0"></i>
                        <input type="text" name="products[${itemIndex}][name]" id="product_search_${itemIndex}" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" placeholder="Search by name or barcode" aria-describedby="product_search_help">
                        <div id="product_results_${itemIndex}" class="mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden"></div>
                    </div>
                </div>

                <div class="flex-1">
                    <label for="quantity_${itemIndex}" class="sr-only">Quantity</label>
                    <div class="relative">
                        <input type="number" name="products[${itemIndex}][quantity]" id="quantity_${itemIndex}" placeholder="Quantity" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" required>
                    </div>
                </div>

                <div class="flex items-center">
                    <button type="button" class="remove-item text-red-500" onclick="removeItem(${itemIndex})">Remove</button>
                </div>
            `;

            container.appendChild(newItem);
            itemIndex++;
        }

        // Remove an item entry
        function removeItem(index) {
            const itemToRemove = document.querySelector(`#items-container .item-entry:nth-child(${index + 1})`);
            itemToRemove.remove();
        }
    </script>

@endsection
