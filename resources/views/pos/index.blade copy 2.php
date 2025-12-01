<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .cart-container {
            max-width: 100%;
            padding: 10px;
        }

        .cart {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            height: 80vh;
            overflow-y: auto;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 10px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.2s;
        }

        .product-card img {
            width: 100%;
            height: auto;
            border-bottom: 1px solid #ddd;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

        .card-body {
            padding: 15px;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .card-subtitle {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .card-text {
            font-size: 0.9rem;
        }

        .checkout-buttons button {
            font-size: 1rem;
            padding: 10px;
            margin: 5px;
        }

        .modal-dialog {
            max-width: 50%;
            margin: 1.75rem auto;
        }

        .modal-content {
            border-radius: 8px;
        }

        .empty-cart-placeholder {
            text-align: center;
            color: #dc3545;
            font-style: italic;
            padding: 50px;
        }

        @media (max-width: 767.98px) {
            .product-card {
                width: 100%;
                margin: 10px 0;
            }

            .modal-dialog {
                max-width: 90%;
            }

            .cart {
                font-size: 0.9rem;
            }

            .checkout-buttons button {
                font-size: 0.9rem;
                padding: 8px;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <input type="hidden" id="store-id" value="{{ $storeId }}">

        <div class="row">
            <!-- Cart Section -->
            <div class="col-md-4 cart-container mb-4 mb-md-0">
                <div class="cart">
                    <h2 class="h4 mb-4">Shopping Cart</h2>
                    <div class="table-responsive">
                        <table id="cart-table" class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Unit</th>
                                    <th>Unit Qty</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <tr id="empty-cart-placeholder" class="empty-cart-placeholder">
                                    <td colspan="7" class="text-center">Cart is empty</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="total h5" id="cart-total">Total: $0.00</p>
                    <div class="checkout-buttons">
                        <button type="button" class="btn btn-success btn-sm" onclick="checkout('cash')">Cash</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="checkout('pos')">POS</button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="checkout('bank')">Bank Transfer</button>
                        <form action="{{ route('cart.clear') }}" method="POST" id="clear-cart-form" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm" aria-label="Clear Cart">Clear Cart</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="col-md-8">
                <h2 class="h4 mb-4">Products</h2>
                <div class="row">
                    @foreach($products as $product)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="product-card card shadow-sm" data-product="{{ $product->id }}" onclick="addToCart({{ json_encode($product) }})">
                                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <h6 class="card-subtitle mb-1">{{ $product->barcode }}</h6>
                                    <p class="card-text">Sale: ${{ number_format($product->sale, 2) }}</p>
                                    @if($product->inventories->isNotEmpty())
                                        <p class="card-text">Qty: {{ $product->inventories->first()->quantity }}</p>
                                    @else
                                        <p class="card-text text-danger">Out of stock</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Variant Selection -->
    <div class="modal fade" id="variantModal" tabindex="-1" role="dialog" aria-labelledby="variantModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="variantModalLabel">Select Variant</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="variantOptions">
                        <!-- Variant options will be dynamically inserted here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addVariantToCart">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            let cart = loadCartFromLocalStorage();
            let selectedProduct = null;
            let selectedVariant = null;

            window.addToCart = function(product) {
                selectedProduct = product;

                if (product.variants && product.variants.length > 0) {
                    displayVariantOptions(product.variants);
                    $('#variantModal').modal('show');
                } else {
                    selectedVariant = null;
                    addProductToCart();
                }
            };

            function displayVariantOptions(variants) {
                const variantContainer = $('#variantOptions');
                variantContainer.empty();

                variants.forEach((variant, index) => {
                    const variantName = variant.unit_type || "Unnamed Variant";
                    const variantPrice = parseFloat(variant.price || 0).toFixed(2);
                    const variantQuantity = variant.unit_qty || 0;

                    variantContainer.append(`
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="variantRadio" id="variant${index}" value="${index}" ${index === 0 ? 'checked' : ''}>
                            <label class="form-check-label" for="variant${index}">
                                ${variantName} - $${variantPrice} (Qty: ${variantQuantity})
                            </label>
                        </div>
                    `);
                });

                variantContainer.find('input[type="radio"]').first().prop('checked', true);
            }

            function addProductToCart() {
                const product = selectedProduct;
                const variantIndex = selectedVariant !== null ? selectedVariant : 0;
                const variant = product.variants ? product.variants[variantIndex] : null;

                const productId = product.id;
                const variantId = variant ? variant.id : null;
                const unitQty = variant ? variant.unit_qty : 1;
                const price = variant ? parseFloat(variant.price) : parseFloat(product.sale);
                const variantName = variant ? variant.unit_type : 'N/A';

                const itemName = `${product.name}`;

                if (cart[itemName]) {
                    cart[itemName].quantity += 1;
                } else {
                    cart[itemName] = {
                        name: itemName,
                        variant: variantName,
                        price: price,
                        unit_qty: unitQty,
                        quantity: 1,
                        product_id: productId,
                        variant_id: variantId,
                    };
                }

                updateStock(productId, variantId, -1 * unitQty);
                saveCartToLocalStorage();
                updateCart();
                $('#variantModal').modal('hide');
            }

            function updateCart() {
                const cartItemsElement = $('#cart-items');
                cartItemsElement.empty();

                if (Object.keys(cart).length === 0) {
                    showEmptyCartPlaceholder();
                    $('#cart-total').text('Total: $0.00');
                    return;
                }

                let total = 0;
                for (const [key, item] of Object.entries(cart)) {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;

                    cartItemsElement.append(`
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.variant || 'N/A'}</td>
                            <td>${item.unit_qty}</td>
                            <td>$${item.price.toFixed(2)}</td>
                            <td>${item.quantity}</td>
                            <td>$${itemTotal.toFixed(2)}</td>
                            <td><button class="btn btn-danger btn-sm" onclick="removeFromCart('${key}')">X</button></td>
                        </tr>
                    `);
                }

                $('#cart-total').text(`Total: $${total.toFixed(2)}`);
            }

            function showEmptyCartPlaceholder() {
                $('#cart-items').html(`
                    <tr id="empty-cart-placeholder" class="empty-cart-placeholder">
                        <td colspan="7" class="text-center">Cart is empty</td>
                    </tr>
                `);
            }

            function updateReceipt() {
                const receiptItemsElement = $('#receipt-items');
                receiptItemsElement.empty();

                let total = 0;
                for (const [key, item] of Object.entries(cart)) {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;

                    receiptItemsElement.append(`
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.variant || 'N/A'}</td>
                            <td>${item.unit_qty}</td>
                            <td>$${item.price.toFixed(2)}</td>
                            <td>${item.quantity}</td>
                            <td>$${itemTotal.toFixed(2)}</td>
                        </tr>
                    `);
                }

                $('#receipt-total').text(`$${total.toFixed(2)}`);
            }

            window.removeFromCart = function(itemName) {
                const item = cart[itemName];
                if (item) {
                    updateStock(item.product_id, item.variant_id, item.unit_qty * item.quantity);
                    delete cart[itemName];
                    saveCartToLocalStorage();
                    updateCart();
                }
            };

            window.checkout = function(paymentMethod) {
                if (Object.keys(cart).length === 0) {
                    alert("Cart is empty!");
                    return;
                }

                const storeId = $('#store-id').val();

                const checkoutData = {
                    cart: Object.values(cart),
                    paymentMethod: paymentMethod,
                    store_id: storeId
                };

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('#checkout-spinner').show();

                $.ajax({
                    url: '{{ route("checkout.process") }}',
                    type: 'POST',
                    data: JSON.stringify(checkoutData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.order_id) {
                            alert(`Checkout successful using ${paymentMethod}!`);
                            cart = {};
                            saveCartToLocalStorage();
                            updateCart();
                            window.location.href = `{{ url('/receipt') }}/${response.order_id}`;
                        } else {
                            alert('Checkout successful, but no order ID was returned.');
                        }
                    },
                    error: function(xhr) {
                        console.error("Checkout Error:", xhr);
                        alert(`Error during checkout: ${xhr.responseJSON?.message || "An unexpected error occurred."}`);
                    },
                    complete: function() {
                        $('#checkout-spinner').hide();
                    }
                });
            };

            $('#addVariantToCart').on('click', function() {
                const selectedVariantRadio = $('input[name="variantRadio"]:checked');
                selectedVariant = parseInt(selectedVariantRadio.val());
                addProductToCart();
            });

            function saveCartToLocalStorage() {
                localStorage.setItem('cart', JSON.stringify(cart));
            }

            function loadCartFromLocalStorage() {
                const savedCart = localStorage.getItem('cart');
                return savedCart ? JSON.parse(savedCart) : {};
            }

            function updateStock(productId, variantId, quantityChange) {
                $.ajax({
                    url: '{{ route("product.update-stock") }}',
                    type: 'POST',
                    data: {
                        product_id: productId,
                        variant_id: variantId,
                        quantity_change: quantityChange
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Stock updated successfully.');
                        } else {
                            console.error('Stock update failed.');
                        }
                    },
                    error: function(xhr) {
                        console.error("Stock Update Error:", xhr);
                    }
                });
            }

            updateCart();
        });
    </script>
</body>

</html>
