@extends('layouts.app')

@section('title', 'Inventory for ' . $store->name)

@section('content')
<div class="container">
    <h1>Inventory for {{ $store->name }}</h1>

    @php
        // Ensure storeInventories and products are arrays
        $storeInventories = $store->storeInventories ?? collect();
        $products = $store->products ?? collect();
    @endphp

    @if($storeInventories->isEmpty() && $products->isEmpty())
        <p>No products available in this store.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>Total Quantity</th>
                    <th>Cost Price</th>
                    <th>Sale Price</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $productTotals = []; // Initialize an array to hold product totals

                    // Loop through store inventories to sum quantities
                    foreach ($storeInventories as $inventoryItem) {
                        $productId = $inventoryItem->product->id;

                        if (!isset($productTotals[$productId])) {
                            $productTotals[$productId] = [
                                'name' => $inventoryItem->product->name,
                                'barcode' => $inventoryItem->product->barcode,
                                'total_quantity' => 0,
                                'cost_price' => $inventoryItem->product->cost, // Assuming cost is available
                                'sale_price' => $inventoryItem->product->sale, // Assuming sale price is available
                            ];
                        }

                        $productTotals[$productId]['total_quantity'] += $inventoryItem->quantity;
                    }

                    // Loop through all products in the store
                    foreach ($products as $product) {
                        if (!isset($productTotals[$product->id])) {
                            $productTotals[$product->id] = [
                                'name' => $product->name,
                                'barcode' => $product->barcode,
                                'total_quantity' => 0,
                                'cost_price' => $product->cost, // Assuming cost is available
                                'sale_price' => $product->sale, // Assuming sale price is available
                            ];
                        }
                    }
                @endphp

                @foreach($productTotals as $product)
                    <tr>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['barcode'] }}</td>
                        <td>{{ $product['total_quantity'] }}</td>
                        <td>${{ number_format($product['cost_price'], 2) }}</td>
                        <td>${{ number_format($product['sale_price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to Products</a>
</div>
@endsection