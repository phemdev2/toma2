// resources/views/cart.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search and Add to Cart</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div>
        <input type="text" id="search-box" placeholder="Search by name or barcode">
        <button id="search-btn">Search</button>
    </div>

    <div id="product-list"></div>

    <script>
        // Search functionality
        $('#search-btn').click(function() {
            const query = $('#search-box').val();

            $.ajax({
                url: '/search',
                method: 'GET',
                data: { query: query },
                success: function(response) {
                    let productsHtml = '';
                    response.forEach(function(product) {
                        productsHtml += `
                            <div class="product-item" data-id="${product.id}">
                                <p>Name: ${product.name}</p>
                                <p>Barcode: ${product.barcode}</p>
                                <p>Price: $${product.price}</p>
                                <p>Stock: ${product.quantity}</p>
                                <input type="number" id="quantity-${product.id}" value="1" min="1" max="${product.quantity}">
                                <button class="add-to-cart-btn" data-id="${product.id}">Add to Cart</button>
                            </div>
                        `;
                    });
                    $('#product-list').html(productsHtml);
                }
            });
        });

        // Add product to cart
        $(document).on('click', '.add-to-cart-btn', function() {
            const productId = $(this).data('id');
            const quantity = $(`#quantity-${productId}`).val();

            $.ajax({
                url: '/cart/add',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert(response.message);
                },
                error: function(response) {
                    alert(response.responseJSON.message);
                }
            });
        });
    </script>
</body>
</html>
