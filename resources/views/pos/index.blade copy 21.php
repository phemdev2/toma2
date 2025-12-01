<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Modern POS System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['Inter','sans-serif'] } } }
    }
  </script>

  <!-- Fonts + Icons + Alpine -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <style>
    * { transition: all 0.15s ease; }
    .glass { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); }
    ::-webkit-scrollbar { width: 8px; } 
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  </style>
</head>

<body class="bg-gray-50 font-sans text-gray-800">

<!-- Status Bar -->
<div x-data="{ online: navigator.onLine }"
     x-init="window.addEventListener('online',()=>online=true);window.addEventListener('offline',()=>online=false)"
     class="fixed top-0 left-0 right-0 h-1 z-50"
     :class="online ? 'bg-green-500' : 'bg-red-500'"></div>

<div class="flex h-screen">

  <!-- Cart Sidebar -->
  <div x-data="CartSidebar" x-init="$store.refs.cart=$data"
       class="glass w-full md:w-1/3 border-r shadow-xl flex flex-col">

    <!-- Header -->
    <div class="p-4 bg-purple-600 text-white flex justify-between items-center">
      <h2 class="font-bold flex items-center gap-2"><i class="fas fa-shopping-basket"></i> Cart</h2>
      <button @click="clearCart()" x-show="Object.keys(cart).length>0"
              class="text-xs bg-white/20 px-3 py-1 rounded">Clear</button>
    </div>

    <!-- Items -->
    <div class="flex-1 overflow-y-auto p-3 space-y-3" data-cart-list>
      <template x-if="Object.keys(cart).length===0">
        <p class="text-gray-400 text-center">Cart is empty</p>
      </template>

      <template x-for="(item,key,index) in cart" :key="key">
        <div class="bg-white p-3 rounded shadow flex justify-between items-center"
             :class="index===activeIndex?'ring-2 ring-purple-500':''"
             @click="activeIndex=index">

          <div>
            <p class="font-semibold text-sm" x-text="item.name"></p>
            <p class="text-xs text-gray-500">
              <span x-text="item.variant || 'Unit'"></span> × 
              <span x-text="item.unit_qty"></span> = 
              ₦<span x-text="(item.price*item.quantity).toFixed(2)"></span>
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button @click.stop="if(item.quantity>1){item.quantity--;saveCart()}" class="px-2">-</button>
            <span x-text="item.quantity"></span>
            <button @click.stop="item.quantity++;saveCart()" class="px-2">+</button>
            <button @click.stop="removeItem(key)" class="text-red-500"><i class="fas fa-times"></i></button>
          </div>
        </div>
      </template>
    </div>

    <!-- Footer -->
    <div class="p-4 border-t bg-gray-50">
      <p class="font-bold mb-2">Total: ₦<span x-text="cartTotal().toFixed(2)"></span></p>
      <div class="grid grid-cols-3 gap-2">
        <button @click="checkout('cash')" class="bg-green-500 text-white py-2 rounded">Cash</button>
        <button @click="checkout('bank')" class="bg-blue-500 text-white py-2 rounded">Bank</button>
        <button @click="checkout('pos')" class="bg-purple-500 text-white py-2 rounded">POS</button>
      </div>
    </div>
  </div>

  <!-- Main (Products) -->
  <div x-data="ProductGrid" x-init="$store.refs.grid=$data"
       class="flex-1 flex flex-col">

    <!-- Navbar -->
    <nav class="glass border-b p-4 flex justify-between items-center">
      <span class="font-bold text-purple-600">ModernPOS</span>
      <button @click="$store.receiptModal.reprint()" class="text-gray-600"><i class="fas fa-print"></i></button>
    </nav>

    <!-- Search -->
    <div class="p-4">
      <input type="text" x-model="query" placeholder="Search or scan..."
             class="w-full border rounded px-3 py-2">
    </div>

    <!-- Grid -->
    <div class="flex-1 overflow-y-auto p-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
      <template x-for="(product,index) in filtered" :key="product.id">
        <div class="bg-white p-3 rounded shadow cursor-pointer hover:ring-2 hover:ring-purple-400"
             :class="index===activeIndex?'ring-2 ring-purple-500':''"
             @click="add(product)">
          <p class="font-semibold" x-text="product.name"></p>
          <p class="text-xs text-gray-500">₦<span x-text="product.sale"></span></p>
        </div>
      </template>
    </div>
  </div>
</div>

<!-- Variant Modal -->
<div x-data="VariantModal" x-show="show" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="display:none">
  <div class="bg-white p-4 rounded w-80">
    <h3 class="font-bold mb-2" x-text="product?.name"></h3>
    <template x-for="(v,i) in product?.variants" :key="i">
      <div class="p-2 border rounded mb-2 cursor-pointer"
           :class="i===selected?'border-purple-500':''"
           @click="select(i)">
        <span x-text="v.unit_type"></span> - ₦<span x-text="v.price"></span>
      </div>
    </template>
    <div class="flex justify-end gap-2 mt-3">
      <button @click="close()" class="px-3 py-1 border rounded">Cancel</button>
      <button @click="add()" class="px-3 py-1 bg-purple-500 text-white rounded">Add</button>
    </div>
  </div>
</div>

<!-- Receipt Modal -->
<div x-data="ReceiptModal" x-show="show" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="display:none">
  <div class="bg-white w-96 h-[80vh] rounded shadow flex flex-col">
    <div class="p-3 bg-purple-600 text-white flex justify-between">
      <span>Receipt</span>
      <button @click="close()">X</button>
    </div>
    <iframe x-ref="receiptFrame" :src="url" class="flex-1"></iframe>
    <div class="p-3">
      <button @click="reprint()" class="bg-purple-500 text-white px-3 py-1 rounded">Reprint</button>
    </div>
  </div>
