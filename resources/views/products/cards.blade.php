@extends('layouts.app')

@section('title', 'Product List')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-6">Product List</h1>

    <!-- Search and View Toggle -->
    <div class="mb-6">
        <input type="text" id="search-input" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500" placeholder="Search by Name or Barcode">
        
        <div class="mt-4 flex space-x-4">
            <a href="{{ route('products.cards', ['view' => 'table']) }}" class="px-4 py-2 text-white bg-gray-700 rounded-md hover:bg-gray-800 {{ request('view') == 'table' ? 'bg-blue-500' : '' }}">Table View</a>
            <a href="{{ route('products.cards', ['view' => 'cards']) }}" class="px-4 py-2 text-white bg-gray-700 rounded-md hover:bg-gray-800 {{ request('view') == 'cards' ? 'bg-blue-500' : '' }}">Card View</a>
        </div>
    </div>

    <!-- Check for view mode and display accordingly -->
    @if(request('view') == 'cards')
        <div id="card-view" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="bg-white p-4 rounded-lg shadow-md" data-name="{{ $product->name }}" data-barcode="{{ $product->barcode }}">
                    <h5 class="text-lg font-semibold">{{ $product->name }}</h5>
                    <p class="text-gray-600"><strong>Barcode:</strong> {{ $product->barcode }}</p>
                    <p class="text-gray-600"><strong>Cost:</strong> {{ number_format($product->cost, 2) }}</p>
                    <p class="text-gray-600"><strong>Sale Price:</strong> {{ $product->sale ? number_format($product->sale, 2) : 'N/A' }}</p>
                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('products.edit', $product->id) }}" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Edit</a>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div id="table-view" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                        <tr class="hover:bg-gray-50" data-name="{{ $product->name }}" data-barcode="{{ $product->barcode }}">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $product->barcode }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ number_format($product->cost, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $product->sale ? number_format($product->sale, 2) : 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <a href="{{ route('products.edit', $product->id) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- JavaScript for Filtering -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const cardView = document.getElementById('card-view');
        const tableView = document.getElementById('table-view');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();

            // Filter Card View
            if (cardView) {
                const cards = cardView.querySelectorAll('.product-card');
                cards.forEach(card => {
                    const name = card.getAttribute('data-name').toLowerCase();
                    const barcode = card.getAttribute('data-barcode').toLowerCase();
                    card.style.display = (name.includes(query) || barcode.includes(query)) ? '' : 'none';
                });
            }

            // Filter Table View
            if (tableView) {
                const rows = tableView.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name').toLowerCase();
                    const barcode = row.getAttribute('data-barcode').toLowerCase();
                    row.style.display = (name.includes(query) || barcode.includes(query)) ? '' : 'none';
                });
            }
        });
    });
</script>
@endsection
