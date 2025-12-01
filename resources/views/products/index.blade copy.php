@extends('layouts.app')

@section('title', 'Product List')

@section('content')
<div class="container">
    <h1>Product List</h1>

    <!-- Button to Create a New Product -->
    <div class="mb-3">
        <a href="{{ route('products.create') }}" class="btn btn-success">Create New Product</a>
    </div>

    @if($productsWithVariants->isEmpty())
        <p>No products available.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Barcode</th>
                    <th>Cost</th>
                    <th>Sale</th>
                    <th>Variants</th>
                    <th>Inventory by Store</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productsWithVariants as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->barcode }}</td>
                        <td>${{ number_format($product->cost, 2) }}</td>
                        <td>${{ number_format($product->sale, 2) }}</td>
                        <td>
                            <ul class="list-unstyled">
                                @foreach($product->variants as $variant)
                                    <li>
                                        {{ $variant->unit_type }} - 
                                        {{ $variant->unit_qty }} units - 
                                        ${{ number_format($variant->price, 2) }}
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            @php
                                // Initialize totals
                                $storeTotals = [];
                                
                                // Sum quantities by store
                                foreach ($product->storeInventories as $inventoryItem) {
                                    if ($inventoryItem->store) { // Ensure store is not null
                                        $storeName = $inventoryItem->store->name;
                                        $storeId = $inventoryItem->store->id; // Get the store ID
                                        $storeTotals[$storeName] = [
                                            'total' => ($storeTotals[$storeName]['total'] ?? 0) + $inventoryItem->quantity,
                                            'id' => $storeId // Store ID for the link
                                        ];
                                    }
                                }
                            @endphp
                            <ul class="list-unstyled">
                                @forelse($storeTotals as $storeName => $data)
                                    <li>
                                        <a href="{{ route('store.show', $data['id']) }}">
                                            {{ $storeName }}: {{ $data['total'] }}
                                        </a>
                                    </li>
                                @empty
                                    <p>No inventory available.</p>
                                @endforelse
                            </ul>
                        </td>
                        <td>
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm">Edit</a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