</div>

<!-- Toasts -->
<div x-data="toastStore" class="fixed bottom-4 right-4 space-y-2"></div>


<script>
document.addEventListener('alpine:init', () => {
  // ----------------- Refs Store -----------------
  Alpine.store('refs', { grid: null, cart: null });

  // ----------------- Cart Store -----------------
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
        quantity: 1, 
        price: v.price 
      };
      this.save();
    },
    remove(key) { delete this.items[key]; this.save(); },
    clear() { this.items = {}; this.save(); },
    total() { return Object.values(this.items).reduce((s,i)=>s+Number(i.price)*Number(i.quantity),0); }
  });

  // ----------------- Mode Store -----------------
  Alpine.store('mode', {
    current: 'products',
    toggle() {
      this.current = this.current === 'products' ? 'cart' : 'products';
      Alpine.store('toast').show(`Switched to ${this.current} mode`, "info");
    }
  });

  // ----------------- Cart Sidebar -----------------
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
    id: 'offline-' + Date.now(), // unique ID for offline orders
    cart: Object.values(this.cart),
    paymentMethod: method,
    store_id: {{ auth()->user()->store_id }},
    created_at: new Date().toISOString(),
    status: 'pending'
  };

  if (navigator.onLine) {
    // ----// ---- ONLINE checkout ----
    // MOCKING FETCH for a standalone HTML file
    new Promise((resolve) => {
      setTimeout(() => resolve({ 
        json: () => Promise.resolve({ 
          message: 'Order processed successfully!', 
          order_id: 'WEB-123456-' + Date.now() // Mock Order ID
        })
      }), 500); // Simulate network delay
  })
.then(data => {
Alpine.store('toast').show(data.message || "Order processed", "success");
if (data.order_id) {
 // Mock receipt URL since the route doesn't exist outside Laravel
 const url = `data:text/html,<h1>Receipt for ${data.order_id}</h1><p>Total: ₦${this.cartTotal().toFixed(2)}</p><p>Payment: ${method.toUpperCase()}</p><hr><p>Items: ${Object.keys(this.cart).length}</p>`;
 Alpine.store('receiptModal').open(url);
 Alpine.store('receiptModal').lastOrderUrl = url;
}
 Alpine.store('cart').clear();
 })
 .catch(() => Alpine.store('toast').show("Checkout failed (MOCK)", "error", 5000));
  } else {
    // ---- OFFLINE checkout ----
    this.saveOffline(payload);
    Alpine.store('toast').show("Offline: order saved locally", "info", 5000);

   // 👉 Optional: open receipt modal in "Pending" mode
 const offlineReceiptUrl = `data:text/html,<h1>Offline Order Saved!</h1><p>Total: ₦${this.cartTotal().toFixed(2)}</p><p>Status: **PENDING SYNC**</p><hr><p>Thank you. Your order will sync when online.</p>`;
Alpine.store('receiptModal').open(offlineReceiptUrl);
Alpine.store('receiptModal').lastOrderUrl = offlineReceiptUrl;

    Alpine.store('cart').clear(); // ✅ allow new order immediately
  }
},

saveOffline(order) {
  const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
  offlineOrders.push(order);
  localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
},


    // Keyboard helpers
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
    }
  }));

  // ----------------- Product Grid -----------------
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

  // ----------------- Variant Modal -----------------
  Alpine.store('variantModal', {
    show: false, product: null, selected: 0,
    open(product) { this.product = product; this.show = true; this.selected = 0; },
    close() { this.show = false; },
    select(i) { this.selected = i; },
    add() { 
      const v = this.product.variants[this.selected];
      Alpine.store('cart').add(this.product, v);
      Alpine.store('toast').show("Added to cart", "success");
      this.close();
    }
  });
  Alpine.data('VariantModal', () => Alpine.store('variantModal'));

  // ----------------- Receipt Modal -----------------
  Alpine.store('receiptModal', {
    show: false,
    url: null,
    lastOrderUrl: null,

    open(url) {
      this.url = url;
      this.show = true;
    },
    reprint() {
      if (this.lastOrderUrl) {
        this.open(this.lastOrderUrl);
        Alpine.store('toast').show("Reprinting last receipt", "info");
      } else {
        Alpine.store('toast').show("No receipt available to reprint", "error");
      }
    },
    close() {
      this.show = false;
      this.url = null;
    }
  });
  Alpine.data('ReceiptModal', () => Alpine.store('receiptModal'));

  // ----------------- Toast Store -----------------
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
      setTimeout(() => { this.toasts = this.toasts.filter(t => t !== toast); }, 300);
    }
  });
  Alpine.data('toastStore', () => Alpine.store('toast'));

  // ----------------- Sync offline orders -----------------
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

  // ----------------- Global Hotkeys -----------------
  document.addEventListener('keydown', (e) => {
    if (Alpine.store('receiptModal').show) {
      switch (e.key) {
        case 'Escape':
          e.preventDefault();
          Alpine.store('receiptModal').close();
          break;
        case 'Enter':
          e.preventDefault();
          const iframe = document.querySelector('[x-ref="receiptFrame"]');
          if (iframe && iframe.contentWindow) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
          }
          break;
        default:
          return;
      }
      return;
    }

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
        e.preventDefault(); cart?.checkout('cash'); break;
      case '2':
        e.preventDefault(); cart?.checkout('bank'); break;
      case '3':
        e.preventDefault(); cart?.checkout('pos'); break;
      case '/':
        e.preventDefault();
        const search = document.querySelector('[x-data="ProductGrid"] input[type="text"]');
        if (search) search.focus();
        break;
      case 'p':
      case 'P':
        e.preventDefault();
        Alpine.store('receiptModal').reprint();
        break;
    }
  });
});
</script>

</body>
</html>
