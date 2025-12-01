@extends('layouts.app')

@section('title', 'Store Inventories')

@section('content')
<div class="container mx-auto px-4">
    <div class="bg-purple-200 p-6 rounded-lg mb-4 shadow-md">
        <h1 class="text-2xl font-bold mb-4">Store Inventories</h1>

        <!-- 1. Search Bar -->
        <form method="GET" action="{{ route('store-inventories.index') }}" class="mb-4 flex space-x-2">
            <input type="text" name="search" id="searchBar" placeholder="Search by store or product..." 
                   class="border rounded-md px-2 py-1 w-full md:w-1/3" value="{{ request('search') }}">
            <button type="submit" class="bg-blue-600 text-white rounded-md px-4 py-1">Search</button>
        </form>

        <div class="mb-3">
            <!-- 2. Add New Inventory Button -->
            <a href="{{ route('store-inventories.create') }}" class="bg-green-600 text-white hover:bg-green-700 rounded-md px-4 py-2">Add New Inventory</a>
        </div>
    </div>

    @if($inventories->isEmpty())
        <p class="text-center text-gray-500">No inventories available.</p>
    @else
        <div class="overflow-x-auto">
            <!-- 3. Inventory Table -->
            <table class="min-w-full bg-white border border-gray-300 shadow-md">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b text-left">#</th> <!-- Number Column -->
                        <th class="py-2 px-4 border-b text-left">Store</th>
                        <th class="py-2 px-4 border-b text-left">Product</th>
                        <th class="py-2 px-4 border-b text-left">Quantity</th>
                        <th class="py-2 px-4 border-b text-left">User</th>
                        <th class="py-2 px-4 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventories as $index => $inventory)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">{{ $index + 1 }}</td> <!-- Display Row Number -->
                            <td class="py-2 px-4 border-b">{{ $inventory->store ? $inventory->store->name : 'N/A' }}</td>
                            <td class="py-2 px-4 border-b">{{ $inventory->product ? $inventory->product->name : 'N/A' }}</td>
                            <td class="py-2 px-4 border-b">{{ $inventory->quantity }}</td>
                            <td class="py-2 px-4 border-b">{{ $inventory->user ? $inventory->user->name : 'N/A' }}</td>
                            <td class="py-2 px-4 border-b flex items-center space-x-2">
                                <!-- 4. Action Buttons -->
                                <!-- <button class="bg-yellow-500 text-white hover:bg-yellow-600 rounded-md px-2 py-1" onclick="openModal('{{ $inventory->id }}', '{{ $inventory->product->name }}', {{ $inventory->quantity }})">Add Purchase</button> -->
                                <a href="{{ route('store-inventories.show', $inventory->id) }}" class="bg-blue-500 text-white hover:bg-blue-600 rounded-md px-2 py-1">View</a>
                                <a href="{{ route('store-inventories.edit', $inventory->id) }}" class="bg-yellow-500 text-white hover:bg-yellow-600 rounded-md px-2 py-1">Edit</a>
                                <form action="{{ route('store-inventories.destroy', $inventory->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white hover:bg-red-600 rounded-md px-2 py-1" onclick="return confirm('Are you sure you want to delete this inventory?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 5. Pagination Controls -->
        <div class="mt-4">
            {{ $inventories->links() }}
        </div>
    @endif
</div>

<!-- 6. Modal for Adding Purchases -->
<div id="addPurchaseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-md">
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
function openModal(inventoryId, productName, currentQuantity) {
    document.getElementById('inventory_id').value = inventoryId;
    document.getElementById('addPurchaseForm').action = `/store-inventories/${inventoryId}/add-purchase`; // Adjust the route as needed
    document.getElementById('addPurchaseModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('addPurchaseModal').classList.add('hidden');
}
</script>
@endsection
