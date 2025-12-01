<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Raleway:wght@400;700&display=swap" rel="stylesheet">
   

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/styles.css">
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
            onclick="addToCart({{ $product->toJson() }})">
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
<!-- Modal for Receipt -->

<!-- Modal for Offline Receipt -->
<div class="modal fade" id="offlineReceiptModal" tabindex="-1" aria-labelledby="offlineReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offlineReceiptModalLabel">Offline Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="offlineReceiptContent">
                <!-- Content will be injected here via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printReceiptBtn">Print</button>
                <button type="button" class="btn btn-danger" id="clearCartBtn">Clear Cart</button>
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

    // Function to debounce search input
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Function to update the product list based on the search query
    function updateProductList() {
        const searchQuery = $('#search-bar').val().toLowerCase();
        $('#product-list .product-card').each(function() {
            const productName = String($(this).data('product-name')).toLowerCase();
            const productBarcode = String($(this).data('product-barcode')).toLowerCase();
            const isVisible = productName.includes(searchQuery) || productBarcode.includes(searchQuery);
            $(this).toggle(isVisible);
        });
    }

    $('#search-bar').on('input', debounce(updateProductList, 300));

    // Function to add product to cart
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

    // Function to display variant options in the modal
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

    // Function to add the selected product or variant to the cart
    function addProductToCart() {
        const product = selectedProduct;
        const variant = product.variants ? product.variants[selectedVariant] : null;

        const itemName = `${product.name}`;
        const itemPrice = variant ? parseFloat(variant.price) : parseFloat(product.sale);
        const itemQuantity = variant ? variant.unit_qty : 1;

        if (cart[itemName]) {
            cart[itemName].quantity += 1;
        } else {
            cart[itemName] = {
                name: itemName,
                variant: variant ? variant.unit_type : 'N/A',
                price: itemPrice,
                unit_qty: itemQuantity,
                quantity: 1,
                product_id: product.id,
                variant_id: variant ? variant.id : null,
            };
        }

        updateStock(product.id, variant ? variant.id : null, -itemQuantity);
        saveCartToLocalStorage();
        updateCart();
        $('#variantModal').modal('hide');
    }

    // Bind the "Add to Cart" button in the modal
    $('#addVariantToCart').on('click', function() {
        const selectedRadio = $('input[name="variantRadio"]:checked').val();
        selectedVariant = selectedRadio ? parseInt(selectedRadio) : null;
        addProductToCart();
    });

    // Function to update the cart display
    function updateCart() {
        const cartItemsElement = $('#cart-items');
        cartItemsElement.empty();

        if (Object.keys(cart).length === 0) {
            showEmptyCartPlaceholder();
            $('#cart-total').text('Total: $0.00');
            return;
        }

        let total = 0;
        Object.values(cart).forEach(item => {
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
                    <td><button class="btn btn-danger btn-sm" onclick="removeFromCart('${item.name}')"><i class="fas fa-times"></i></button></td>
                </tr>
            `);
        });

        $('#cart-total').text(`Total: $${total.toFixed(2)}`);
    }

    // Function to display a placeholder when the cart is empty
    function showEmptyCartPlaceholder() {
        $('#cart-items').html(`
            <tr id="empty-cart-placeholder" class="empty-cart-placeholder">
                <td colspan="7" class="text-center">
                    <img src="./image.png" alt="Empty Cart" class="empty-cart-img mb-2">
                    Cart is empty
                </td>
            </tr>
        `);
    }

    // Function to save cart to local storage
    function saveCartToLocalStorage() {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    // Function to load cart from local storage
    function loadCartFromLocalStorage() {
        const savedCart = localStorage.getItem('cart');
        return savedCart ? JSON.parse(savedCart) : {};
    }

    // Function to update stock quantities
    function updateStock(productId, variantId, changeQty) {
        console.log(`Updating stock for Product ID: ${productId}, Variant ID: ${variantId}, Change Qty: ${changeQty}`);
    }

    // Function to clear the cart
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

    // Function to remove an item from the cart
    window.removeFromCart = function(itemName) {
        const item = cart[itemName];
        if (item) {
            updateStock(item.product_id, item.variant_id, item.unit_qty * item.quantity);
            delete cart[itemName];
            saveCartToLocalStorage();
            updateCart();
        }
    };

    // Function to handle checkout process
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
                    // Checkout successful and online
                    alert(`Checkout successful using ${paymentMethod}!`);
                    cart = {};
                    saveCartToLocalStorage();
                    updateCart();
                    window.location.href = `{{ url('/receipt') }}/${response.order_id}`;
                } else {
                    // Checkout successful but no order_id (likely offline)
                    alert('Checkout data saved locally. Will sync when back online.');
                    showOfflineReceipt(checkoutData);
                }
            },
            error: function(xhr) {
                console.error("Checkout Error:", xhr);
                alert(`Error during checkout: ${xhr.responseJSON?.message || "An unexpected error occurred."}`);

                // Save checkout data locally if offline
                if (!navigator.onLine) {
                    saveOfflineOrder(checkoutData);
                    showOfflineReceipt(checkoutData);
                }
            }
        });
    };

    // Function to save offline orders
    function saveOfflineOrder(data) {
        let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')) || [];

        // Create a unique ID for the offline order
        const orderId = `offline_${new Date().getTime()}`;
        offlineOrders.push({ ...data, order_id: orderId });

        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));

        console.log('Saved order locally:', orderId);
    }

    // Function to sync offline orders
    function syncOfflineOrders() {
        if (navigator.onLine) {
            let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')) || [];
            const syncedOrders = [];
            let syncCount = 0;

            if (offlineOrders.length > 0) {
                offlineOrders.forEach((order, index) => {
                    setTimeout(() => {
                        $.ajax({
                            url: '{{ route("checkout.process") }}',
                            type: 'POST',
                            data: JSON.stringify(order),
                            contentType: 'application/json',
                            success: function(response) {
                                if (response.order_id) {
                                    console.log(`Successfully synced offline order ${order.order_id}`);
                                    removeOfflineOrder(order.order_id);
                                    syncedOrders.push(order.order_id);
                                    syncCount++;

                                    if (syncCount === offlineOrders.length) {
                                        notifyUser(
                                            'Offline Orders Synced',
                                            { body: `Orders ${syncedOrders.join(', ')} have been successfully synced.` }
                                        );
                                    }
                                }
                            },
                            error: function(xhr) {
                                console.error(`Failed to sync offline order ${order.order_id}:`, xhr);
                            }
                        });
                    }, index * 1000); // Delay of 1 second between each request
                });
            }
        }
    }

    // Function to remove offline order from local storage
    function removeOfflineOrder(orderId) {
        let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')) || [];
        offlineOrders = offlineOrders.filter(order => order.order_id !== orderId);
        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
        console.log('Removed offline order:', orderId);
    }

    // Function to show offline receipt
    function showOfflineReceipt(data) {
        alert('Checkout data saved locally. Will sync when back online.');
        saveOfflineOrder(data);
    }

    // Request notification permission
    function requestNotificationPermission() {
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                } else {
                    console.log('Notification permission denied.');
                }
            });
        }
    }

    requestNotificationPermission();

    // Function to display desktop notifications
    function notifyUser(title, options) {
        if (Notification.permission === 'granted') {
            new Notification(title, options);
        } else {
            console.log("Notification permission not granted.");
        }
    }

    window.addEventListener('online', syncOfflineOrders);

    // Load cart items and update cart on page load
    updateCart();
});

</script>




</body>
</html>
