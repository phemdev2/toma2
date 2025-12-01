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
      html, body {
    height: 100%;
    margin: 0;
    overflow-x: hidden;
}

body, input, button {
    font-family: 'Roboto', sans-serif; 
}

body {
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}

main {
    flex: 1;
    overflow: hidden; 
}

.custom-scrollbar {
    scrollbar-width: thin; 
    scrollbar-color: #8E24AA #e0e0e0; 
}

.custom-scrollbar::-webkit-scrollbar {
    width: 8px; 
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #e0e0e0; 
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #4caf50; 
    border-radius: 8px; 
}

.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    margin: 0rem;
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
    z-index: 20; 
}

.menu-link {
    font-weight: 500;
}

.search-bar-container {
    position: fixed;
    top: 4rem;
    left: 0;
    width: 100%;
    max-width: 400px;
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

@media (max-width: 768px) {
    .search-bar-container {
        top: 3rem;
        max-width: 100%;
    }
}

.content-wrapper {
    margin-top: 4rem;
    height: calc(90vh - 4rem);
    overflow-y: auto;
}

.cart-table-container {
    max-height: 400px; 
    width: 30rem;
    overflow-y: auto; 
}

@media (max-width: 600px) {
    .cart-table-container {
        padding: 1rem; 
    }
}

@media (max-width: 768px) {
    .cart-table-container {
        height: auto; 
        width: 100%;
    }
}

#empty-cart-placeholder {
    text-align: center; 
    padding: 20px;     
    height: 500px;    
}

@media (max-width: 600px) {
    #empty-cart-placeholder {   
        height: auto;    
    }
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
    flex: 1 1 5px; 
    max-width: 150px; 
    padding: 0.5rem 0.2rem; 
    font-size: 0.875rem; 
    text-align: center;
    transition: opacity 0.2s ease; 
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

.product-title, .text-lg {
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

.products-container {
    flex: 1;
    display: flex;
    margin-top: 3rem;
    padding: 0.3rem;
    margin: 0rem;
    max-height: calc(100vh - 3rem);
    flex-direction: column;
}

@media (min-width: 640px) {
    .products-container {
        padding: 2rem;
    }
}

.modal-close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #f56565;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.modal-close-btn i {
    font-size: 16px;
}

    </style>
</head>
<body class="bg-gray-100 relative flex flex-col min-h-screen">
<nav class="bg-purple-800 p-2 sticky top-0 z-20">
    <div class="container mx-auto flex flex-wrap items-center">
        <!-- Logo -->
        <a class="text-white text-2xl font-bold" href="#">POS</a>

        <!-- Navigation Links -->
        <div class="flex-grow">
            <ul class="hidden md:flex space-x-6 m-2">
                <li><a class="menu-link text-white hover:text-gray-300" href="/dashboard">Home</a></li>
                <li>
    <a 
        href="javascript:void(0)" 
        class="menu-link text-white hover:text-gray-300" 
        onclick="openCashOutModal()">
        Cash Out
    </a>
</li>
            </ul>
        </div>
<!-- Cash Out Modal -->
<div id="cashOutModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-11/12 sm:max-w-md">
        <h2 class="text-lg font-semibold mb-4">Withdraw Cash</h2>
        <form action="{{ route('cashout.store') }}" method="POST" id="cashout-form">
            @csrf
            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                <input type="number" name="amount" id="amount" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter amount" min="1" />
            </div>
            <div class="mb-4">
                <label for="charges" class="block text-sm font-medium text-gray-700">Charges</label>
                <input type="number" name="charges" id="charges" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter charges" min="0" />
            </div>
            <div class="flex justify-end">
                <button type="button" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md mr-2" onclick="closeCashOutModal()">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Withdraw</button>
            </div>
        </form>
    </div>
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
            <li><a class="menu-link block text-white hover:text-gray-300 mb-2" href="/cashout">Cash Out</a></li> <!-- Added Cash Out link -->
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

<main class="flex flex-col md:flex-row relative h-screen">
    <!-- Cart Section -->
    <div class="cart-fixed bg-white p-4 mb-4 rounded shadow-md relative">
    
        <div class="cart-table-container custom-scrollbar">
            <table id="cart-table" class="min-w-full border-collapse border border-gray-200 cart-table">
            <thead class="bg-purple-100 sticky top-0">
    <tr>
        <th class="border border-gray-300 px-2 py-2 text-left text-sm font-semibold text-gray-700">Product</th>
        <th class="border border-gray-300 px-2 py-2 text-left text-sm font-semibold text-gray-700">Unit</th>
       
        <th class="border border-gray-300 px-2 py-2 text-left text-sm font-semibold text-gray-700">Qty</th>
        <th class="border border-gray-300 px-2 py-2 text-left text-sm font-semibold text-gray-700">Subtotal</th>
        <th class="border border-gray-300 px-2 py-2 text-left text-sm font-semibold text-gray-700">X</th>
    </tr>
