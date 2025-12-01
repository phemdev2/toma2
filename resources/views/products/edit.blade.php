@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6 text-purple-700">Update Product</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-lg p-6">
        <form action="{{ route('products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Tabs Navigation -->
            <ul class="flex border-b mb-4">
                <li class="mr-1">
                    <a class="inline-flex items-center py-2 px-4 text-purple-600 border-b-2 border-purple-600 cursor-pointer transition-colors duration-300 ease-in-out" id="general-tab" data-toggle="tab" href="#general" aria-controls="general" aria-selected="true">
                        <i class="fas fa-info-circle mr-2"></i> General Info
                    </a>
                </li>
                <li class="mr-1">
                    <a class="inline-flex items-center py-2 px-4 text-gray-600 hover:text-purple-600 hover:border-b-2 hover:border-purple-600 transition-colors duration-300 ease-in-out cursor-pointer" id="pricing-tab" data-toggle="tab" href="#pricing" aria-controls="pricing" aria-selected="false">
                        <i class="fas fa-dollar-sign mr-2"></i> Pricing
                    </a>
                </li>
                <li>
                    <a class="inline-flex items-center py-2 px-4 text-gray-600 hover:text-purple-600 hover:border-b-2 hover:border-purple-600 transition-colors duration-300 ease-in-out cursor-pointer" id="variants-tab" data-toggle="tab" href="#variants" aria-controls="variants" aria-selected="false">
                        <i class="fas fa-th-list mr-2"></i> Variants
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- General Info Tab -->
                <div id="general" class="tab-pane hidden" role="tabpanel" aria-labelledby="general-tab">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700">Product Name</label>
                        <div class="relative">
                            <i class="fas fa-box absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" name="name" id="name" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('name', $product->name) }}" required>
                        </div>
                        @error('name')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="barcode" class="block text-gray-700">Barcode</label>
                        <div class="relative">
                            <i class="fas fa-barcode absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" name="barcode" id="barcode" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('barcode', $product->barcode) }}" required>
                        </div>
                        @error('barcode')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-gray-700">Description</label>
                        <div class="relative">
                            <i class="fas fa-info-circle absolute left-3 top-3 text-gray-400"></i>
                            <textarea name="description" id="description" class="form-textarea mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $product->description) }}</textarea>
                        </div>
                        @error('description')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Pricing Tab -->
                <div id="pricing" class="tab-pane hidden" role="tabpanel" aria-labelledby="pricing-tab">
                    <div class="mb-4">
                        <label for="cost" class="block text-gray-700">Cost</label>
                        <div class="relative">
                            <i class="fas fa-dollar-sign absolute left-3 top-3 text-gray-400"></i>
                            <input type="number" name="cost" id="cost" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('cost', $product->cost) }}" step="0.01" required>
                        </div>
                        @error('cost')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="sale" class="block text-gray-700">Sale Price (Optional)</label>
                        <div class="relative">
                            <i class="fas fa-tag absolute left-3 top-3 text-gray-400"></i>
                            <input type="number" name="sale" id="sale" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('sale', $product->sale) }}" step="0.01">
                        </div>
                        @error('sale')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Variants Tab -->
                <div id="variants" class="tab-pane hidden" role="tabpanel" aria-labelledby="variants-tab">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300" id="variants-table">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-2 text-left">Unit Type</th>
                                    <th class="px-4 py-2 text-left">Unit Quantity</th>
                                    <th class="px-4 py-2 text-left">Price</th>
                                    <th class="px-4 py-2 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody id="variants-container">
                                @foreach($product->variants as $index => $variant)
                                    <tr id="variant_{{ $index }}" class="border-b">
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <i class="fas fa-cube absolute left-3 top-3 text-gray-400"></i>
                                                <input type="text" name="unit_type[]" id="unit_type_{{ $index }}" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('unit_type.' . $index, $variant->unit_type) }}" required>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <i class="fas fa-hashtag absolute left-3 top-3 text-gray-400"></i>
                                                <input type="number" name="unit_qty[]" id="unit_qty_{{ $index }}" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('unit_qty.' . $index, $variant->unit_qty) }}" required>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <i class="fas fa-dollar-sign absolute left-3 top-3 text-gray-400"></i>
                                                <input type="number" name="price[]" id="price_{{ $index }}" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('price.' . $index, $variant->price) }}" step="0.01" required>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <button type="button" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 delete-variant" data-id="{{ $variant->id }}">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                            <input type="hidden" name="deleted_variants[]" value="{{ $variant->id }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" id="add-variant" class="mt-3 px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                            <i class="fas fa-plus"></i> Add Another Variant
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="mt-4 px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                <i class="fas fa-pencil-alt mr-2"></i> Update Product
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('[data-toggle="tab"]');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Deactivate all tabs
                    tabs.forEach(t => {
                        t.classList.remove('text-purple-600', 'border-purple-600');
                        t.setAttribute('aria-selected', 'false');
                    });
                    tabPanes.forEach(pane => pane.classList.add('hidden'));

                    // Activate clicked tab
                    tab.classList.add('text-purple-600', 'border-purple-600');
                    tab.setAttribute('aria-selected', 'true');
                    document.querySelector(tab.getAttribute('href')).classList.remove('hidden');
                });
            });

            // Initialize the first tab
            document.getElementById('general-tab').click();

            // Handle adding new variant
            document.getElementById('add-variant').addEventListener('click', () => {
                const container = document.getElementById('variants-container');
                const index = container.children.length;

                const newVariantRow = `
                    <tr id="variant_${index}" class="border-b">
                        <td class="px-4 py-2">
                            <input type="text" name="unit_type[]" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" name="unit_qty[]" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" name="price[]" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" step="0.01" required>
                        </td>
                        <td class="px-4 py-2">
                            <button type="button" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 delete-variant">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                            <input type="hidden" name="deleted_variants[]" value="">
                        </td>
                    </tr>
                `;

                container.insertAdjacentHTML('beforeend', newVariantRow);
            });

            // Handle deletion of variants
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-variant')) {
                    const variantRow = e.target.closest('tr');
                    const variantId = e.target.getAttribute('data-id');

                    // Mark the variant as deleted by adding it to the hidden input
                    const hiddenInput = variantRow.querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        hiddenInput.value = variantId; // set the ID of the variant to be deleted
                    }

                    variantRow.remove(); // Remove the row from the table
                }
            });
        });
    </script>
</div>
@endsection
