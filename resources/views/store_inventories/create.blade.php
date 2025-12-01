@extends('layouts.app')

@section('title', 'Add Inventory')

@section('content')

<!-- Available Quantities Section -->
<div id="available_quantities" class="mb-4 ">
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

                <!-- Product Search Input -->
                <div class="flex-1">
                    <label for="product_search" class="sr-only">Search Product</label>
                    <div class="relative">
                        <i class="fas fa-search absolute ml-3 text-gray-400 top-3 left-0"></i>
                        <input type="text" id="product_search" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" placeholder="Search by name or barcode" aria-describedby="product_search_help">
                        <div id="product_results" class="mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden"></div>
                    </div>
                    @error('product_id')
                        <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Quantity Input -->
            <div class="mb-4">
                <label for="quantity" class="sr-only">Quantity</label>
                <div class="relative">
                    <i class="fas fa-box absolute ml-3 text-gray-400 top-3 left-0"></i>
                    <input type="number" name="quantity" id="quantity" placeholder="Quantity" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" required>
                </div>
                @error('quantity')
                    <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Batch Number Input -->
            <div class="mb-4">
                <label for="batch_number" class="sr-only">Batch Number</label>
                <div class="relative">
                    <i class="fas fa-hashtag absolute ml-3 text-gray-400 top-3 left-0"></i>
                    <input type="text" name="batch_number" id="batch_number" placeholder="Batch Number" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" required>
                </div>
                @error('batch_number')
                    <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Expiry Date Input -->
            <div class="mb-4">
                <label for="expiry_date" class="sr-only">Expiry Date</label>
                <div class="relative">
                    <i class="fas fa-calendar-alt absolute ml-3 text-gray-400 top-3 left-0"></i>
                    <input type="date" name="expiry_date" id="expiry_date" class="form-input block w-full border-gray-300 rounded-md shadow-sm py-3 pl-10 pr-4 focus:ring focus:ring-blue-300 transition duration-150 ease-in-out" required>
                </div>
                @error('expiry_date')
                    <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Hidden Input for Selected Product -->
            <input type="hidden" name="product_id" id="product_id">

            <!-- Available Quantities Section -->
            <div id="available_quantities" class="mb-4 hidden">
                <h3 class="font-bold">Available Quantities in Stores:</h3>
                <ul id="quantity_list" class="list-disc pl-5"></ul>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="mt-4 px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150 ease-in-out">Purchase</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('product_search');
        const resultsContainer = document.getElementById('product_results');
        const productIdInput = document.getElementById('product_id');
        const availableQuantitiesContainer = document.getElementById('available_quantities');
        const quantityList = document.getElementById('quantity_list');

        searchInput.addEventListener('input', function() {
            const searchQuery = searchInput.value;

            if (searchQuery.length < 2) {
                resultsContainer.classList.add('hidden');
                availableQuantitiesContainer.classList.add('hidden');
                return;
            }

            fetch(`{{ route('search.products') }}?search=${searchQuery}`)
                .then(response => response.json())
                .then(products => {
                    if (products.length > 0) {
                        resultsContainer.innerHTML = products.map(product => `  
                            <div class="p-2 cursor-pointer hover:bg-gray-100" data-product-id="${product.id}" data-product-name="${product.name}">
                                <strong>${product.name}</strong><br>
                                <small>${product.barcode}</small>
                            </div>
                        `).join('');
                        resultsContainer.classList.remove('hidden');
                    } else {
                        resultsContainer.innerHTML = '<div class="p-2">No products found</div>';
                        resultsContainer.classList.remove('hidden');
                    }
                });
        });

        resultsContainer.addEventListener('click', function(event) {
            const target = event.target.closest('div[data-product-id]');
            if (target) {
                const productId = target.getAttribute('data-product-id');
                const productName = target.getAttribute('data-product-name');

                searchInput.value = productName;
                productIdInput.value = productId;
                resultsContainer.classList.add('hidden');

                // Fetch available quantities for the selected product
                fetch(`/products/${productId}/quantities`)
                    .then(response => response.json())
                    .then(data => {
                        quantityList.innerHTML = '';
                        data.forEach(store => {
                            const li = document.createElement('li');
                            li.textContent = `${store.store_name}: ${store.quantity} units`;
                            quantityList.appendChild(li);
                        });
                        availableQuantitiesContainer.classList.remove('hidden');
                    });
            }
        });

        document.addEventListener('click', function(event) {
            if (!resultsContainer.contains(event.target) && event.target !== searchInput) {
                resultsContainer.classList.add('hidden');
            }
        });
    });
</script>

<!-- Include Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM4+2T74q0u8PBlV0dV6G1x4g9Obq4jibp4deh" crossorigin="anonymous">

@endsection
