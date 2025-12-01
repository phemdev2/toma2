@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6 text-gray-600">Add New Product</h1>

    <!-- Display validation errors if any -->
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 border border-red-300 rounded-lg p-4 mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form for creating a new product -->
    <form action="{{ route('products.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Product Name -->
            <div>
                <label for="name" class="block text-sm font-bold text-gray-700">Product Name</label>
                <input type="text" id="name" name="name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" value="{{ old('name') }}" required>
            </div>

            <!-- Barcode -->
            <div>
                <label for="barcode" class="block text-sm font-bold text-gray-700">Barcode</label>
                <input type="text" id="barcode" name="barcode" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" value="{{ old('barcode') }}">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Cost Price -->
            <div>
    <label for="cost" class="block text-sm font-bold text-gray-700">Cost Price</label>
    <input type="number" id="cost" name="cost" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" value="{{ old('cost', 0) }}" step="1" required>
</div>


            <!-- Selling Price -->
            <div>
                <label for="sale" class="block text-sm font-bold text-gray-700">Selling Price</label>
                <input type="number" id="sale" name="sale" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" value="{{ old('sale', 0) }}" step="0.01">
            </div>
        </div>

        <!-- Expiry Date -->
        <div class="mb-4">
            <label for="expiry_date" class="block text-sm font-bold text-gray-700">Expiry Date</label>
            <input type="date" id="expiry_date" name="expiry_date" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" value="{{ old('expiry_date') }}">
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="block text-sm font-bold text-gray-700">Description</label>
            <textarea id="description" name="description" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" rows="4">{{ old('description') }}</textarea>
        </div>

        <!-- Variants Section -->
        <div class="mb-4">
            <h2 class="text-sm font-bold text-gray-700">Variants (Optional)</h2>
            <div class="flex flex-col space-y-4" id="variants-container">
                <div class="flex space-x-2">
                    <input type="text" id="unit_type" name="unit_type[]" class="flex-1 p-2 border border-gray-300 rounded-md" placeholder="Unit Type">
                    <input type="number" name="unit_qty[]" class="flex-1 p-2 border border-gray-300 rounded-md" placeholder="Quantity">
                    <input type="number" id="price" name="price[]" class="flex-1 p-2 border border-gray-300 rounded-md" placeholder="Price" step="0.01">
                </div>
            </div>
            <button type="button" id="add-variant" class="mt-2 px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i> Add Variant
            </button>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-save mr-2"></i> Save Product
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('add-variant').addEventListener('click', function() {
        const variantRow = `
            <div class='flex space-x-2'>
                <input type='text' name='unit_type[]' class='flex-1 p-2 border border-gray-300 rounded-md' placeholder='Unit Type'>
                <input type='number' name='unit_qty[]' class='flex-1 p-2 border border-gray-300 rounded-md' placeholder='Quantity'>
                <input type='number' name='price[]' class='flex-1 p-2 border border-gray-300 rounded-md' placeholder='Price' step='0.01'>
            </div>`;
        const container = document.getElementById('variants-container');
        container.insertAdjacentHTML('beforeend', variantRow);
    });
</script>
@endsection
