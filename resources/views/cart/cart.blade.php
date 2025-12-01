<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Cards</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-card {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
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
                                    <th>Variant</th>
                                    <th>Unit Qty</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <!-- Cart items will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                    <p class="total h5" id="cart-total">Total: $0.00</p>
                    <div class="checkout-buttons mt-3">
                        <button type="button" class="btn btn-success btn-sm" id="cash-checkout-button" aria-label="Cash Checkout">Cash</button>
                        <button type="button" class="btn btn-danger btn-sm" id="pos-checkout-button" aria-label="POS Checkout">POS</button>
                        <form action="{{ route('cart.clear') }}" method="POST" id="clear-cart-form" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm" aria-label="Clear Cart">Clear Cart</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="col-md-8">
                <div class="search-container mb-4">
                    <input type="text" id="search-bar" class="form-control" placeholder="Search by name or barcode...">
                </div>
                <div id="products-container" class="row">
                @foreach($products as $product)
                    <div class="col-6 col-sm-4 col-md-3 d-flex align-items-stretch">
                        <div class="product-card card shadow-sm" data-product="{{ $product->id }}">
                            <div class="card-body text-center d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <h6 class="card-subtitle mb-2 text-muted">Barcode: {{ $product->barcode }}</h6>
                                    <p class="card-text">Sale Price: ${{ number_format($product->sale, 2) }}</p>
                                    @if($product->inventories->isNotEmpty())
                                        <p class="card-text">Available Quantity: {{ $product->inventories->first()->quantity }}</p>
                                    @else
                                        <p class="card-text text-danger">Out of stock</p>
                                    @endif
                                </div>
                                <div class="variant-container mb-3">
                                    <label for="variant-select-{{ $product->id }}">Select Variant</label>
                                    <select id="variant-select-{{ $product->id }}" class="form-control">
                                        @foreach($product->variants as $variant)
                                            <option value="{{ $variant->id }}" data-price="{{ $variant->price }}" data-qty="{{ $variant->unit_qty }}">
                                                {{ $variant->unit_type }} - {{ $variant->unit_qty }} units - ${{ number_format($variant->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-secondary view-variants-button" data-product="{{ $product->id }}">Select Variant</button>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Variant Selection Modal -->
    <div class="modal fade" id="variant-modal" tabindex="-1" role="dialog" aria-labelledby="variantModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="variantModalLabel">Select Variant</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="variant-details">
                        <!-- Variant details will be loaded here -->
                    </div>
                    <form id="add-to-cart-form">
                        @csrf
                        <input type="hidden" name="product_id" id="modal-product-id">
                        <input type="hidden" name="price" id="modal-price">
                        <input type="hidden" name="variant_id" id="modal-variant-id">
                        <input type="number" name="quantity" min="1" value="1" class="form-control mb-2" id="modal-quantity" required>
                        <button type="button" class="btn btn-primary" id="modal-add-to-cart">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Print Template -->
    <div id="receipt-template" style="display: none;">
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <h2>Receipt</h2>
            <hr>
            <div id="receipt-content"></div>
            <hr>
            <p id="receipt-total" style="font-size: 1.2em;"></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let cart = JSON.parse(localStorage.getItem('cart')) || {};

            function updateCart() {
                const cartItems = document.getElementById('cart-items');
                const cartTotal = document.getElementById('cart-total');
                cartItems.innerHTML = '';
                let total = 0;

                Object.entries(cart).forEach(([key, item]) => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.name || 'Unknown Product'}</td>
                        <td>${item.variant || 'Unknown Variant'}</td>
                        <td>${item.unit_qty || 'N/A'}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>$${itemTotal.toFixed(2)}</td>
                        <td><span class="delete-icon" data-item="${item.name}" role="button" aria-label="Remove item">🗑️</span></td>
                    `;
                    cartItems.appendChild(row);
                });

                cartTotal.innerText = `Total: $${total.toFixed(2)}`;
                localStorage.setItem('cart', JSON.stringify(cart));
            }

            function addToCart(productId, variantId, quantity, price, unitQty) {
                const productName = document.querySelector(`.product-card[data-product="${productId}"] .card-title`).textContent.trim();
                const variant = document.querySelector(`#variant-select-${productId} option[value="${variantId}"]`).textContent.split(' - ')[0];
                const itemName = `${productName} (${variant})`;

                if (cart[itemName]) {
                    cart[itemName].quantity += quantity;
                } else {
                    cart[itemName] = {
                        name: itemName,
                        variant: variant,
                        price: price,
                        unit_qty: unitQty,
                        quantity: quantity
                    };
                }
                updateCart();
            }

            function filterProducts() {
                const searchValue = document.getElementById('search-bar').value.toLowerCase();
                document.querySelectorAll('.product-card').forEach(card => {
                    const name = card.querySelector('.card-title').textContent.toLowerCase();
                    const barcode = card.querySelector('.card-subtitle').textContent.toLowerCase();
                    card.style.display = (name.includes(searchValue) || barcode.includes(searchValue)) ? 'block' : 'none';
                });
            }

            let debounceTimeout;
            document.getElementById('search-bar').addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(filterProducts, 300); // Debounce for 300ms
            });

            document.getElementById('products-container').addEventListener('click', function (event) {
                if (event.target.classList.contains('view-variants-button')) {
                    const productId = event.target.getAttribute('data-product');
                    const productCard = event.target.closest('.product-card');
                    const variantSelect = productCard.querySelector(`#variant-select-${productId}`);

                    if (!variantSelect) {
                        console.error('No variant select found for product ID:', productId);
                        return;
                    }

                    const variants = variantSelect.querySelectorAll('option');
                    let variantDetails = '<label for="modal-variant-select">Select Variant</label><select id="modal-variant-select" class="form-control">';

                    variants.forEach(variant => {
                        variantDetails += `
                            <option value="${variant.value}" data-price="${variant.dataset.price}" data-qty="${variant.dataset.qty}">
                                ${variant.textContent}
                            </option>
                        `;
                    });

                    variantDetails += '</select>';

                    document.getElementById('variant-details').innerHTML = variantDetails;
                    document.getElementById('modal-product-id').value = productId;
                    $('#variant-modal').modal('show');
                }
            });

            document.getElementById('variant-details').addEventListener('change', function (event) {
                if (event.target.id === 'modal-variant-select') {
                    const selectedOption = event.target.options[event.target.selectedIndex];
                    document.getElementById('modal-price').value = selectedOption.dataset.price;
                }
            });

            document.getElementById('modal-add-to-cart').addEventListener('click', function () {
                const productId = document.getElementById('modal-product-id').value;
                const variantSelect = document.getElementById('modal-variant-select');
                const selectedVariant = variantSelect.options[variantSelect.selectedIndex];
                const price = parseFloat(selectedVariant.dataset.price);
                const unitQty = parseInt(selectedVariant.dataset.qty);
                const quantity = parseInt(document.getElementById('modal-quantity').value);

                if (!price || !unitQty || quantity < 1) return;

                addToCart(productId, selectedVariant.value, quantity, price, unitQty);
                $('#variant-modal').modal('hide');
            });

            document.getElementById('cart-items').addEventListener('click', function (event) {
                if (event.target.classList.contains('delete-icon')) {
                    const itemName = event.target.getAttribute('data-item');
                    delete cart[itemName];
                    updateCart();
                }
            });

            document.getElementById('clear-cart-form').addEventListener('submit', function (event) {
                event.preventDefault();
                if (confirm('Are you sure you want to clear the cart?')) {
                    cart = {};
                    updateCart();
                }
            });

            function checkout(checkoutType) {
                fetch("{{ route('cart.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        cart: cart,
                        checkout_type: checkoutType
                    })
                }).then(response => response.json())
                  .then(data => {
                    if (data.success) {
                        printReceipt();
                        cart = {}; // Clear the cart object
                        updateCart(); // Update the cart UI
                        localStorage.removeItem('cart'); // Remove cart from local storage
                    } else {
                        console.error('Checkout failed:', data.message);
                        alert('Checkout failed: ' + data.message);
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during checkout.');
                });
            }

            document.getElementById('cash-checkout-button').addEventListener('click', function () {
                checkout('cash');
            });

            document.getElementById('pos-checkout-button').addEventListener('click', function () {
                checkout('pos');
            });

            function printReceipt() {
                const receiptContent = document.getElementById('receipt-content');
                const receiptTotal = document.getElementById('receipt-total');

                receiptContent.innerHTML = '';
                let total = 0;

                Object.entries(cart).forEach(([key, item]) => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    receiptContent.innerHTML += `
                        <div style="margin-bottom: 10px;">
                            <p><strong>${item.name || 'Unknown Product'}</strong> (${item.variant || 'Unknown Variant'})</p>
                            <p>Unit Qty: ${item.unit_qty || 'N/A'}</p>
                            <p>Qty: ${item.quantity} x $${item.price.toFixed(2)} = $${itemTotal.toFixed(2)}</p>
                        </div>
                    `;
                });

                receiptTotal.innerText = `Total: $${total.toFixed(2)}`;

                const newWindow = window.open('', '', 'height=600,width=800');
                newWindow.document.write('<html><head><title>Receipt</title></head><body>');
                newWindow.document.write('<h2>Receipt</h2><hr>');
                newWindow.document.write(receiptContent.innerHTML);
                newWindow.document.write('<hr>');
                newWindow.document.write(`<p style="font-size: 1.2em;">Total: $${total.toFixed(2)}</p>`);
                newWindow.document.write('</body></html>');
                newWindow.document.close();
                newWindow.focus();
                newWindow.print();
            }

            updateCart();
        });
    </script>
</body>
</html>
