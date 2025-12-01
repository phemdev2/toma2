
document.addEventListener('alpine:init', () => {
  // ----------------- Store Definitions -----------------
  Alpine.store('refs', { grid: null, cart: null });

  Alpine.store('cart', {
    items: {},
    save() { this.updateBadge(); },
    updateBadge() {
      const count = Object.keys(this.items).length;
      document.title = count > 0 ? `(${count}) Modern POS System` : 'Modern POS System';
    },
    add(product, variant = null) {
      const v = variant ?? { unit_type: product.unit, price: product.sale, unit_qty: 1, id: null };
      const key = `${product.id}-${v.unit_type ?? 'default'}`;
      
      if (this.items[key]) {
        this.items[key].quantity++;
        Alpine.store('toast').show(`Updated quantity for ${product.name}`, "success");
      } else {
        this.items[key] = { 
          ...v, 
          name: product.name, 
          product_id: product.id, 
          variant: v.unit_type ?? null, 
          unit_qty: v.unit_qty, 
          quantity: 1, 
          price: v.price 
        };
        Alpine.store('toast').show(`${product.name} added to cart`, "success");
      }
      this.save();
      
      const cartIcon = document.querySelector('[data-cart-list]');
      if (cartIcon) {
        cartIcon.classList.add('animate-pulse-soft');
        setTimeout(() => cartIcon.classList.remove('animate-pulse-soft'), 1000);
      }
    },
    remove(key) { 
      const item = this.items[key];
      delete this.items[key]; 
      this.save(); 
      Alpine.store('toast').show(`${item.name} removed from cart`, "info");
    },
    clear() { 
      this.items = {}; 
      this.save(); 
    },
    total() { 
      return Object.values(this.items).reduce((s,i) => s + Number(i.price) * Number(i.quantity), 0); 
    }
  });

  Alpine.store('mode', {
    current: 'products',
    toggle() {
      this.current = this.current === 'products' ? 'cart' : 'products';
      Alpine.store('toast').show(`Switched to ${this.current} mode`, "info");
    }
  });

  // ----------------- Cart Sidebar Component -----------------
  Alpine.data('CartSidebar', () => ({
    activeIndex: 0,
    get cart() { return Alpine.store('cart').items; },
    get keys() { return Object.keys(this.cart); },
    cartTotal() { return Alpine.store('cart').total(); },
    saveCart() { Alpine.store('cart').save(); },
    removeItem(key) { Alpine.store('cart').remove(key); },
    clearCart() { 
      if (confirm('Are you sure you want to clear the cart?')) {
        Alpine.store('cart').clear(); 
        Alpine.store('toast').show("Cart cleared", "info");
      }
    },
    
    checkout(method) {
      if (Object.keys(this.cart).length === 0) {
        Alpine.store('toast').show("Cart is empty", "error", 4000);
        return;
      }

      const payload = {
        id: 'POS-' + Date.now(),
        cart: Object.values(this.cart),
        paymentMethod: method,
        store_id: 1,
        created_at: new Date().toISOString(),
        status: 'completed',
        total: this.cartTotal()
      };

      // Simulate successful checkout
      Alpine.store('toast').show("Payment successful!", "success");
      
      // ✅ Generate receipt with Blob
      const url = this.generateReceipt(payload);
      Alpine.store('receiptModal').open(url);
      Alpine.store('receiptModal').lastOrderUrl = url;
      
      Alpine.store('cart').clear();
    },

   generateReceipt(order) {
  // Build text-only version for QR & WhatsApp
  const receiptText = `
${order.id}
Date: ${new Date().toLocaleString()}

Store: {{ Auth::user()->store->company }}
Branch: {{ Auth::user()->store->name }}
Cashier: {{ Auth::user()->name }}
Tel: {{ Auth::user()->store->phone }}
Email: {{ Auth::user()->store->email }}
-------------------------
${order.cart.map(item =>
  `${item.name} ×${item.quantity} @ ₦${parseFloat(item.price).toFixed(2)} = ₦${(item.price * item.quantity).toFixed(2)}`
).join('\n')}
-------------------------
TOTAL: ₦${order.total.toFixed(2)}
PAYMENT: ${order.paymentMethod.toUpperCase()}
-------------------------
{{ Auth::user()->store->thank_you_message }} 
{{ Auth::user()->store->visit_again_message }}
  `.trim();

  const digitalUrl = `https://yourdomain.com/receipts/${order.id}`;
  const whatsappMessage = encodeURIComponent(receiptText + "\n\nDigital Copy: " + digitalUrl);

  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Receipt</title>
      <meta charset="UTF-8" />
      <style>
        body { font-family: monospace; margin: 20px; line-height: 1.4; }
        .receipt { max-width: 300px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .item { display: flex; justify-content: space-between; margin: 5px 0; }
        .total { border-top: 1px dashed #000; padding-top: 10px; font-weight: bold; }
        .qr { margin-top: 15px; text-align: center; }
        .share { margin-top: 20px; text-align: center; }
        input { padding: 5px; width: 90%; margin-bottom: 8px; font-size: 14px; }
        button { padding: 8px 12px; background: #25D366; border: none; color: white; font-weight: bold; border-radius: 5px; cursor: pointer; }
      @media print {
    .no-print { display: none !important; }
  }
    </style>
    </head>
    <body>
      <div class="receipt">
        <div class="header">
          <h2>{{ Auth::user()->store->company }}</h2>
          <p><strong>Receipt ID:</strong> ${order.id}</p>
          <p>${new Date().toLocaleString()}</p>
          <p>Tel: {{ Auth::user()->store->phone }}</p>
          <p>Email: {{ Auth::user()->store->email }}</p>
          <h3>{{ Auth::user()->store->name }}</h3>
          <p style="text-transform: uppercase;">Cashier: {{ Auth::user()->name }}</p>
        </div>

        <div class="items">
          ${order.cart.map(item => `
            <div class="item">
              <span>${item.name} ×${item.quantity}</span>
              <span>₦${(item.price * item.quantity).toFixed(2)}</span>
            </div>
          `).join('')}
        </div>

        <div class="total">
          <div class="item">
            <span>TOTAL</span>
            <span>₦${order.total.toFixed(2)}</span>
          </div>
          <div class="item">
            <span>PAYMENT METHOD</span>
            <span>${order.paymentMethod.toUpperCase()}</span>
          </div>
        </div>

        <div class="qr">
          <p>Scan QR for receipt text:</p>
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(receiptText)}" alt="QR Code" />
          
          <p style="font-size:12px; margin-top:10px;">Or visit online:</p>
          <p style="font-size:11px; word-break:break-all;">${digitalUrl}</p>
        </div>

         <!-- WhatsApp Share -->
<div class="share no-print">
  <h4>Send to Customer</h4>
  
  <!-- Country Code + Phone -->
  <div style="display:flex; gap:5px; margin-bottom:8px;">
    <input id="countryCode" type="text" value="234" 
           style="width:60px; text-align:center; padding:5px; font-size:14px;" />
    <input id="customerPhone" type="tel" placeholder="Enter number (e.g. 8012345678)" 
           style="flex:1; padding:5px; font-size:14px;" />
  </div>

  <!-- WhatsApp Button -->
  <button onclick="
    var code = document.getElementById('countryCode').value.trim().replace('+','');
    var num  = document.getElementById('customerPhone').value.trim().replace(/^0+/, '');
    if(code && num) {
      var full = code + num;
      window.open('https://wa.me/' + full + '?text=${whatsappMessage}', '_blank');
    } else {
      alert('Enter a valid country code and phone number!');
    }
  ">
    Share via WhatsApp
  </button>
</div>


        <div style="text-align:center; margin-top:20px;">
          <p class="text-sm text-gray-600">
            {{ Auth::user()->store->thank_you_message }}  
            {{ Auth::user()->store->visit_again_message }}
          </p>
        </div>
      </div>
    </body>
    </html>
  `;

  const blob = new Blob([html], { type: "text/html" });
  return URL.createObjectURL(blob);
}


,

    moveSelection(dir) {
      if (!this.keys.length) return;
      this.activeIndex = (this.activeIndex + dir + this.keys.length) % this.keys.length;
    },
    removeActive() {
      const key = this.keys[this.activeIndex];
      if (key) {
        this.removeItem(key);
        if (this.activeIndex >= this.keys.length) this.activeIndex = this.keys.length - 1;
      }
    },
    increaseQty() {
      const key = this.keys[this.activeIndex];
      if (key && this.cart[key]) {
        this.cart[key].quantity++;
        this.saveCart();
      }
    },
    decreaseQty() {
      const key = this.keys[this.activeIndex];
      if (key && this.cart[key] && this.cart[key].quantity > 1) {
        this.cart[key].quantity--;
        this.saveCart();
      }
    }
  }));
function checkoutComponent() {
        return {
            cart: {}, // Initialize cart
            loading: false,

            // Check if online
            isOnline() {
                return navigator.onLine;
            },

            // Checkout function
            checkout(paymentMethod) {
                if (Object.keys(this.cart).length === 0) {
                    alert('Your cart is empty.');
                    return;
                }

                const cartData = Object.values(this.cart);
                const storeId = {{ Auth::user()->store_id }};

                const data = {
                    cart: cartData,
                    paymentMethod: paymentMethod,
                    store_id: storeId
                };

                if (this.isOnline()) {
                    this.loading = true;

                    // Set CSRF token for AJAX requests
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
                        success: (response) => {
                            this.loading = false;
                            alert(response.message);
                            if (response.order_id) {
                                window.location.href = `/receipt/${response.order_id}`;
                            }
                            this.clearCart();
                        },
                        error: (xhr) => {
                            this.loading = false;
                            alert(xhr.responseJSON.message || 'An error occurred during checkout. Please try again later.');
                        }
                    });
                } else {
                    this.saveOrderLocally(data);
                    alert('You are offline. Your order has been saved and will be processed once you are back online.');
                    this.clearCart();
                }
            },

            // Clear cart function
            clearCart() {
                this.cart = {}; // Reset cart
                alert('Cart has been cleared.');
            },

            // Save order locally (implement this function as needed)
            saveOrderLocally(data) {
                // Logic to save order locally
                console.log('Order saved locally:', data);
            },
        };
    }
  // ----------------- Product Grid -----------------
  Alpine.data('ProductGrid', () => ({
    query: '',
    debouncedQuery: '',
    products: @json($products) || [],
    activeIndex: 0,
    limit: 20,

    get filtered() {
      const k = this.debouncedQuery.toLowerCase();
      return this.products.filter(p =>
        p.name.toLowerCase().includes(k) ||
        String(p.barcode).toLowerCase().includes(k)
      ).slice(0, this.limit);
    },

    add(product) {
      if (product.variants?.length) Alpine.store('variantModal').open(product);
      else Alpine.store('cart').add(product);
    },

    moveSelection(dir) {
      if (!this.filtered.length) return;
      this.activeIndex = (this.activeIndex + dir + this.filtered.length) % this.filtered.length;
    },

    addActive() {
      if (this.filtered[this.activeIndex]) {
        this.add(this.filtered[this.activeIndex]);
        Alpine.store('toast').show("Added via keyboard", "success");
      }
    },

    handleBarcodeScan(digit) {
      this.scanBuffer = (this.scanBuffer || '') + digit;
      clearTimeout(this._scanTimeout);
      this._scanTimeout = setTimeout(() => {
        if (this.scanBuffer.length >= 6) this.addByBarcode(this.scanBuffer);
        this.scanBuffer = '';
      }, 250);
    },

    addByBarcode(code) {
      const product = this.products.find(p => String(p.barcode) === String(code));
      if (product) {
        this.add(product);
        Alpine.store('toast').show(`Scanned: ${product.name}`, "success");
      } else {
        Alpine.store('toast').show(`No product for barcode ${code}`, "error");
      }
    },

    init() {
      setInterval(() => { this.debouncedQuery = this.query; }, 200);
    }
  }));

  // ----------------- Modals -----------------
  Alpine.store('variantModal', {
    show: false, product: null, selected: 0,
    open(product) { this.product = product; this.show = true; this.selected = 0; },
    close() { this.show = false; this.product = null; },
    select(i) { this.selected = i; },
    add() { 
      const v = this.product.variants[this.selected];
      Alpine.store('cart').add(this.product, v);
      this.close();
    }
  });
  Alpine.data('VariantModal', () => Alpine.store('variantModal'));

  Alpine.store('receiptModal', {
    show: false, url: null, lastOrderUrl: null,
    open(url) { this.url = url; this.show = true; },
    reprint() {
      if (this.lastOrderUrl) {
        this.open(this.lastOrderUrl);
        Alpine.store('toast').show("Reprinting last receipt", "info");
      } else {
        Alpine.store('toast').show("No receipt available to reprint", "error");
      }
    },
    close() { this.show = false; this.url = null; }
  });
  Alpine.data('ReceiptModal', () => Alpine.store('receiptModal'));

  // ----------------- Toast System -----------------
  Alpine.store('toast', {
    toasts: [],
    show(message, type = 'success', timeout = null) {
      const durations = { success: 3000, error: 5000, info: 4000 };
      const toast = { message, type, show: true };
      this.toasts.push(toast);
      setTimeout(() => this.remove(toast), timeout ?? durations[type] ?? 3000);
    },
    remove(toast) {
      toast.show = false;
      setTimeout(() => { this.toasts = this.toasts.filter(t => t !== toast); }, 300);
    }
  });
  Alpine.data('toastStore', () => Alpine.store('toast'));

  // ----------------- Global Keyboard Shortcuts -----------------
  document.addEventListener('keydown', (e) => {
    if (Alpine.store('receiptModal').show) {
      if (e.key === 'Escape') { e.preventDefault(); Alpine.store('receiptModal').close(); }
      else if (e.key === 'Enter') {
        e.preventDefault();
        const iframe = document.querySelector('[x-ref="receiptFrame"]');
        if (iframe?.contentWindow) { iframe.contentWindow.focus(); iframe.contentWindow.print(); }
      }
      return;
    }

    if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

    const mode = Alpine.store('mode').current;
    const grid = Alpine.store('refs').grid;
    const cart = Alpine.store('refs').cart;

    if (mode === 'products' && /^[0-9]$/.test(e.key)) {
      e.preventDefault();
      grid?.handleBarcodeScan(e.key);
      return;
    }

    const shortcuts = {
      'Tab': () => Alpine.store('mode').toggle(),
      'ArrowDown': () => mode === 'products' ? grid?.moveSelection(1) : cart?.moveSelection(1),
      'ArrowUp': () => mode === 'products' ? grid?.moveSelection(-1) : cart?.moveSelection(-1),
      'Enter': () => mode === 'products' ? grid?.addActive() : cart?.removeActive(),
      '+': () => mode === 'cart' && cart?.increaseQty(),
      '-': () => mode === 'cart' && cart?.decreaseQty(),
      'Delete': () => mode === 'cart' && cart?.removeActive(),
      'Backspace': () => mode === 'cart' && cart?.removeActive(),
      'c': () => cart?.clearCart(),
      'C': () => cart?.clearCart(),
      '1': () => cart?.checkout('cash'),
      '2': () => cart?.checkout('bank'),
      '3': () => cart?.checkout('pos'),
      '/': () => document.querySelector('[x-data="ProductGrid"] input[type="text"]')?.focus(),
      'p': () => Alpine.store('receiptModal').reprint(),
      'P': () => Alpine.store('receiptModal').reprint()
    };

    if (shortcuts[e.key]) { e.preventDefault(); shortcuts[e.key](); }
  });

  // Initialize cart badge
  Alpine.store('cart').updateBadge();
});
