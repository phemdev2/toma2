@extends('layouts.app')

@section('title', 'Purchase Products')

@section('content')
<div class="container mx-auto px-4">
    <div class="bg-purple-200 p-6 rounded-lg mb-4">
        <h1 class="text-2xl font-bold mb-4">Purchase Products</h1>

        <!-- Store Selection -->
        <div class="mb-4">
    <label for="store_id" class="block text-sm font-medium text-gray-700">Select Store</label>
    <select name="store_id" id="store_id" required class="border rounded-md px-2 py-1 w-full">
        <option value="">Select a store</option>
        @foreach($stores as $store) <!-- Changed $stores to $store -->
            <option value="{{ $store->id }}">{{ $store->name }}</option>
        @endforeach
    </select>
</div>
        <!-- Product Search Bar -->
        <div class="mb-4">
            <label for="productSearch" class="block text-sm font-medium text-gray-700">Search Product</label>
            <input type="text" id="productSearch" placeholder="Search by name or barcode..." 
                   class="border rounded-md px-2 py-1 w-full" oninput="searchProducts(this.value)">
            <div id="productResults" class="mt-2 bg-white border rounded-md hidden"></div>
        </div>

        <!-- Product Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300" id="productTable">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-2 md:px-4 border-b text-left">Name</th>
                        <th class="py-2 px-2 md:px-4 border-b text-left">Barcode</th>
                        <th class="py-2 px-2 md:px-4 border-b text-left">Cost</th>
                        <th class="py-2 px-2 md:px-4 border-b text-left">Sale</th>
                        <th class="py-2 px-2 md:px-4 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody id="productBody">
                    <!-- Product rows will be appended here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Adding Purchases -->
<div id="addPurchaseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Add Purchase</h2>
        <form id="addPurchaseForm" method="POST" action="">
            @csrf
            <input type="hidden" name="inventory_id" id="inventory_id">
            <div class="mb-4">
                <label for="purchase_quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" id="purchase_quantity" name="purchase_quantity" required min="1" class="border rounded-md px-2 py-1 w-full">
            </div>
            <button type="submit" class="bg-blue-600 text-white rounded-md px-4 py-2">Add</button>
            <button type="button" onclick="closeModal()" class="mt-2 bg-gray-500 text-white rounded-md px-4 py-2">Cancel</button>
        </form>
    </div>
</div>

<script>
function searchProducts(query) {
    if (query.length < 2) {
        document.getElementById('productResults').classList.add('hidden');
        return;
    }

    fetch(`/products/search?query=${query}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('productResults');
            resultsDiv.innerHTML = '';

            if (data.length === 0) {
                resultsDiv.classList.add('hidden');
                return;
            }

            data.forEach(product => {
                const div = document.createElement('div');
                div.classList.add('cursor-pointer', 'p-2', 'hover:bg-gray-100');
                div.textContent = product.name;
                div.onclick = () => selectProduct(product);
                resultsDiv.appendChild(div);
            });

            resultsDiv.classList.remove('hidden');
        });
}

function selectProduct(product) {
    const row = document.createElement('tr');
    row.classList.add('hover:bg-gray-50');
    row.innerHTML = `
        <td class="py-2 px-2 md:px-4 border-b">${product.name}</td>
        <td class="py-2 px-2 md:px-4 border-b">${product.barcode}</td>
        <td class="py-2 px-2 md:px-4 border-b">$${product.cost.toFixed(2)}</td>
        <td class="py-2 px-2 md:px-4 border-b">$${product.sale.toFixed(2)}</td>
        <td class="py-2 px-2 md:px-4 border-b flex items-center space-x-2">
            <button class="bg-yellow-500 text-white hover:bg-yellow-600 rounded-md px-2 py-1" onclick="openModal('${product.id}', '${product.name}')">Add Purchase</button>
        </td>
    `;
    document.getElementById('productBody').appendChild(row);
    document.getElementById('productResults').classList.add('hidden');
}

function openModal(productId, productName) {
    document.getElementById('inventory_id').value = productId;
    document.getElementById('addPurchaseForm').action = `/purchases/${productId}/add-purchase`; // Adjust the route as necessary
    document.getElementById('addPurchaseModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('addPurchaseModal').classList.add('hidden');
}
</script>
@endsection