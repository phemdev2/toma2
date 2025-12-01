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
            font-family: Arial, sans-serif;
            background-color: #e8f8f5;
        }
        .col-12, .col-sm-6, .col-md-4, .col-lg-3 {
            padding-left: 0;
            padding-right: 0;
        }
        .cart {
            height: 60vh;
            background-color: white;
           
            margin-bottom: 30px;
        }
        .product-list, .cart {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .product-item, .cart-item {
            margin-bottom: 10px;
        }
        .product-item img, .cart-item img {
            max-width: 100px;
            height: auto;
        }
        .product-item, .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .product-item h5, .cart-item h5 {
            margin: 0;
        }
        .cart-summary {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .navbar {
            background-color: #0b5345;
        }
        @media (max-width: 576px) {
            .card {
                margin-bottom: 20px;
            }
            .card-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-1">
        <a class="navbar-brand" href="#">POS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                <!-- Add more navigation items here -->
            </ul>
        </div>
    </nav>
    
    <div class="container-fluid">
        <input type="hidden" id="store-id" value="{{ $storeId }}">
        <div class="row">
            <!-- Cart Section -->
            <div class="col-md-4 cart-container mb-4 mb-md-0">
                <div class="cart p-2">
                    <h2 class="h4 mb-3">Cart</h2>
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
                </div>
                <p class="total h5" id="cart-total">Total: $0.00</p>
                <div class="checkout-buttons mt-3">
                    <button type="button" class="btn btn-success btn-sm" onclick="checkout('cash')">Cash</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="checkout('pos')">POS</button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="checkout('bank')">Bank Transfer</button>
                    <button type="button" id="clear-cart-btn" class="btn btn-info btn-sm" aria-label="Clear Cart">Clear Cart</button>
                </div>
                
<!-- Hidden Form for Submission -->
<form id="checkout-form" method="POST" action="{{ route('your.route') }}" style="display: none;">
    @csrf
    <input type="hidden" name="paymentMethod" id="paymentMethod">
    <input type="hidden" name="store_id" value="{{ $storeId }}"> <!-- Add your store ID -->
    <input type="hidden" name="cart" value="{{ json_encode($cart) }}"> <!-- Add your cart data -->
</form>
            </div>
            <!-- Products Section -->
            <div class="col-8">
                <div class="row mb-2">
                    <div class="col-md-8 offset-md-5 border-bottom">
                        <div class="input-group">
                            <input type="text" id="search-bar" class="form-control" placeholder="Search products by name or barcode">
                           
                        </div>
                    </div>
                </div>
                <div class="row" id="product-list">
                    @foreach($products as $product)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="product-card card shadow-sm" 
                                data-product-id="{{ $product->id }}" 
                                data-product-name="{{ $product->name }}" 
                                data-product-barcode="{{ $product->barcode }}" 
                                data-product-price="{{ $product->sale }}" 
                                data-product-variants="{{ json_encode($product->variants) }}"
                                onclick="addToCart({{ json_encode($product) }})">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <h6 class="card-subtitle mb-1 text-muted">{{ $product->barcode }}</h6>
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

    // Function to update the product list based on search input
    function updateProductList() {
        const searchQuery = $('#search-bar').val().toLowerCase();
        $('#product-list .product-card').each(function() {
            const productName = String($(this).data('product-name')).toLowerCase();
            const productBarcode = String($(this).data('product-barcode')).toLowerCase();
            const isVisible = productName.includes(searchQuery) || productBarcode.includes(searchQuery);
            $(this).toggle(isVisible);
        });
    }

    $('#search-bar').on('input', function() {
        updateProductList();
    });

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

    function saveCartToLocalStorage() {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    function loadCartFromLocalStorage() {
        const savedCart = localStorage.getItem('cart');
        return savedCart ? JSON.parse(savedCart) : {};
    }

    function updateStock(productId, variantId, changeQty) {
        console.log(`Updating stock for Product ID: ${productId}, Variant ID: ${variantId}, Change Qty: ${changeQty}`);
    }

    function clearCart() {
        localStorage.removeItem('cart');
        cart = {};
        updateCart();
    }

    $('#clear-cart-btn').on('click', function() {
        if (confirm("Are you sure you want to clear the cart?")) {
            clearCart();
        }
    });

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
        paymentMethod: paymentMethod, // Ensure this is being set
        store_id: storeId
    };

    // AJAX setup...
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
        const selectedRadio = $('input[name="variantRadio"]:checked').val();
        selectedVariant = selectedRadio ? parseInt(selectedRadio) : null;
        addProductToCart();
    });

    updateCart();
});
</script>
</html>