</thead>
                <tbody id="cart-items">
                    <!-- Sample rows can be added here -->
                </tbody>
            </table>
        </div>
        
        <div class="absolute bottom-0 left-0 right-0 flex flex-col items-center p-2">
        <p class="text-lg font-bold text-right right-10 w-full mt-4" id="cart-total">Total: ₦0.00</p>
    <div class="button-container bg-gray-100 flex space-x-2 mt-2 w-full"> <!-- Full width for button container -->
        <button class="flex-1 bg-green-500 text-white rounded p-2" onclick="checkout('cash')">Cash</button>
        <button class="flex-1 bg-red-500 text-white rounded p-2" onclick="checkout('pos')">POS</button>
        <button class="flex-1 bg-yellow-500 text-white rounded p-2" onclick="checkout('bank')">Bank Transfer</button>
        <button id="clear-cart-btn" class="flex-1 bg-blue-500 text-white rounded p-2" onclick="clearCart()">Clear Cart</button>
    </div>
</div>
    </div>
    <!-- Products Section -->
   

    <!-- Products Section -->
    <div class="content-wrapper flex-1 p-2 ">
        <!-- Search Bar -->
        <div class="fixed top-16 right-4 sm:right-8 md:right-16 lg:right-24 xl:right-32 w-full sm:max-w-sm md:max-w-md lg:max-w-lg bg-white z-10 p-2 shadow-md transition-all duration-300 ease-in-out">
            <div class="flex justify-end">
                <input type="text" id="search-bar" class="border rounded w-full py-2 px-4" placeholder="Search...">
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="product-list">
       @foreach($products as $product)
    <div class="product-card bg-white shadow hover:scale-105 cursor-pointer"
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
                
                @php
                    // Get the store ID of the logged-in user
                    $userStoreId = auth()->user()->store_id;
                    
                    // Filter the product's inventories for the logged-in user's store
                    $filteredInventories = $product->storeInventories->where('store_id', $userStoreId);
                    $totalQuantity = $filteredInventories->sum('quantity');
                @endphp
                
                @if($totalQuantity > 0)
                    <p class="text-xs text-gray-700">Available: {{ $totalQuantity }}</p>
                @elseif($totalQuantity === 0)
                    <p class="text-xs text-red-500">Out of stock</p>
                @else
                    <p class="text-xs text-red-500">Quantity: {{ $totalQuantity }}</p>
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
       function closeModal() {
        document.getElementById('variantModal').classList.add('hidden');
    }
