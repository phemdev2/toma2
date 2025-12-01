<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>POS Shopping Cart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 font-['Inter'] text-gray-800">

  <!-- Layout Wrapper -->
<div class="flex w-full h-screen overflow-hidden">
  <!-- Cart Sidebar -->
  <div x-data="CartSidebar"
       x-init="$store.refs.cart = $data"
       class="bg-white border-r border-gray-200 shadow-sm z-40 
       w-full lg:w-2/6 h-screen flex flex-col">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-white sticky top-0 z-10">
      <h2 class="text-base font-semibold flex items-center gap-2">
        <i class="fas fa-shopping-cart text-purple-600"></i>
        Cart
      </h2>
      <button @click="clearCart()" 
              class="text-xs px-3 py-1 rounded bg-red-50 text-red-600 hover:bg-red-100">
        Clear cart
      </button>
    </div>

    <!-- Items -->
    <div class="flex-1 overflow-y-auto p-4 space-y-2">
      <template x-if="Object.keys(cart).length === 0">
        <div class="flex flex-col items-center justify-center text-gray-400 py-12">
          <i class="fas fa-shopping-basket text-4xl mb-3"></i>
          <p class="text-sm">Your cart is empty</p>
        </div>
      </template>

      <template x-for="(item, key, index) in cart" :key="key">
        <div class="flex justify-between items-center p-2 rounded-md"
             :class="index === activeIndex ? 'bg-purple-50 ring-2 ring-purple-400' : ''">
          <div>
            <p class="font-medium text-sm truncate" 
               x-text="shortName(item.name)" 
               :title="item.name"></p>
            <p class="text-xs text-gray-400" 
               x-text="item.variant ? `${item.variant} x${item.unit_qty}` : `Qty: ${item.unit_qty}`"></p>
          </div>
          <div class="flex items-center gap-2">
            <input type="number" min="1"
                   class="w-14 text-center border border-gray-200 rounded-md text-sm focus:ring-purple-500 focus:border-purple-500"
                   x-model.number="item.quantity" @input="saveCart()">
            <button @click="removeItem(key)" class="text-gray-400 hover:text-red-500">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- Footer -->
    <div class="p-4 border-t border-gray-200 bg-white sticky bottom-0 z-10">
      <p class="font-semibold text-lg text-right mb-3">
        ₦<span x-text="cartTotal().toFixed(2)"></span>
      </p>
      <div class="grid grid-cols-3 gap-2">
        <button class="bg-green-500 hover:bg-green-600 text-white rounded-md py-2 text-sm font-medium" @click="checkout('cash')">Cash</button>
        <button class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-md py-2 text-sm font-medium" @click="checkout('bank')">Bank</button>
        <button class="bg-purple-600 hover:bg-purple-700 text-white rounded-md py-2 text-sm font-medium" @click="checkout('pos')">POS</button>
      </div>
    </div>
  </div>

  <!-- Main Content (Navbar + Products) -->
  <div class="flex-1 flex flex-col h-screen overflow-hidden">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-30">
      <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center gap-2">
          <i class="fas fa-store text-purple-600"></i>
          <span class="text-base font-semibold text-gray-700">POS</span>
          <span x-text="$store.mode.current.toUpperCase()" 
                class="ml-3 px-2 py-1 text-xs rounded bg-purple-100 text-purple-600 font-semibold"></span>
        </div>
        <ul class="hidden md:flex gap-6 text-sm font-medium">
          <li><a href="/dashboard" class="text-gray-600 hover:text-purple-600">Home</a></li>
          <li><a href="#" class="text-gray-600 hover:text-purple-600">Products</a></li>
          <li><a href="#" class="text-gray-600 hover:text-purple-600">Reports</a></li>
        </ul>
        <div class="flex items-center gap-4 text-xs text-gray-500">
          <span><i class="fas fa-user mr-1"></i>{{ Auth::user()->name }}</span>
          <span><i class="fas fa-store mr-1"></i>ID: {{ Auth::user()->store_id }}</span>
          <span><i class="fas fa-store-alt mr-1"></i>{{ Auth::user()->store->name }}</span>
        </div>
      </div>
    </nav>

    <!-- Product Grid -->
    <div x-data="ProductGrid"
         x-init="$store.refs.grid = $data"
         class="p-6 flex-1 overflow-y-auto">
      <div class="sticky top-0 bg-gray-50 z-20 pb-2">
        <input type="text" x-model="query" placeholder="Search products..."
               class="w-full p-3 border border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" />
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mt-4">
        <template x-for="(product, index) in filtered" :key="product.id">
          <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer"
               :class="index === activeIndex ? 'ring-2 ring-purple-500' : ''"
               @click="add(product)">
            <h2 class="text-sm font-medium truncate" x-text="product.name"></h2>
            <p class="text-xs text-gray-400" x-text="String(product.barcode)"></p>
            <p class="text-base font-semibold text-purple-600 mt-1">
              ₦<span x-text="parseFloat(product.sale).toFixed(2)"></span>
            </p>
            <p class="text-xs mt-1" :class="getStock(product) > 0 ? 'text-gray-500' : 'text-red-500'">
              Stock: <span x-text="getStock(product)"></span>
            </p>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>

