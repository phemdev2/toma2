<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif; 
        }
        body {
      overflow: hidden;
    }
        .custom-scrollbar {
            scrollbar-width: thin; 
            scrollbar-color: #8E24AA #e0e0e0; 
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 1px; 
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #e0e0e0; 
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #4caf50; 
            border-radius: 1px; 
        }

        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            margin: 0px;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .product-card .product-title {
            text-transform: uppercase; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }

        .product-card img {
            border-bottom: 1px solid #e5e7eb; 
        }
        #mobileMenu {
      z-index: 20; /* Higher than the search bar */
    }
    
    /* Ensure the font is applied to the menu */
    .menu-link {
      font-family: 'Roboto', sans-serif;
      font-weight: 500;
    }
        .search-bar-container {
            position: fixed;
            top: 4rem;
            left: 0;
            width: 250px;
            background-color: #fff;
            z-index: 10;
            padding: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .search-bar-container input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .search-bar-container input:focus {
            border-color: #007BFF;
        }

        .search-bar-container .search-button {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-bar-container .search-button:hover {
            background-color: #0056b3;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .search-bar-container {
                width: 100%;
                top: 2rem;
            }
        }

        .content-wrapper {
            margin-top: 4rem;
            height: calc(100vh - 4rem);
            overflow-y: auto;
        }

        .cart-table-container {
            height: 600px;
            width: 30rem;
            overflow-y: auto; 
        }
        @media (max-width: 768px) {
            .cart-table-container {
            height: auto;
            width: 100%;
            overflow-y: auto; 
        }
        }
        #empty-cart-placeholder {
            text-align: center; 
            padding: 20px;     
            height: 500px;    
        }

        .empty-cart-img {
            max-width: 100%; 
            height: auto;
        }

        .cart-table th, .cart-table td {
            font-size: 0.875rem; 
        }

        .button-container {
            margin-top: 0.5rem; 
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem; 
        }

        .button-container button {
            flex: 1 1 10px; 
            max-width: 150px; 
            padding: 0.5rem 0.2rem; 
            font-size: 0.875rem; 
            font-weight: bold;
            text-align: center;
        }

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        .button-container button:hover {
            opacity: 0.9; 
        }

        .button-container button:focus {
            outline: 2px solid #4caf50;
            outline-offset: 2px; 
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

        .product-title {
            font-family: 'Roboto', sans-serif;
        }

        .text-lg {
            font-family: 'Roboto', sans-serif;
        }

        @media (max-width: 640px) {
            .cart-table-container {
                max-height: 400px; 
            }
            .empty-cart-placeholder {
                text-align: center; 
                padding: 10px;     
            }
        }

        /* Reduce card margin and ensure responsiveness */
        .products-container {
            flex: 1;
            display: flex;
            margin-top: 3rem;
            padding: 0.3rem;
            margin: 0rem;
            max-height: calc(100vh - 3rem);
            flex-direction: column;
        }

        .product-card {
            margin: 0rem; 
        }

        @media (min-width: 640px) {
            .products-container {
                padding: 2rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
<nav class="bg-purple-800 p-4 sticky top-0 z-20">
    <div class="container mx-auto flex flex-wrap items-center">
      <!-- Logo -->
      <a class="text-white text-2xl font-bold" href="#">POS</a>
      
      <!-- Navigation Links -->
      <div class="flex-grow">
        <ul class="hidden md:flex space-x-6">
          <li><a class="menu-link text-white hover:text-gray-300" href="/dashboard">Home</a></li>
          <!-- Add more links here as needed -->
        </ul>
      </div>

      <!-- User Info and Network Status -->
      <div class="flex items-center space-x-4 text-white text-sm">
        <span class="hidden md:flex items-center space-x-4">
          <span class="flex items-center">
            <i class="fas fa-user mr-2"></i> 
            <strong>{{ Auth::user()->name }}</strong>
          </span>
          <span class="flex items-center">
            <i class="fas fa-store mr-2"></i>
            <strong>{{ Auth::user()->store_id }}</strong>
          </span>
          <span class="flex items-center">
            <i class="fas fa-store-alt mr-2"></i> 
            <strong>{{ Auth::user()->store->name }}</strong>
          </span>
        </span>
        <i class="fas fa-wifi" id="wifiIcon" aria-label="Network status"></i>
      </div>

      <!-- Hamburger Menu for Mobile -->
      <div class="md:hidden flex items-center">
        <button id="menuToggle" class="text-white text-2xl">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobileMenu" class="md:hidden hidden mt-2 bg-purple-700 p-4 rounded-lg absolute top-full right-0 w-full sm:max-w-sm md:max-w-md lg:max-w-lg transition-all duration-300 ease-in-out">
      <ul>
        <li><a class="menu-link block text-white hover:text-gray-300 mb-2" href="/dashboard">Home</a></li>
        <!-- Add more links here as needed -->
      </ul>
      <div class="flex items-center space-x-4 text-white text-sm mt-4">
        <span class="flex items-center">
          <i class="fas fa-user mr-2"></i> 
          <strong>{{ Auth::user()->name }}</strong>
        </span>
        <span class="flex items-center">
          <i class="fas fa-store mr-2"></i>
          <strong>{{ Auth::user()->store_id }}</strong>
        </span>
        <span class="flex items-center">
          <i class="fas fa-store-alt mr-2"></i> 
          <strong>{{ Auth::user()->store->name }}</strong>
        </span>
        <i class="fas fa-wifi" id="wifiIcon" aria-label="Network status"></i>
      </div>
    </div>
  </nav>


<main class="flex flex-col md:flex-row h-screen">
    <!-- Cart Section -->
    <div class="cart-fixed bg-white p-4 rounded shadow-md">
        <h2 class="text-md font-semibold mb-3">Cart</h2>
        <div class="cart-table-container custom-scrollbar">
            <table id="cart-table" class="min-w-full border-collapse border border-gray-200 cart-table">
                <thead class="bg-purple-100 sticky top-0">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-left">Product</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Unit</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Unit Qty</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Qty</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Total</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody id="cart-items">
                    <!-- Sample rows can be added here -->
                </tbody>
            </table>
        </div>
        <p class="text-lg font-bold mt-2" id="cart-total">Total: ₦0.00</p>
        <div class="button-container">
            <button class="bg-green-500 text-white rounded" onclick="checkout('cash')">Cash</button>
            <button class="bg-red-500 text-white rounded" onclick="checkout('pos')">POS</button>
            <button class="bg-yellow-500 text-white rounded" onclick="checkout('bank')">Bank Transfer</button>
            <button id="clear-cart-btn" class="bg-blue-500 text-white rounded" onclick="clearCart()">Clear Cart</button>
        </div>
    </div>

    <!-- Products Section -->
   

    <!-- Products Section -->
    <div class="content-wrapper flex-1 p-2 mt-16">
     <!-- Search Bar -->
  <div class="fixed top-16 right-4 sm:right-8 md:right-16 lg:right-24 xl:right-32 w-full sm:max-w-sm md:max-w-md lg:max-w-lg bg-white z-10 p-2 shadow-md transition-all duration-300 ease-in-out">
    <div class="flex justify-end">
      <input type="text" id="search-bar" class="border rounded w-full py-2 px-4" placeholder="Search...">
    </div>
  </div>

  </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 mt-16" id="product-list">
            @foreach($products as $product)
            <div class="product-card bg-white shadow hover:scale-105 cursor-pointer h-120"
                 data-product-id="{{ $product->id }}" 
                 data-product-name="{{ $product->name }}" 
                 data-product-barcode="{{ $product->barcode }}" 
                 data-product-price="{{ $product->sale }}" 
                 data-product-variants="{{ json_encode($product->variants) }}"
                 onclick="addToCart({{ $product->toJson() }})">
                <div class="p-4 flex flex-col justify-between text-center h-full">
                    <div>
                        <p class="product-title text-sm text-gray-600 mb-1 font-bold">{{ $product->name }}</p>
                        <h6 class="text-xs text-gray-500 mb-1">{{ $product->barcode }}</h6>
                        <p class="text-xs text-gray-900">Sale: &#8358;{{ number_format($product->sale, 2) }}</p>
                        @if($product->inventories->isNotEmpty())
                            <p class="text-xs text-gray-700">Available: {{ $product->inventories->first()->quantity }}</p>
                        @else
                            <p class="text-xs text-red-500">Out of stock</p>
                        @endif
                    </div>
                    <button 
                        class="bg-purple-500 text-white rounded px-3 py-1 text-xs" 
                        onclick="event.stopPropagation(); addToCart({{ $product->toJson() }});" 
                        aria-label="Add {{ $product->name }} to cart"
                        role="button"
                        aria-pressed="false">
                        Add to Cart
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</main>

<!-- Modal for Variant Selection -->
<div id="variantModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-purple-100 rounded-lg shadow-lg w-11/12 sm:w-3/4 md:w-1/2 lg:w-1/3">
        <div class="p-4 border-b flex justify-between items-center">
            <h5 class="text-lg font-semibold" id="variantModalLabel">Select Variant</h5>
            <button class="text-gray-500" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4" id="variantOptions">
            <!-- Variant options will be dynamically inserted here -->
        </div>
        <div class="p-4 border-t flex justify-end">
            <button class="bg-gray-300 rounded px-4 py-2 mr-2" onclick="closeModal()">Cancel</button>
            <button class="bg-blue-500 text-white rounded px-4 py-2" id="addVariantToCart">Add to Cart</button>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loadingSpinner" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50 hidden">
    <div class="spinner" aria-label="Loading..."></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.getElementById('menuToggle').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobileMenu');
      mobileMenu.classList.toggle('hidden');
    });
$(document).ready(function() {
    showEmptyCartPlaceholder();
    let cart = loadCartFromLocalStorage();
    let selectedProduct = null;
    let selectedVariant = null;
    const productsPerPage = 20;
    let currentPage = 1;

    // Initial display of products
    displayProducts();

    function isOnline() {
        return navigator.onLine;
    }

    function displayProducts() {
        const searchQuery = $('#search-bar').val().toLowerCase();
        const cards = $('#product-list .product-card');
        let visibleCount = 0;

        cards.each(function(index) {
            const productName = String($(this).data('product-name')).toLowerCase();
            const productBarcode = String($(this).data('product-barcode')).toLowerCase();
            const isMatch = productName.includes(searchQuery) || productBarcode.includes(searchQuery);

            if (isMatch && visibleCount < (currentPage * productsPerPage)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        $('#load-more').toggle(visibleCount > currentPage * productsPerPage);
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    $('#search-bar').on('input', debounce(() => {
        currentPage = 1; // Reset to first page on new search
        displayProducts();
    }, 150));

    $('#load-more').on('click', function() {
        currentPage++;
        displayProducts();
    });

    window.addToCart = function(product) {
        selectedProduct = product;

        if (product.variants && product.variants.length) {
            displayVariantOptions(product.variants);
            $('#variantModal').removeClass('hidden'); // Show modal
        } else {
            selectedVariant = null;
            selectedProduct.defaultVariant = {
                unit_type: product.unit,
                price: product.sale,
                unit_qty: 1
            };
            addProductToCart();
        }
    };

    function displayVariantOptions(variants) {
        const variantContainer = $('#variantOptions').empty();

        variants.forEach((variant, index) => {
            const variantName = variant.unit_type || "Unnamed Variant";
            const variantPrice = parseFloat(variant.price || 0).toFixed(2);
            const variantQuantity = variant.unit_qty || 0;

            variantContainer.append(`
                <div class="flex items-center mb-2">
                    <input type="radio" name="variantRadio" id="variant${index}" value="${index}" class="mr-2" ${index === 0 ? 'checked' : ''}>
                    <label for="variant${index}" class="flex-1">
                        ${variantName} - &#8358;${variantPrice} (Qty: ${variantQuantity})
                    </label>
                </div>
            `);
        });

        if (!variants.length) {
            variantContainer.append(`
                <div class="flex items-center mb-2">
                    <input type="radio" name="variantRadio" id="variant0" value="0" class="mr-2" checked>
                    <label for="variant0" class="flex-1">
                        ${selectedProduct.defaultVariant.unit_type} - &#8358;${selectedProduct.defaultVariant.price} (Qty: ${selectedProduct.defaultVariant.unit_qty})
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
        const cartItemsElement = $('#cart-items').empty();

        if (Object.keys(cart).length === 0) {
            showEmptyCartPlaceholder();
            $('#cart-total').text('Total: ₦0.00');
            return;
        }

        let total = 0;
        Object.values(cart).forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            cartItemsElement.append(`
                <tr>
                    <td class="border border-gray-300 px-2 py-1">${item.name}</td>
                    <td class="border border-gray-300 px-2 py-1">${item.variant || 'Unit'}</td>
                    <td class="border border-gray-300 px-2 py-1">${item.unit_qty}</td>
                    <td class="border border-gray-300 px-2 py-1">${item.quantity}</td>
                    <td class="border border-gray-300 px-2 py-1">&#8358;${itemTotal.toFixed(2)}</td>
                    <td class="border border-gray-300 px-2 py-1">
                        <button class="bg-red-500 text-white rounded px-2 py-1" onclick="removeFromCart('${item.name}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('#cart-total').text(`Total: ₦${total.toFixed(2)}`);
    }

    function showEmptyCartPlaceholder() {
    $('#cart-items').html(`
        <tr id="empty-cart-placeholder" class="empty-cart-placeholder">
            <td colspan="3" class="text-center py-6">
                <div class="flex flex-col items-center justify-center min-h-full">
                    <img src="img/cart.png" alt="Empty Cart" class="empty-cart-img mb-2">
                   
                </div>
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
        console.log(`Updating stock for Product ID ${productId} and Variant ID ${variantId} by ${changeQty}`);
        // Implement actual stock update logic here
    }

    window.removeFromCart = function(itemName) {
        if (cart[itemName]) {
            const item = cart[itemName];
            updateStock(item.product_id, item.variant_id, item.quantity);
            delete cart[itemName];
            saveCartToLocalStorage();
            updateCart();
        }
    };

    window.clearCart = function() {
        cart = {};
        saveCartToLocalStorage();
        updateCart();
    };

    window.checkout = function(paymentMethod) {
        if (Object.keys(cart).length === 0) {
            alert('Your cart is empty.');
            return;
        }

        const cartData = Object.values(cart);
        const storeId = {{ Auth::user()->store_id }}; // Get the store_id from the backend or context

        const data = {
            cart: cartData,
            paymentMethod: paymentMethod,
            store_id: storeId
        };

        if (isOnline()) {
            // Online - Proceed with normal checkout
            $('#loadingSpinner').removeClass('hidden');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '{{ route("checkout.process") }}',
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function(response) {
                    $('#loadingSpinner').addClass('hidden');
                    alert(response.message);
                    if (response.order_id) {
                        window.location.href = `/receipt/${response.order_id}`;
                    }
                    clearCart();
                },
                error: function(xhr) {
                    $('#loadingSpinner').addClass('hidden');
                    alert(xhr.responseJSON.message || 'An error occurred during checkout.');
                }
            });
        } else {
            // Offline - Save the order locally
            saveOrderLocally(data);
            alert('You are offline. Your order has been saved and will be processed once you are back online.');
            clearCart();
        }
    };

    function saveOrderLocally(orderData) {
        const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
        offlineOrders.push(orderData);
        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
    }

    function processOfflineOrders() {
        const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
        if (offlineOrders.length === 0) return;

        $('#loadingSpinner').removeClass('hidden');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '{{ route("checkout.process") }}',
            type: 'POST',
            data: JSON.stringify({ orders: offlineOrders }),
            contentType: 'application/json',
            success: function(response) {
                $('#loadingSpinner').addClass('hidden');
                localStorage.removeItem('offlineOrders');
                alert('Offline orders have been processed. Order ID(s) saved.');
            },
            error: function(xhr) {
                $('#loadingSpinner').addClass('hidden');
                console.error('Failed to sync offline orders:', xhr.responseJSON.message || 'An error occurred.');
            }
        });
    }

    // Handle offline/online events
    window.addEventListener('online', processOfflineOrders);
    window.addEventListener('offline', function() {
        console.log('You are offline. Orders will be saved locally.');
    });

});
</script>



</body>
</html>
