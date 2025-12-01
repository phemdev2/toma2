<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Add this to your styles.css or inside a <style> tag */
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .product-card .product-title {
    text-transform: uppercase; /* Make product titles uppercase */
    font-weight: bold; /* Optional: makes the title bolder */
}
        .product-card img {
            border-bottom: 1px solid #e5e7eb; /* Light gray border for separation */
        }

        .cart-table-container {
            max-height: 400px; /* Adjust as needed */
            overflow-y: auto; /* Scroll only vertically */
        }

        /* Smaller font sizes */
        .cart-table th, .cart-table td {
            font-size: 0.875rem; /* Smaller text */
        }
         .spinner {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 20px auto;
            box-sizing: border-box;
        }
        .spinner::before, .spinner::after {
            content: '';
            position: absolute;
            border: 8px solid #f3f3f3;
            border-radius: 50%;
            border-top-color: #3498db;
            animation: spin 1.5s linear infinite;
        }
        .spinner::before {
            width: 100%;
            height: 100%;
            border-width: 8px;
        }
        .spinner::after {
            width: 60%;
            height: 60%;
            border-width: 4px;
            top: 20%;
            left: 20%;
            animation: spin 0.75s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-200 flex flex-col min-h-screen">
    <!-- Navigation Bar -->
   <!-- Navigation Bar -->
<!-- Navigation Bar -->
<nav class="bg-green-800 p-4 sticky top-0">
    <div class="container mx-auto flex justify-between items-center">
        <a class="text-white text-lg font-bold" href="#">POS</a>
        <div class="flex items-center">
        <span class="text-white mr-4 flex items-center" id="user-info">
            <i class="fas fa-user mr-2"></i> <!-- User icon -->
            <strong>{{ Auth::user()->name }}</strong> 
            <i class="fas fa-store mr-2 ml-4"></i> <!-- Store icon -->
            <strong>{{ Auth::user()->store_id }}</strong> 
            <i class="fas fa-store-alt mr-2 ml-4"></i> <!-- Store name icon -->
            <strong>{{ Auth::user()->store->name }}</strong>
        </span>
            <a class="text-white px-4" href="/dashboard">Home</a>

            <i class="fas fa-wifi text-white" id="wifiIcon" aria-label="Network status"></i>
        </div>
    </div>
</nav>
    <!-- Main Content -->
    <main class="flex flex-1 container mx-auto mt-4">
        
        <div class="flex flex-col md:flex-row w-full">
            <!-- Cart Section -->
            <div class="md:w-1/3 bg-white p-4 rounded shadow-md mb-4 flex flex-col">
                <h2 class="text-md font-semibold mb-3">Cart</h2>
                <div class="cart-table-container flex-1">
                    <table id="cart-table" class="min-w-full border-collapse border border-gray-200 cart-table">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 px-2 py-1">Product</th>
                                <th class="border border-gray-300 px-2 py-1">Unit</th>
                                <th class="border border-gray-300 px-2 py-1">Unit Qty</th>
                                <th class="border border-gray-300 px-2 py-1">Price</th>
                                <th class="border border-gray-300 px-2 py-1">Qty</th>
                                <th class="border border-gray-300 px-2 py-1">Total</th>
                                <th class="border border-gray-300 px-2 py-1">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items"></tbody>
                    </table>
                </div>
                <p class="text-lg font-bold mt-2" id="cart-total">Total: &#8358;0.00</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button class="bg-green-500 text-white rounded px-2 py-1" onclick="checkout('cash')">Cash</button>
                    <button class="bg-red-500 text-white rounded px-2 py-1" onclick="checkout('pos')">POS</button>
                    <button class="bg-yellow-500 text-white rounded px-2 py-1" onclick="checkout('bank')">Bank Transfer</button>
                    <button id="clear-cart-btn" class="bg-blue-500 text-white rounded px-2 py-1">Clear Cart</button>
                </div>
            </div>

            <!-- Products Section -->
            <div class="md:w-2/3 bg-white p-4 rounded shadow-md">
                <div class="mb-2">
                    <div class="relative">
                        <input type="text" id="search-bar" class="border rounded w-full py-2 px-3" placeholder="  Search.... ">
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="product-list">
                    @foreach($products as $product)
                    <div class="product-card bg-white rounded-lg shadow hover:scale-105 cursor-pointer h-100"
    data-product-id="{{ $product->id }}" 
    data-product-name="{{ $product->name }}" 
    data-product-barcode="{{ $product->barcode }}" 
    data-product-price="{{ $product->sale }}" 
    data-product-variants="{{ json_encode($product->variants) }}"
    onclick="addToCart({{ $product->toJson() }})">
    <div class="p-4 flex flex-col justify-between text-center h-full">
        <div>
            <p class="product-title text-lg mb-1">{{ $product->name }}</p> <!-- Title displayed in uppercase -->
            <h6 class="text-xs text-gray-500 mb-1">{{ $product->barcode }}</h6>
            <p class="text-md font-bold mb-1">Sale: &#8358;{{ number_format($product->sale, 2) }}</p>
            @if($product->inventories->isNotEmpty())
                <p class="text-xs text-gray-700">Available: {{ $product->inventories->first()->quantity }}</p>
            @else
                <p class="text-xs text-red-500">Out of stock</p>
            @endif
        </div>
        <button class="bg-green-500 text-white rounded px-3 py-1 text-xs" onclick="event.stopPropagation(); addToCart({{ $product->toJson() }});">Add to Cart</button>
    </div>
</div>

                    @endforeach
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for Variant Selection -->
    <div id="variantModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/3">
            <div class="p-4 border-b">
                <h5 class="text-lg font-semibold" id="variantModalLabel">Select Variant</h5>
                <button class="absolute top-2 right-2 text-gray-500" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4" id="variantOptions">
                <!-- Variant options will be dynamically inserted here -->
            </div>
            <div class="p-4 border-t">
                <button class="bg-gray-300 rounded px-4 py-2 mr-2" onclick="closeModal()">Cancel</button>
                <button class="bg-blue-500 text-white rounded px-4 py-2" id="addVariantToCart">Add to Cart</button>
            </div>
        </div>
    </div>
<!-- Loading Spinner -->
<div id="loadingSpinner" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50 hidden">
        <div class="spinner" aria-label="Loading..."></div>
    </div>
    <!-- JavaScript and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
       $(document).ready(function() {
    let cart = loadCartFromLocalStorage();
    let selectedProduct = null;
    let selectedVariant = null;

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

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

    window.addToCart = function(product) {
        selectedProduct = product;

        if (product.variants && product.variants.length > 0) {
            displayVariantOptions(product.variants);
            $('#variantModal').removeClass('hidden'); // Show modal
        } else {
            selectedVariant = null;
            // Set default variant (unit) if no variants are available
            selectedProduct.defaultVariant = {
                unit_type: product.unit,
                price: product.sale,
                unit_qty: 1
            };
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
                <div class="flex items-center mb-2">
                    <input type="radio" name="variantRadio" id="variant${index}" value="${index}" class="mr-2" ${index === 0 ? 'checked' : ''}>
                    <label for="variant${index}" class="flex-1">
                        ${variantName} - $${variantPrice} (Qty: ${variantQuantity})
                    </label>
                </div>
            `);
        });

        // Check if a default variant (unit) should be displayed
        if (!variants.length) {
            variantContainer.append(`
                <div class="flex items-center mb-2">
                    <input type="radio" name="variantRadio" id="variant0" value="0" class="mr-2" checked>
                    <label for="variant0" class="flex-1">
                        ${selectedProduct.defaultVariant.unit_type} - $${selectedProduct.defaultVariant.price} (Qty: ${selectedProduct.defaultVariant.unit_qty})
                    </label>
                </div>
            `);
        }
    }

    function addProductToCart() {
        const product = selectedProduct;
        const variant = selectedVariant !== null ? product.variants[selectedVariant] : (product.defaultVariant || null);

        const itemName = product.name;
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
        closeModal();
    }

    $('#addVariantToCart').on('click', function() {
        const selectedRadio = $('input[name="variantRadio"]:checked').val();
        selectedVariant = selectedRadio ? parseInt(selectedRadio) : null;
        addProductToCart();
    });

    function closeModal() {
        $('#variantModal').addClass('hidden'); // Hide modal
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
        Object.values(cart).forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            cartItemsElement.append(`
                <tr>
                    <td class="border border-gray-300 px-2 py-1">${item.name}</td>
                    <td class="border border-gray-300 px-2 py-1">${item.variant || 'N/A'}</td>
                    <td class="border border-gray-300 px-2 py-1">${item.unit_qty}</td>
                    <td class="border border-gray-300 px-2 py-1">$${item.price.toFixed(2)}</td>
                    <td class="border border-gray-300 px-2 py-1">${item.quantity}</td>
                    <td class="border border-gray-300 px-2 py-1">$${itemTotal.toFixed(2)}</td>
                    <td class="border border-gray-300 px-2 py-1">
                        <button class="bg-red-500 text-white rounded px-2 py-1" onclick="removeFromCart('${item.name}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('#cart-total').text(`Total: $${total.toFixed(2)}`);
    }

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

    // Get the store ID from the user object
    const storeId = '{{ Auth::user()->store_id }}'; // Ensure this is set properly in your Blade view

    const checkoutData = {
        cart: Object.values(cart),
        paymentMethod: paymentMethod,
        store_id: storeId // Include the store ID
    };

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Show loading spinner
    $('#loadingSpinner').removeClass('hidden');

    $.ajax({
        url: '{{ route("checkout.process") }}',
        type: 'POST',
        data: JSON.stringify(checkoutData),
        contentType: 'application/json',
        success: function(response) {
            // Hide loading spinner
            $('#loadingSpinner').addClass('hidden');

            if (response.order_id) {
                alert(`Checkout successful using ${paymentMethod}!`);
                cart = {};
                saveCartToLocalStorage();
                updateCart();
                window.location.href = `{{ url('/receipt') }}/${response.order_id}`;
            } else {
                alert('Checkout data saved locally. Will sync when back online.');
                showOfflineReceipt(checkoutData);
            }
        },
        error: function(xhr) {
            // Hide loading spinner
            $('#loadingSpinner').addClass('hidden');

            console.error("Checkout Error:", xhr);
            alert(`Error during checkout: ${xhr.responseJSON?.message || "An unexpected error occurred."}`);

            if (!navigator.onLine) {
                saveOfflineOrder(checkoutData);
                showOfflineReceipt(checkoutData);
            }
        }
    });
};

    function saveOfflineOrder(data) {
        let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')) || [];
        const orderId = `offline_${new Date().getTime()}`;
        offlineOrders.push({ ...data, order_id: orderId });
        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
        console.log('Saved order locally:', orderId);
    }

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
                    }, index * 1000);
                });
            }
        }
    }

    function removeOfflineOrder(orderId) {
        let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')) || [];
        offlineOrders = offlineOrders.filter(order => order.order_id !== orderId);
        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
        console.log('Removed offline order:', orderId);
    }

    function showOfflineReceipt(data) {
        alert('Checkout data saved locally. Will sync when back online.');
        saveOfflineOrder(data);
    }

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

    function notifyUser(title, options) {
        if (Notification.permission === 'granted') {
            new Notification(title, options);
        } else {
            console.log("Notification permission not granted.");
        }
    }

    window.addEventListener('online', syncOfflineOrders);
    updateCart();
});

    </script>
</body>
</html>
