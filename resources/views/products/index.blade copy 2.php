@extends('layouts.app')

@section('title', 'Product List')

@section('content')
<div class="container mx-auto px-4">
    <!-- Card Background -->
    <div class="bg-purple-200 p-6 rounded-lg mb-4">
        <h1 class="text-2xl font-bold mb-4">Product List</h1>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('products.index') }}" class="mb-4">
            <input type="text" name="search" id="searchBar" placeholder="Search by name or barcode..." 
                   class="border rounded-md px-2 py-1 w-full md:w-1/3" value="{{ request('search') }}">
            <button type="submit" class="bg-blue-600 text-white rounded-md px-4 py-1 mt-2">Search</button>
        </form>
        <div class="mb-3">
            <a href="{{ route('products.create') }}" class="bg-green-600 text-white hover:bg-green-700 rounded-md px-4 py-2">Create New Product</a>
            <a href="{{ route('products.download.csv') }}" class="bg-gray-600 text-white hover:bg-gray-700 rounded-md px-4 py-2 ml-2">Download CSV</a>
            <a href="{{ route('products.download.pdf') }}" class="bg-gray-600 text-white hover:bg-gray-700 rounded-md px-4 py-2 ml-2">Download PDF</a>
        </div>
    </div>

    <!-- Product Table -->
    @if($productsWithVariants->isEmpty())
        <p>No products available.</p>
    @else
        <div class="overflow-x-auto">
            <table id="productTable" class="min-w-full bg-white border border-gray-300">
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
                    @foreach($productsWithVariants as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-2 md:px-4 border-b">{{ $product->name }}</td>
                            <td class="py-2 px-2 md:px-4 border-b">{{ $product->barcode }}</td>
                            <td class="py-2 px-2 md:px-4 border-b">${{ number_format($product->cost, 2) }}</td>
                            <td class="py-2 px-2 md:px-4 border-b">${{ number_format($product->sale, 2) }}</td>
                            <td class="py-2 px-2 md:px-4 border-b flex items-center space-x-2">
                                <button class="bg-yellow-500 text-white hover:bg-yellow-600 rounded-md px-2 py-1" onclick="openModal('{{ $product->id }}')">View Details</button>
                                <a href="{{ route('products.edit', $product->id) }}" class="bg-blue-500 text-white hover:bg-blue-600 rounded-md px-2 py-1">Edit</a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white hover:bg-red-600 rounded-md px-2 py-1" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div id="paginationControls" class="flex justify-center mt-4"></div>
    @endif
</div>

<!-- Modal for Product Details -->
<div class="backdrop-blur-lg">
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Product Details</h2>
        <div id="detailsContent"></div>
        <button onclick="closeModal()" class="mt-4 bg-gray-500 text-white hover:bg-gray-600 rounded-md px-4 py-2">Close</button>
    </div>
</div>

</div>
<script>
<script>
function openModal(productId) {
    fetch(`/products/${productId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            let content = `<p><strong>Name:</strong> ${data.name}</p>
                           <p><strong>Barcode:</strong> ${data.barcode}</p>
                           <p><strong>Cost:</strong> $${data.cost.toFixed(2)}</p>
                           <p><strong>Sale:</strong> $${data.sale.toFixed(2)}</p>
                           <h3 class="mt-4 font-bold">Variants:</h3>
                           <ul class="list-disc pl-5">`;
            data.variants.forEach(variant => {
                content += `<li>${variant.unit_type} - ${variant.unit_qty} units - $${variant.price.toFixed(2)}</li>`;
            });
            content += '</ul>';
            document.getElementById('detailsContent').innerHTML = content;
            document.getElementById('detailsModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
        });
}

function closeModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}
</script>

</script>
@endsection