<!-- Variant Modal -->
<div x-data="VariantModal" x-show="show"
     class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 px-4"
     style="display: none;">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6">
    <h2 class="text-base font-semibold mb-4" x-text="product?.name"></h2>
    <div class="space-y-2">
      <template x-for="(variant, index) in product?.variants" :key="variant.id">
        <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-md cursor-pointer hover:bg-purple-50">
          <input type="radio" name="variant" :value="index" class="text-purple-600 focus:ring-purple-500"
                 :checked="index === selected" @change="select(index)">
          <span x-text="`${variant.unit_type} - ₦${parseFloat(variant.price).toFixed(2)} (x${variant.unit_qty})`"
                class="text-sm"></span>
        </label>
      </template>
    </div>
    <div class="flex justify-end mt-6 gap-2">
      <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-sm" @click="close()">Cancel</button>
      <button class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-medium" @click="add()">Add</button>
    </div>
  </div>
</div>

<!-- Receipt Modal -->
<div x-data="ReceiptModal" x-show="show"
     class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 px-4"
     style="display: none;">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl h-[80vh] flex flex-col">
    <div class="p-4 border-b flex justify-between items-center">
      <h2 class="text-base font-semibold">Receipt</h2>
      <button @click="close()" class="text-gray-400 hover:text-red-600">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <iframe x-bind:src="url" class="flex-1 w-full border-none"></iframe>
  </div>
</div>

<!-- Toast Notifications -->
<div x-data="toastStore" class="fixed top-4 right-4 z-50 space-y-3" aria-live="polite" aria-atomic="true">
  <template x-for="(toast, index) in toasts" :key="index">
    <div x-show="toast.show"
         x-transition
         class="max-w-xs w-full bg-white border rounded-lg shadow-md p-3 flex items-start gap-2 text-sm"
         :class="{
           'border-green-400': toast.type === 'success',
           'border-red-400': toast.type === 'error',
           'border-yellow-400': toast.type === 'info'
         }">
      <div>
        <template x-if="toast.type === 'success'"><i class="fas fa-check-circle text-green-500"></i></template>
        <template x-if="toast.type === 'error'"><i class="fas fa-exclamation-circle text-red-500"></i></template>
        <template x-if="toast.type === 'info'"><i class="fas fa-info-circle text-yellow-500"></i></template>
      </div>
      <p class="flex-1 font-medium" x-text="toast.message"></p>
      <button @click="remove(index)" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </template>