$(document).ready(function() {
    // Initialize variables and functions
    let cart = loadCartFromLocalStorage();
    let selectedProduct = null;
    let selectedVariant = null;
    const productsPerPage = 20;
    let currentPage = 1;

    // Initial display of products
    displayProducts();
    showEmptyCartPlaceholder();

    // Function to check online status
    function isOnline() {
        return navigator.onLine;
    }

    // Display products based on search query
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

    // Debounce function for search input
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Search input handler
    $('#search-bar').on('input', debounce(() => {
        currentPage = 1; // Reset to first page on new search
        displayProducts();
    }, 150));

    // Load more products
    $('#load-more').on('click', function() {
        currentPage++;
        displayProducts();
    });

    // Open product modal
    window.addToCart = function(product) {
        selectedProduct = product;

        if (product.variants && product.variants.length) {
            displayVariantOptions(product.variants);
            showModal();
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

    // Display variant options in modal
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
    }

    // Add selected product/variant to the cart
    function addProductToCart() {
        const product = selectedProduct;
        const variantIndex = selectedVariant !== null ? selectedVariant : 0; // Default to the first variant if none selected
        const variant = product.variants[variantIndex] || product.defaultVariant;

        const itemName = product.name + (variant ? ` - ${variant.unit_type}` : '');
        const itemPrice = parseFloat(variant.price || product.sale);
        const itemQuantity = variant.unit_qty;

        if (cart[itemName]) {
            cart[itemName].quantity += 1; // Increment quantity if item already exists
        } else {
            cart[itemName] = {
                name: itemName,
                variant: variant.unit_type || 'Unit',
                price: itemPrice,
                unit_qty: itemQuantity,
                quantity: 1,
                product_id: product.id,
                variant_id: variant.id || null,
            };
        }

        updateStock(product.id, variant.id || null, -itemQuantity);
        saveCartToLocalStorage();
        updateCart();
        closeModal();
    }

    // Add variant to cart on button click
    $('#addVariantToCart').on('click', function() {
        const selectedRadio = $('input[name="variantRadio"]:checked').val();
        selectedVariant = selectedRadio ? parseInt(selectedRadio) : null; // Update selected variant index
        addProductToCart();
    });

    // Update cart display
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
                    <td class="border border-gray-300 px-1 py-1">${item.name}</td>
                    <td class="border border-gray-300 px-1 py-1">${item.variant || 'Unit'} x${item.unit_qty}</td>
                    <td class="border border-gray-300 px-1 py-1">
                        <input type="number" value="${item.quantity}" class="quantity-input text-center mx-2" min="1" data-item-id="${item.product_id}">
                    </td>
                    <td class="border border-gray-300 w-10 px-1 py-1">&#8358;${itemTotal.toFixed(2)}</td>
                    <td class="border border-gray-300 px-1 py-1">
                        <button class="bg-red-500 text-white rounded px-2 py-1" onclick="removeFromCart('${item.name}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('#cart-total').text(`Total: ₦${total.toFixed(2)}`);

        $('.quantity-input').on('change', function() {
            const itemId = $(this).data('item-id');
            const newQuantity = parseInt($(this).val(), 10);
            if (isNaN(newQuantity) || newQuantity < 1) {
                $(this).val(1); // Reset to 1 if invalid
            } else {
                updateCartItemQuantity(itemId, newQuantity);
            }
        });
    }

    // Update quantity of cart item
    function updateCartItemQuantity(itemId, newQuantity) {
        const itemKey = Object.keys(cart).find(key => cart[key].product_id === itemId);
        if (itemKey) {
            cart[itemKey].quantity = newQuantity;
            saveCartToLocalStorage();
            updateCart();
        }
    }

    // Show empty cart placeholder
    function showEmptyCartPlaceholder() {
        $('#cart-items').html(`
            <tr id="empty-cart-placeholder">
                <td colspan="6" class="text-center py-6">
                    <div class="flex flex-col items-center justify-center min-h-full">
                        <img srcset="img/cart.png" loading="lazy" alt="Empty Cart" class="empty-cart-img mb-2">
                    </div>
                </td>
            </tr>
        `);
    }

    // Save cart to local storage
    function saveCartToLocalStorage() {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    // Load cart from local storage
    function loadCartFromLocalStorage() {
        const savedCart = localStorage.getItem('cart');
        return savedCart ? JSON.parse(savedCart) : {};
    }

    // Update stock based on cart actions
    function updateStock(productId, variantId, changeQty) {
        console.log(`Updating stock for Product ID ${productId} and Variant ID ${variantId} by ${changeQty}`);
        // Implement actual stock update logic here
    }

    // Remove item from cart
    window.removeFromCart = function(itemName) {
        if (cart[itemName]) {
            const item = cart[itemName];
            if (item.quantity > 1) {
                item.quantity -= 1; // Decrease quantity if it's greater than 1
            } else {
                updateStock(item.product_id, item.variant_id, item.quantity); // Restore stock
                delete cart[itemName]; // Remove item from cart
            }
            saveCartToLocalStorage();
            updateCart();
        }
    };

    // Close modal
    function closeModal() {
        const modal = document.getElementById('variantModal');
        if (modal) {
            modal.classList.add('hidden'); // Hide modal
        }
        // Reset selected product and variant
        selectedProduct = null;
        selectedVariant = null;
    }

    // Show modal
    function showModal() {
        const modal = document.getElementById('variantModal');
        if (modal) {
            modal.classList.remove('hidden'); // Show modal
        }
    }

    // Add event listener to close button
    $('#closeModal').on('click', closeModal);

    // Add event listener to cancel button
    $('#cancelModal').on('click', closeModal);

    // Clear cart
    window.clearCart = function() {
        cart = {};
        saveCartToLocalStorage();
        updateCart();
    };

    // Checkout process
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

    // Save order locally for offline processing
    function saveOrderLocally(orderData) {
        const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
        offlineOrders.push(orderData);
        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
    }

    // Process offline orders when back online
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

    // Automatically focus on the search bar on page load
    $('#search-bar').focus();

    // Keep focus on the search bar
    $('#search-bar').on('focus', function() {
        $(this).val($(this).val()); // Keep the value of the input intact
    });
});


function openCashOutModal() {
        document.getElementById('cashOutModal').classList.remove('hidden');
    }

    function closeCashOutModal() {
        document.getElementById('cashOutModal').classList.add('hidden');
    }

    // Optional: Close modal when clicking outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('cashOutModal');
        if (event.target === modal) {
            closeCashOutModal();
        }
    };
</script>



</body>
</html>
