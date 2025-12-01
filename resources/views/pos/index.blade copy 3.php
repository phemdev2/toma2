<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
    body {
    font-family: 'Poppins', 'Montserrat', sans-serif;
    background-color: #e8f8f5;
    margin: 0;
    padding: 0;
    color: #333;
}

.container {
    margin: 0px;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Raleway', sans-serif;
}

.card-title, .card-text, .btn {
    font-family: 'Montserrat', sans-serif;
}

.col-12, .col-sm-6, .col-md-4, .col-lg-2 {
    padding-left: 0;
    padding-right: 1px;
}

.cart {
    height: 60vh;
    background-color: white;
    margin-bottom: 30px;
    font-size: 0.875rem;
    display: flex;
    flex-direction: column;
    padding: 1rem;
}

.cart .table thead th, .cart .table tbody td {
    font-size: 0.75rem; /* Smaller font size for table headers and cells */
}

.cart .total {
    font-size: 1rem; /* Slightly larger for readability */
    text-align: right; /* Align text to the right */
    margin-top: auto; /* Push the total to the bottom of the cart */
    margin-right: 10px; /* Space from the right edge */
    padding: 20px;
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
    background-color: #0b5345; /* Custom navbar color */
}

.navbar-nav {
    flex: 1;
}

.navbar-toggler {
    border-color: transparent;
}

.navbar-brand {
    flex: 1;
}

.wifi-icon {
    margin-left: 15px; /* Space between items */
    color: #28a745; /* Default color (green) for network connection */
    transition: color 0.3s; /* Smooth color transition */
}

.no-network {
    color: red; /* Color when offline */
}

.custom-search-bar {
    border: none; /* Remove default border */
    border-radius: 0; /* Remove default border-radius */
    box-shadow: none; /* Remove default box-shadow */
}

.input-group {
    border-bottom: 2px solid #ced4da; /* Adjust color and thickness as needed */
}

.input-group-prepend .input-group-text {
    border: none; /* Remove border from the icon part */
    background-color: transparent; /* Ensure background color matches */
}

.input-group-prepend .input-group-text i {
    color: #6c757d; /* Adjust icon color if needed */
}

.cart-container {
    height: 90vh; 
    display: flex;
    flex-direction: column;
    width: 100%;
}

.table-responsive {
    flex-grow: 1;
    overflow-y: auto;
}

.empty-cart-img {
    max-width: 100px; /* Adjust size as needed */
    height: auto;
}

.empty-cart-placeholder img {
    text-align: center;
    max-width: 80px;
}

.checkout-buttons {
    display: flex;
    flex-direction: column;
    margin-top: auto; /* Push buttons to the bottom */
}

.checkout-buttons button {
    width: 100%; /* Full width for buttons */
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.product-card {
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.product-card:hover {
    transform: scale(1.05);
}

.product-card .card-body {
    padding: 0.75rem; /* Adjust padding as needed */
}

.product-card .card-title {
    font-size: 0.875rem; /* Smaller font size */
    font-weight: bold;
}

.product-card .card-subtitle, .product-card .card-text {
    font-size: 0.75rem; /* Smaller font size */
}

@media (max-width: 767px) {
    .col-sm-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .product-card {
        font-size: 0.875rem; /* Slightly smaller font size for product cards */
    }

    .cart {
        padding: 0.5rem;
    }

    .navbar-brand {
        font-size: 1rem;
    }

    .table-responsive {
        font-size: 0.875rem; /* Adjust table font size on small screens */
    }
}

    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark mb-1">
        <a class="navbar-brand" href="#">POS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
            </ul>
            <i class="fas fa-wifi wifi-icon" id="wifiIcon" aria-label="Network status"></i>
        </div>
    </nav>
    
    <div class="container">
        <input type="hidden" id="store-id" value="{{ $storeId }}">
        <div class="row">
            <!-- Cart Section -->
            <div class="col-md-4 cart-container mb-4 mb-md-0">
    <div class="cart p-2 d-flex flex-column">
        <h2 class="h4 mb-3">Cart</h2>
        <div class="table-responsive flex-grow-1">
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
                        <td colspan="7" class="text-center">
                            <img src="path/to/empty-cart-image.png" alt="Empty Cart" class="empty-cart-img mb-2">
                            Cart is empty
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="total h5" id="cart-total">Total: $0.00</p>
        <div class="checkout-buttons">
            <button type="button" class="btn btn-success btn-sm" onclick="checkout('cash')">Cash</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="checkout('pos')">POS</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="checkout('bank')">Bank Transfer</button>
            <button type="button" id="clear-cart-btn" class="btn btn-info btn-sm" aria-label="Clear Cart">Clear Cart</button>
        </div>
    </div>
</div>

            <!-- Products Section -->
            <div class="col-md-8">
                <div class="row mb-2">
                <div class="col-md-8 offset-md-5">
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
        </div>
        <input type="text" id="search-bar" class="form-control custom-search-bar" placeholder="Search.... ">
    </div>
</div>

                </div>
                <div class="row" id="product-list">
                    @foreach($products as $product)
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
    <div class="product-card card shadow-sm" 
        data-product-id="{{ $product->id }}" 
        data-product-name="{{ $product->name }}" 
        data-product-barcode="{{ $product->barcode }}" 
        data-product-price="{{ $product->sale }}" 
        data-product-variants="{{ json_encode($product->variants) }}"
        onclick="addToCart({{ json_encode($product) }})">
        <div class="card-body p-2">
            <h6 class="card-title">{{ $product->name }}</h6>
            <h6 class="card-subtitle mb-1 text-muted">{{ $product->barcode }}</h6>
            <p class="card-text mb-1">Sale: ${{ number_format($product->sale, 2) }}</p>
            @if($product->inventories->isNotEmpty())
                <p class="card-text mb-1">Qty: {{ $product->inventories->first()->quantity }}</p>
            @else
                <p class="card-text text-danger mb-1">Out of stock</p>
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
        <div class="modal-dialog modal-dialog-centered" role="document">
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
                        <td><button class="btn btn-danger btn-sm" onclick="removeFromCart('${key}')"> <i class="fas fa-times"></i></button></td>
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
                paymentMethod: paymentMethod,
                store_id: storeId
            };

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

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
                }
            });
        };

        $('#addVariantToCart').on('click', function() {
            const selectedRadio = $('input[name="variantRadio"]:checked').val();
            selectedVariant = selectedRadio ? parseInt(selectedRadio) : null;
            addProductToCart();
        });

        const wifiIcon = document.getElementById('wifiIcon');

        function updateNetworkStatus() {
            if (navigator.onLine) {
                wifiIcon.classList.remove('no-network');
            } else {
                wifiIcon.classList.add('no-network');
            }
        }

        // Initial check
        updateNetworkStatus();

        // Listen for online and offline events
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);

        updateCart();
    });
    </script>
</body>
</html>