</div>

  <script>
  document.addEventListener('alpine:init', () => {
    // Refs store
    Alpine.store('refs', { grid: null, cart: null });

    // Cart store
    Alpine.store('cart', {
      items: JSON.parse(localStorage.getItem('cart') || '{}'),
      save() { localStorage.setItem('cart', JSON.stringify(this.items)); },
      add(product, variant = null) {
        const v = variant ?? { unit_type: product.unit, price: product.sale, unit_qty: 1, id: null };
        const key = `${product.id}-${v.unit_type ?? 'default'}`;
        if (this.items[key]) this.items[key].quantity++;
        else this.items[key] = {
          ...v,
          name: product.name,
          product_id: product.id,
          variant: v.unit_type ?? null,
          unit_qty: v.unit_qty,
          quantity: 1
        };
        this.save();
      },
      remove(key) { delete this.items[key]; this.save(); },
      clear() { this.items = {}; this.save(); },
      total() {
        return Object.values(this.items).reduce((sum, i) => sum + Number(i.price) * Number(i.quantity), 0);
      }
    });

    // Mode store (to toggle between product mode & cart mode)
    Alpine.store('mode', {
      current: 'products',
      toggle() {
        this.current = this.current === 'products' ? 'cart' : 'products';
        Alpine.store('toast').show(`Switched to ${this.current} mode`, "info");
      }
    });

    // CartSidebar component
    Alpine.data('CartSidebar', () => ({
      activeIndex: 0,
      get cart() { return Alpine.store('cart').items; },
      get keys() { return Object.keys(this.cart); },
      cartTotal() { return Alpine.store('cart').total(); },
      saveCart() { Alpine.store('cart').save(); },
      removeItem(key) { Alpine.store('cart').remove(key); },
      clearCart() {
        Alpine.store('cart').clear();
        Alpine.store('toast').show("Cart cleared", "info");
      },
      checkout(method) {
        if (Object.keys(this.cart).length === 0) {
          Alpine.store('toast').show("Cart is empty", "error", 4000);
          return;
        }
        const payload = {
          cart: Object.values(this.cart),
          paymentMethod: method,
          store_id: {{ auth()->user()->store_id }}
        };
        if (navigator.onLine) {
          fetch('{{ route("checkout.process") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
          })
          .then(res => res.json())
          .then(data => {
            Alpine.store('toast').show(data.message || "Order processed", "success");
            if (data.order_id) {
              Alpine.store('receiptModal').open(data.order_id);
            }
            Alpine.store('cart').clear();

            // Sync products inventory
            fetch('{{ route("products.sync") }}')
              .then(r => r.json())
              .then(fresh => {
                Alpine.store('refs').grid.products = fresh;
              })
              .catch(() => {
                Alpine.store('toast').show("Inventory sync failed", "error");
              });
          })
          .catch(() => Alpine.store('toast').show("Checkout failed", "error", 5000));
        } else {
          this.saveOffline(payload);
          Alpine.store('toast').show("You're offline. Order saved.", "info", 5000);
          Alpine.store('cart').clear();
        }
      },
      saveOffline(order) {
        const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
        offlineOrders.push(order);
        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
      },
      moveSelection(dir) {
        if (!this.keys.length) return;
        this.activeIndex = (this.activeIndex + dir + this.keys.length) % this.keys.length;
      },
      removeActive() {
        const key = this.keys[this.activeIndex];
        if (key) {
          this.removeItem(key);
          Alpine.store('toast').show("Item removed", "info");
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
      },
      // new function to truncate long names
      shortName(name) {
        const max = 24;
        if (name.length > max) {
          return name.slice(0, max - 1) + '…';
        }
        return name;
      }
    }));

    // ProductGrid component
    Alpine.data('ProductGrid', () => ({
      query: '',
      scanBuffer: '',
      products: @json($products),
      activeIndex: 0,
      get filtered() {
        const k = this.query.toLowerCase();
        return this.products.filter(p =>
          p.name.toLowerCase().includes(k) ||
          String(p.barcode).toLowerCase().includes(k)
        );
      },
      add(product) {
        if (product.variants?.length) Alpine.store('variantModal').open(product);
        else Alpine.store('cart').add(product);
      },
      getStock(product) {
        const userStoreId = {{ auth()->user()->store_id }};
        const storeInventory = product.store_inventories?.filter(inv => inv.store_id === userStoreId);
        return storeInventory?.reduce((sum, inv) => sum + Number(inv.quantity), 0) || 0;
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
      addByBarcode(code) {
        const product = this.products.find(p => String(p.barcode) === String(code));
        if (product) {
          this.add(product);
          Alpine.store('toast').show(`Scanned: ${product.name}`, "success");
        } else {
          Alpine.store('toast').show(`No product for barcode ${code}`, "error");
        }
      }
    }));

    // Variant Modal store
    Alpine.store('variantModal', {
      show: false,
      product: null,
      selected: 0,
      open(product) {
        this.product = product;
        this.show = true;
        this.selected = 0;
      },
      close() {
        this.show = false;
      },
      select(i) {
        this.selected = i;
      },
      add() {
        const v = this.product.variants[this.selected];
        Alpine.store('cart').add(this.product, v);
        Alpine.store('toast').show("Added to cart", "success");
        this.close();
      }
    });
    Alpine.data('VariantModal', () => Alpine.store('variantModal'));

    // Receipt Modal store
    Alpine.store('receiptModal', {
      show: false,
      url: '',
      open(orderId) {
        this.url = `/receipt/${orderId}`;
        this.show = true;
      },
      close() {
        this.show = false;
        this.url = '';
      }
    });
    Alpine.data('ReceiptModal', () => Alpine.store('receiptModal'));

    // Toast store
    Alpine.store('toast', {
      toasts: [],
      show(message, type = 'success', timeout = null) {
        const durations = { success: 3000, error: 6000, info: 5000 };
        const toast = { message, type, show: true };
        this.toasts.push(toast);
        setTimeout(() => this.remove(toast), timeout ?? durations[type] ?? 3000);
      },
      remove(toast) {
        toast.show = false;
        setTimeout(() => {
          this.toasts = this.toasts.filter(t => t !== toast);
        }, 300);
      }
    });
    Alpine.data('toastStore', () => Alpine.store('toast'));

    // Sync offline orders when going online
    window.addEventListener('online', () => {
      const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
      if (offlineOrders.length === 0) return;
      Alpine.store('toast').show("Syncing offline orders...", "info", 5000);
      fetch('{{ route("checkout.process") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ orders: offlineOrders })
      })
      .then(res => res.json())
      .then(() => {
        Alpine.store('toast').show("Offline orders synced successfully!", "success");
        localStorage.removeItem('offlineOrders');
      })
      .catch(() => Alpine.store('toast').show("Failed to sync offline orders.", "error"));
    });

    // Global Hotkeys
    document.addEventListener('keydown', (e) => {
      if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

      const mode = Alpine.store('mode').current;
      const grid = Alpine.store('refs').grid;
      const cart = Alpine.store('refs').cart;

      if (mode === 'products' && /^[0-9]$/.test(e.key)) {
        e.preventDefault();
        grid.scanBuffer += e.key;
        return;
      }
      if (mode === 'products' && e.key === 'Enter' && grid.scanBuffer) {
        e.preventDefault();
        grid.addByBarcode(grid.scanBuffer);
        grid.scanBuffer = '';
        return;
      }
      if (mode === 'products' && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && !/^[0-9]$/.test(e.key)) {
        e.preventDefault();
        grid.query += e.key;
        grid.activeIndex = 0;
        return;
      }
      if (mode === 'products' && e.key === 'Backspace') {
        e.preventDefault();
        if (grid.query.length > 0) {
          grid.query = grid.query.slice(0, -1);
          grid.activeIndex = 0;
        }
        return;
      }

      switch (e.key) {
        case 'Tab':
          e.preventDefault();
          Alpine.store('mode').toggle();
          break;

        case 'ArrowDown':
          e.preventDefault();
          if (mode === 'products') grid?.moveSelection(1);
          else cart?.moveSelection(1);
          break;

        case 'ArrowUp':
          e.preventDefault();
          if (mode === 'products') grid?.moveSelection(-1);
          else cart?.moveSelection(-1);
          break;

        case 'Enter':
          e.preventDefault();
          if (mode === 'products') grid?.addActive();
          else cart?.removeActive();
          break;

        case '+':
          if (mode === 'cart') { e.preventDefault(); cart?.increaseQty(); }
          break;

        case '-':
          if (mode === 'cart') { e.preventDefault(); cart?.decreaseQty(); }
          break;

        case 'Delete':
        case 'Backspace':
          if (mode === 'cart') { e.preventDefault(); cart?.removeActive(); }
          break;

        case 'c':
        case 'C':
          e.preventDefault();
          Alpine.store('cart').clear();
          Alpine.store('toast').show("Cart cleared (Hotkey)", "info");
          break;

        case '1':
          e.preventDefault();
          cart?.checkout('cash');
          break;
        case '2':
          e.preventDefault();
          cart?.checkout('bank');
          break;
        case '3':
          e.preventDefault();
          cart?.checkout('pos');
          break;

        case '/':
          e.preventDefault();
          const search = document.querySelector('[x-data="ProductGrid"] input[type="text"]');
          if (search) search.focus();
          break;
      }
    });
  });
  </script>

</body>
</html>
