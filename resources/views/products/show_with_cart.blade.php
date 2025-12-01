<!-- resources/views/products/show_with_cart.blade.php -->
@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="row">
        <!-- Product Section -->
        <div class="col-md-6">
            <div class="card">
                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }} Image">
                <div class="card-body">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="card-text">Barcode: {{ $product->barcode }}</p>
                    <p class="card-text">Cost: ${{ number_format($product->cost, 2) }}</p>
                    <div class="form-group">
                        <label for="variant">Variant:</label>
                        <select id="variant" class="form-control">
                            @foreach($product->variants as $variant)
                                <option value="{{ $variant->id }}" data-price="{{ $variant->price }}" data-quantity="{{ $variant->unit_qty }}">
                                    {{ $variant->unit_type }} - {{ $variant->unit_qty }} units for ${{ number_format($variant->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input id="quantity" type="number" class="form-control" min="1" value="1">
                    </div>
                    <button id="add-to-cart" class="btn btn-primary">Add to Cart</button>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="col-md-6">
            <h2>Cart</h2>
            @if(count($cart) > 0)
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart as $itemName => $item)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td>${{ number_format($item['price'], 2) }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                                <td>
                                    <form action="{{ route('cart.remove') }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="item_name" value="{{ $itemName }}">
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="font-weight-bold">Total: ${{ number_format($total, 2) }}</p>
                <form action="{{ route('cart.clear') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">Clear Cart</button>
                </form>
                <form action="{{ route('cart.checkout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">Checkout</button>
                </form>
            @else
                <p>Your cart is empty!</p>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('add-to-cart').addEventListener('click', function() {
            const quantity = parseInt(document.getElementById('quantity').value);
            const variantSelect = document.getElementById('variant');
            const selectedOption = variantSelect.options[variantSelect.selectedIndex];
            const variantId = variantSelect.value;
            const variantPrice = parseFloat(selectedOption.getAttribute('data-price'));
            const variantQuantity = parseInt(selectedOption.getAttribute('data-quantity'));

            if (quantity < 1 || quantity > variantQuantity) {
                alert('Invalid quantity.');
                return;
            }

            const formData = new FormData();
            formData.append('product_id', '{{ $product->id }}');
            formData.append('variant_id', variantId);
            formData.append('quantity', quantity);

            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error adding to cart.');
                }
            });
        });
    </script>
@endsection
