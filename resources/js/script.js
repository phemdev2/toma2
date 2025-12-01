

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

    $('#search-bar').on('input', debounce(updateProductList, 300));

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
                    window.location.href = `{{ url('/receipt/offline') }}`; // Optional: redirect to an offline receipt page or show a different message
                }
            },
            error: function(xhr) {
                console.error("Checkout Error:", xhr);
                alert(`Error during checkout: ${xhr.responseJSON?.message || "An unexpected error occurred."}`);

                // Save checkout data locally if offline
                if (!navigator.onLine) {
                    saveOfflineOrder(checkoutData);
                }
            }
        });
    };

    function saveOfflineOrder(data) {
        let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')) || [];

        // Create a unique ID for the offline order
        const orderId = `offline_${new Date().getTime()}`;
        offlineOrders.push({ ...data, order_id: orderId });

        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));

        console.log('Saved order locally:', orderId);
    }

    function syncOfflineOrders() {
        if (navigator.onLine) {
            let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')) || [];

            offlineOrders.forEach(order => {
                $.ajax({
                    url: '{{ route("checkout.process") }}',
                    type: 'POST',
                    data: JSON.stringify(order),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.order_id) {
                            console.log(`Successfully synced offline order ${order.order_id}`);
                            removeOfflineOrder(order.order_id);
                        }
                    },
                    error: function(xhr) {
                        console.error(`Failed to sync offline order ${order.order_id}:`, xhr);
                    }
                });
            });
        }
    }

    function removeOfflineOrder(orderId) {
        let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')) || [];
        offlineOrders = offlineOrders.filter(order => order.order_id !== orderId);
        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
        console.log(`Removed offline order ${orderId}`);
    }

    window.addEventListener('online', syncOfflineOrders);

    $('#addVariantToCart').on('click', function() {
        const selectedRadio = $('input[name="variantRadio"]:checked').val();
        selectedVariant = selectedRadio ? parseInt(selectedRadio) : null;
        addProductToCart();
    });

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

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

    // Fetch initial cart data if available
    if (localStorage.getItem('cart')) {
        cart = JSON.parse(localStorage.getItem('cart'));
        updateCart();
    }
});