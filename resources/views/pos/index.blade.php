<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
  <title>POS - {{ auth()->user()->store->name ?? 'Store' }}</title>
  <meta name="description" content="Offline POS" />
  <link rel="icon" href="/favicon.ico" sizes="any" />

  <!-- CSRF TOKEN -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          animation: { 'fade-in': 'fadeIn 0.2s ease-out', 'pulse-fast': 'pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite' },
          keyframes: { fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } } }
        }
      }
    }
  </script>

  <!-- Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

  <!-- SERVICE WORKER REGISTRATION (Robust) -->
  <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('✅ SW Registered!', reg.scope))
            .catch(err => console.error('❌ SW Failed:', err));
    }
  </script>

  <style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    [x-cloak] { display: none !important; }
    .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  </style>
</head>

{{-- DATA OPTIMIZATION --}}
@php
  $user = auth()->user();
  $store = $user->store ?? null;
  $storeId = $store ? $store->id : 0;
  $storeName = $store ? $store->name : 'My Store';
  $storeAddress = $store ? $store->address : 'Location N/A';
  
  $products = $products ?? collect();

  $preparedProducts = $products->map(function ($product) use ($storeId) {
    $stock = $product->relationLoaded('storeInventories') 
        ? (int)$product->storeInventories->where('store_id', $storeId)->sum('quantity') 
        : 0;

    $variants = collect($product->variants ?? [])->map(function ($v, $k) {
      return [
        'id' => $v['id'] ?? $k,
        'n'  => $v['unit_type'] ?? 'Var', 
        'q'  => $v['unit_qty'] ?? 1,
        'p'  => (float) ($v['price'] ?? 0)
      ];
    })->values()->all();

    return [
      'id' => $product->id,
      'n'  => $product->name,
      'b'  => $product->barcode,
      'p'  => (float) $product->sale,
      's'  => $stock,
      'v'  => $variants
    ];
  })->values()->all();
@endphp

<body class="bg-slate-100 font-sans text-gray-800 h-screen overflow-hidden flex flex-col">

  <!-- TOP NAVIGATION -->
  <nav class="bg-slate-900 text-white h-14 flex-none z-50 shadow-md flex justify-between items-center px-4">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded bg-purple-600 flex items-center justify-center font-bold text-lg">P</div>
      <span class="font-bold text-lg hidden md:block">POS System</span>
    </div>

    <div class="text-center leading-tight">
      <div class="font-bold text-sm">{{ $storeName }}</div>
      <div class="text-[10px] text-gray-400"><i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($storeAddress, 40) }}</div>
    </div>

    <div class="flex items-center gap-4 text-sm" x-data="SyncManager">
      
      <!-- PENDING SYNC (Hidden if empty) -->
      <div x-show="queueCount > 0" x-cloak 
           class="flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-500 text-black font-bold text-xs animate-pulse-fast cursor-pointer"
           @click="forceSync()">
         <i class="fas fa-sync" :class="syncing ? 'fa-spin' : ''"></i>
         <span x-text="queueCount + ' Offline Orders'"></span>
      </div>

      <!-- NETWORK INDICATOR -->
      <div class="flex items-center gap-2 px-3 py-1 rounded-full transition-colors duration-300 font-bold text-xs"
           :class="online ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-600 text-white shadow-lg animate-pulse'">
         <div class="w-2 h-2 rounded-full" :class="online ? 'bg-emerald-400' : 'bg-white'"></div>
         <span x-text="online ? 'Online' : 'Offline'"></span>
      </div>

      <div class="hidden md:flex items-center gap-2 border-l border-gray-700 pl-4">
        <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold">{{ substr($user->name, 0, 1) }}</div>
        <span>{{ $user->name }}</span>
      </div>
    </div>
  </nav>

  <!-- MAIN LAYOUT -->
  <div class="flex-1 flex overflow-hidden">

    <!-- LEFT: Cart Sidebar -->
    <div x-data="CartSidebar" class="bg-white border-r border-gray-200 shadow-xl z-40 w-full lg:w-[450px] flex flex-col h-full relative">
      
      <!-- TABS -->
      <div class="flex items-center bg-gray-100 border-b border-gray-200 overflow-x-auto custom-scrollbar">
        <template x-for="(session, id) in sessions" :key="id">
            <div @click="switchTab(id)" 
                 class="group relative min-w-[100px] max-w-[140px] px-3 py-2 text-xs font-bold cursor-pointer border-r border-gray-200 select-none flex items-center justify-between transition-colors"
                 :class="activeTab === id ? 'bg-white text-purple-700 border-t-2 border-t-purple-600' : 'text-gray-500 hover:bg-gray-200'">
                <span class="truncate mr-2" x-text="`Order ${session.number}`"></span>
                <button x-show="Object.keys(sessions).length > 1" @click.stop="closeTab(id)" class="text-gray-300 hover:text-red-500"><i class="fas fa-times"></i></button>
            </div>
        </template>
        <button @click="createTab()" class="px-3 py-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors" title="New Order Tab"><i class="fas fa-plus"></i></button>
      </div>

      <!-- Header -->
      <div class="p-3 bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-sm z-10">
        <div class="flex justify-between items-center mb-1">
          <h2 class="font-bold flex items-center gap-2 text-sm">
            <i class="fas fa-shopping-basket"></i> 
            <span x-text="`Order #${sessions[activeTab].number}`"></span>
            <span x-show="count > 0" class="bg-white/20 text-[10px] px-2 py-0.5 rounded-full" x-text="count + ' items'"></span>
          </h2>
          <button @click="clear()" x-show="count > 0" class="text-[10px] bg-red-500/20 hover:bg-red-500/40 px-2 py-1 rounded border border-red-400/30">Clear</button>
        </div>
      </div>

      <!-- Items -->
      <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar bg-slate-50" id="cart-container">
        <template x-if="count === 0">
          <div class="h-full flex flex-col items-center justify-center text-gray-400">
            <i class="fas fa-cart-plus text-4xl opacity-20 mb-2"></i>
            <p class="text-xs">Scan item or select from grid</p>
          </div>
        </template>
        <template x-for="(item, key) in currentItems" :key="key">
          <div class="bg-white p-2 rounded border border-gray-200 shadow-sm relative animate-fade-in group">
            <div class="flex justify-between items-start gap-2">
              <div class="flex-1 min-w-0">
                <div class="font-bold text-sm text-gray-800 truncate" x-text="item.n"></div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                   <span x-show="item.v_name" class="bg-indigo-50 text-indigo-600 px-1 rounded text-[10px]" x-text="item.v_name"></span>
                   <span>@ ₦<span x-text="format(item.p)"></span></span>
                </div>
              </div>
              <div class="font-bold text-purple-700">₦<span x-text="format(item.p * item.qty)"></span></div>
            </div>
            <div class="flex justify-between items-center mt-2 pt-2 border-t border-dashed border-gray-100">
               <button @click="mod(key, -1)" class="w-6 h-6 flex items-center justify-center text-red-500 bg-red-50 rounded hover:bg-red-100"><i class="fas fa-minus text-[10px]"></i></button>
               <input type="number" x-model.number="item.qty" @change="check(key)" class="w-12 h-8 text-center bg-transparent text-sm font-bold focus:outline-none" />
               <button @click="mod(key, 1)" class="w-6 h-6 flex items-center justify-center text-green-600 bg-green-50 rounded hover:bg-green-100"><i class="fas fa-plus text-[10px]"></i></button>
               <button @click="remove(key)" class="ml-auto text-gray-300 hover:text-red-500"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>
        </template>
      </div>

      <!-- Footer -->
      <div class="p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-20">
        <div class="flex justify-between items-end mb-4">
          <span class="text-gray-500 text-sm font-medium">Total Payable</span>
          <span class="text-3xl font-extrabold text-gray-800">₦<span x-text="total"></span></span>
        </div>
        <div class="grid grid-cols-3 gap-2">
          <button @click="pay('cash')" :disabled="count === 0 || loading" class="flex flex-col items-center justify-center py-3 px-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white rounded-lg shadow active:scale-95"><span class="text-xs font-bold uppercase">Cash</span></button>
          <button @click="pay('pos')" :disabled="count === 0 || loading" class="flex flex-col items-center justify-center py-3 px-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg shadow active:scale-95"><span class="text-xs font-bold uppercase">POS</span></button>
          <button @click="pay('bank_transfer')" :disabled="count === 0 || loading" class="flex flex-col items-center justify-center py-3 px-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white rounded-lg shadow active:scale-95"><span class="text-xs font-bold uppercase">Transfer</span></button>
        </div>
      </div>
    </div>

    <!-- RIGHT: Product Grid -->
    <div x-data="ProductGrid" class="flex-1 flex flex-col h-full bg-slate-100 overflow-hidden relative">
      <div class="bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center shadow-sm z-30">
        <div class="relative w-full max-w-xl">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
          <input type="text" x-model="search" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Search (F2)..." @keydown.window.f2.prevent="$el.focus()" autofocus @keydown.enter="enterSearch()"/>
          <button x-show="search" @click="search=''" class="absolute right-3 top-3 text-gray-400"><i class="fas fa-times-circle"></i></button>
        </div>
        <div class="flex items-center gap-3 ml-4">
           <div class="hidden md:flex flex-col items-end mr-2"><span class="text-xs text-gray-500"><span x-text="filteredCount"></span> items</span></div>
           <button @click="$dispatch('open-receipt-modal', {url: lastReceiptUrl})" :disabled="!lastReceiptUrl" class="p-2.5 bg-white border border-gray-300 rounded-lg hover:text-purple-600 disabled:opacity-50"><i class="fas fa-print"></i></button>
        </div>
      </div>

      <div class="flex-1 p-6 overflow-y-auto custom-scrollbar" id="product-scroll-area">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 pb-20">
          <template x-for="p in visibleProducts" :key="p.id">
            <button @click="click(p)" class="group bg-white rounded-xl border border-gray-200 p-4 flex flex-col justify-between hover:border-purple-400 hover:shadow-lg hover:-translate-y-1 transition-all h-full relative">
              <div class="absolute top-0 right-0"><span x-show="p.s <= 5" class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded-bl-lg">Low</span></div>
              <div>
                <h3 class="font-semibold text-gray-800 text-sm leading-tight mb-1 line-clamp-2 h-9" x-text="p.n"></h3>
                <p class="text-xs text-gray-400 font-mono mb-2" x-text="p.b || ''"></p>
                <div class="flex flex-wrap gap-1 mb-2">
                  <span x-show="p.s > 0" class="text-[10px] px-1.5 py-0.5 rounded bg-green-50 text-green-700 font-medium">Stock: <span x-text="p.s"></span></span>
                  <span x-show="p.s <= 0" class="text-[10px] px-1.5 py-0.5 rounded bg-red-50 text-red-700 font-medium">Out</span>
                  <span x-show="p.v.length" class="text-[10px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-medium"><i class="fas fa-layer-group mr-1"></i>Var</span>
                </div>
              </div>
              <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between items-center w-full">
                <span class="font-bold text-lg text-gray-800">₦<span x-text="format(p.p)"></span></span>
                <div class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center transition-colors"><i class="fas fa-plus text-xs"></i></div>
              </div>
            </button>
          </template>
        </div>
        <div x-show="hasMore" class="py-6 text-center"><button @click="loadMore()" class="px-6 py-2 bg-white border border-gray-300 text-gray-600 rounded-full shadow-sm hover:bg-gray-50 text-sm font-medium">Load More</button></div>
      </div>
    </div>
  </div>

  <!-- MODALS -->
  <div x-data="VariantModal" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden" @click.away="show=false">
      <div class="p-3 bg-gray-50 border-b flex justify-between items-center">
        <h3 class="font-bold text-gray-800 truncate" x-text="p?.n"></h3>
        <button @click="show=false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
      </div>
      <div class="p-3 space-y-2 max-h-[50vh] overflow-y-auto">
        <template x-for="(v, idx) in p?.v" :key="idx">
          <button @click="sel(v)" class="w-full flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 text-left">
            <div><div class="font-bold text-sm text-gray-800" x-text="v.n"></div><div class="text-xs text-gray-500">Size: <span x-text="v.q"></span></div></div>
            <div class="font-bold text-purple-600">₦<span x-text="format(v.p)"></span></div>
          </button>
        </template>
      </div>
    </div>
  </div>

  <div x-data="ReceiptModal" x-show="show" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg h-[85vh] flex flex-col overflow-hidden" @click.away="close()">
      <div class="flex justify-between items-center p-3 border-b bg-gray-50">
        <h3 class="font-bold text-gray-700 flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> Sale Complete</h3>
        <button @click="close()" class="w-8 h-8 flex items-center justify-center hover:bg-gray-200 rounded-full text-gray-500"><i class="fas fa-times"></i></button>
      </div>
      <div class="flex-1 bg-gray-100 relative">
        <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center z-10 bg-white">
            <i class="fas fa-circle-notch animate-spin text-4xl text-purple-600 mb-3"></i><p class="text-sm text-gray-500 font-medium">Loading...</p>
        </div>
        <iframe x-ref="receiptFrame" class="w-full h-full border-0 bg-white" @load="stopLoading()"></iframe>
      </div>
      <div class="p-4 bg-white border-t border-gray-200 grid grid-cols-2 gap-3">
        <button @click="print()" :disabled="loading" class="flex items-center justify-center py-3 rounded-lg bg-gray-100 text-gray-800 font-bold hover:bg-gray-200 border border-gray-300 transition-all disabled:opacity-50"><i class="fas fa-print mr-2"></i> Print</button>
        <button @click="newOrder()" class="flex items-center justify-center py-3 rounded-lg bg-purple-600 text-white font-bold hover:bg-purple-700 shadow-md transition-all active:scale-95"><i class="fas fa-plus-circle mr-2"></i> New Order</button>
      </div>
    </div>
  </div>

  <script>
    // --- 1. CONFIGURATION ---
    const rawData = @json($preparedProducts);
    const STORE_ID = "{{ $storeId }}";
    const BASE_URL = "{{ url('/') }}";
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    const formatter = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // --- 2. DATA NORMALIZATION ---
    const DB = rawData.map(p => ({
        id: p.id,
        n: p.n || p.name || 'Unknown',
        b: p.b || p.barcode || '',
        p: parseFloat(p.p || p.sale || 0),
        s: parseInt(p.s || p.stock || 0),
        v: (p.v || p.variants || []).map((v, i) => ({
            id: v.id ?? `v_${p.id}_${i}`,
            n: v.n || v.variant_name || v.unit_type || 'Option',
            p: parseFloat(v.p || v.price || 0),
            q: v.q || v.unit_qty || 1
        }))
    }));

    // --- 3. ALPINE INITIALIZATION ---
    document.addEventListener('alpine:init', () => {

        // --- TOAST NOTIFICATION LOGIC ---
        Alpine.data('ToastHandler', () => ({
            notifications: [],
            init() {
                window.addEventListener('notify', (e) => {
                    const id = Date.now();
                    this.notifications.push({
                        id: id,
                        message: e.detail.message,
                        type: e.detail.type || 'info', // success, error, warning, info
                        show: true
                    });
                    // Auto dismiss after 4 seconds
                    setTimeout(() => { this.remove(id) }, 4000);
                });
            },
            remove(id) {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }
        }));

        // --- MULTI-TAB CART LOGIC ---
        Alpine.data('CartSidebar', () => ({
            sessions: {}, activeTab: 'tab_1', loading: false,
            
            init() {
                const saved = localStorage.getItem('pos_sessions');
                if (saved) { 
                    this.sessions = JSON.parse(saved); 
                    const keys = Object.keys(this.sessions);
                    if(keys.length) this.activeTab = keys[keys.length-1]; 
                    else this.createTab();
                } else {
                    this.createTab();
                }
                window.addEventListener('add', e => this.add(e.detail.p, e.detail.v));
            },

            createTab() { 
                const id = 'tab_'+Date.now(); 
                this.sessions[id] = { number: Object.keys(this.sessions).length + 1, items: {} }; 
                this.activeTab = id; 
                this.save(); 
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'New order tab opened', type: 'info' } }));
            },

            switchTab(id) { this.activeTab = id; },

            closeTab(id) { 
                if(Object.keys(this.sessions).length <= 1) { this.clear(); return; }
                delete this.sessions[id];
                this.activeTab = Object.keys(this.sessions)[0];
                this.save();
            },

            get currentItems() { return this.sessions[this.activeTab]?.items || {}; },
            get count() { return Object.keys(this.currentItems).length; },
            get total() { 
                return formatter.format(Object.values(this.currentItems).reduce((sum, i) => sum + (i.p * i.qty), 0)); 
            },

            format(n) { return formatter.format(n); },

            add(p, v = null) {
                const vid = v ? v.id : 'base';
                const key = `${p.id}_${vid}`;
                const items = this.sessions[this.activeTab].items;

                if (items[key]) {
                    items[key].qty++;
                } else {
                    items[key] = {
                        id: p.id,
                        n: p.n,
                        p: v ? v.p : p.p,
                        v_name: v ? v.n : null,
                        vid: v ? v.id : null,
                        qty: 1
                    };
                }
                this.save();
                this.scrollToBottom();
                this.playBeep();
                // Optional: Notify on add
                // window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Item added', type: 'success' } }));
            },

            remove(k) { delete this.sessions[this.activeTab].items[k]; this.save(); },
            mod(k, n) { const i = this.sessions[this.activeTab].items; if(i[k]){ i[k].qty += n; if(i[k].qty < 1) { if(confirm('Remove?')) delete i[k]; else i[k].qty = 1; } this.save(); } },
            check(k) { const i = this.sessions[this.activeTab].items; if(i[k] && i[k].qty < 1) i[k].qty = 1; this.save(); },
            clear() { if(confirm('Clear cart?')) { this.sessions[this.activeTab].items = {}; this.save(); } },
            save() { localStorage.setItem('pos_sessions', JSON.stringify(this.sessions)); },
            scrollToBottom() { this.$nextTick(() => { const el = document.getElementById('cart-container'); if(el) el.scrollTop = el.scrollHeight; }); },
            playBeep() { try { const ctx = new (window.AudioContext || window.webkitAudioContext)(); const osc = ctx.createOscillator(); const gain = ctx.createGain(); osc.connect(gain); gain.connect(ctx.destination); osc.frequency.value = 800; gain.gain.value = 0.05; osc.start(); setTimeout(() => osc.stop(), 80); } catch(e) {} },

            async pay(method) {
                if(this.count === 0) return;
                this.loading = true;
                if(method === 'transfer') method = 'bank_transfer';
                
                const payload = { 
                    store_id: STORE_ID, 
                    paymentMethod: method, 
                    cart: Object.values(this.currentItems).map(i => ({
                        product_id: i.id,
                        variant_id: i.vid,
                        quantity: i.qty,
                        price: i.p
                    })) 
                };

                // OFFLINE LOGIC
                if(!navigator.onLine) {
                    window.dispatchEvent(new CustomEvent('queue-order', { detail: payload }));
                    this.sessions[this.activeTab].items = {}; 
                    this.save();
                    this.loading = false;
                    // REPLACED ALERT WITH NOTIFY
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '⚠️ Offline: Order saved to queue', type: 'warning' } }));
                    return;
                }

                try {
                    const res = await fetch('{{ route("checkout.process") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    
                    if(!res.ok) throw new Error(data.message || 'Transaction Failed');
                    
                    this.sessions[this.activeTab].items = {}; 
                    this.save();
                    
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '✅ Transaction Successful', type: 'success' } }));
                    window.dispatchEvent(new CustomEvent('open-receipt-modal', { detail: { url: `/receipt/${data.order_id}` } }));
                } catch(e) {
                    // Fallback to queue if network fails mid-request
                    window.dispatchEvent(new CustomEvent('queue-order', { detail: payload }));
                    this.sessions[this.activeTab].items = {}; 
                    this.save();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '⚠️ Connection lost. Order queued.', type: 'error' } }));
                } finally { 
                    this.loading = false; 
                }
            }
        }));

        // --- PRODUCT GRID ---
        Alpine.data('ProductGrid', () => ({
            all: DB, search: '', displayLimit: 24,
            get filtered() { const s = this.search.toLowerCase(); return !s ? this.all : this.all.filter(x => x.n.toLowerCase().includes(s) || (x.b && x.b.includes(s))); },
            get visibleProducts() { return this.filtered.slice(0, this.displayLimit); },
            get filteredCount() { return this.filtered.length; },
            get hasMore() { return this.displayLimit < this.filtered.length; },
            init() { this.$watch('search', () => { this.displayLimit = 24; document.getElementById('product-scroll-area').scrollTop = 0; }); },
            loadMore() { this.displayLimit += 24; },
            enterSearch() { const exact = this.filtered.find(p => p.b === this.search); if(exact) { this.click(exact); this.search = ''; } },
            format(n) { return formatter.format(n); },
            click(p) { if(p.v.length > 0) window.dispatchEvent(new CustomEvent('var', { detail: p })); else window.dispatchEvent(new CustomEvent('add', { detail: { p } })); }
        }));

        // --- RECEIPT MODAL ---
        Alpine.data('ReceiptModal', () => ({
            show: false, loading: true,
            init() { window.addEventListener('open-receipt-modal', e => this.fetchReceipt(e.detail.url)); },
            async fetchReceipt(url) {
                this.show = true; this.loading = true;
                try { 
                    const r = await fetch(url); 
                    let h = await r.text(); 
                    if(!h.includes('<base')) h = h.replace('<head>', `<head><base href="${BASE_URL}/">`); 
                    this.$refs.receiptFrame.srcdoc = h; 
                    setTimeout(() => this.loading = false, 500); 
                } catch(e) { 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Failed to load receipt', type: 'error' } }));
                    this.show = false; 
                }
            },
            stopLoading() { this.loading = false; },
            close() { this.show = false; this.$refs.receiptFrame.srcdoc = ''; },
            newOrder() { this.close(); },
            print() { const f = this.$refs.receiptFrame; if(f && f.contentWindow) { f.contentWindow.focus(); f.contentWindow.print(); } }
        }));

        // --- VARIANT MODAL ---
        Alpine.data('VariantModal', () => ({
            show: false, p: null,
            init() { window.addEventListener('var', e => { this.p = e.detail; this.show = true; }); },
            close() { this.show = false; },
            sel(v) { window.dispatchEvent(new CustomEvent('add', { detail: { p: this.p, v: v } })); this.close(); },
            format(n) { return formatter.format(n); }
        }));

        // --- SYNC MANAGER (Updated with Notify) ---
        Alpine.data('SyncManager', () => ({
            online: navigator.onLine, queue: [], syncing: false,
            init() {
                this.queue = JSON.parse(localStorage.getItem('pos_queue') || '[]');
                window.addEventListener('online', () => { 
                    this.online = true; 
                    this.processQueue(); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '🌐 Online: Syncing data...', type: 'info' } }));
                });
                window.addEventListener('offline', () => {
                    this.online = false;
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '📡 You are now Offline', type: 'warning' } }));
                });
                window.addEventListener('queue-order', e => {
                    this.queue.push(e.detail); localStorage.setItem('pos_queue', JSON.stringify(this.queue));
                });
                setInterval(() => { if (this.online && this.queue.length > 0 && !this.syncing) this.processQueue(); }, 5000);
            },
            get queueCount() { return this.queue.length; },
            forceSync() { if(!this.online) window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Still offline', type: 'warning' } })); else this.processQueue(); },
            async processQueue() {
                if (this.queue.length === 0 || this.syncing || !this.online) return;
                this.syncing = true;
                const order = this.queue[0];
                try {
                    const res = await fetch('{{ route("checkout.process") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                        body: JSON.stringify(order)
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.queue.shift(); localStorage.setItem('pos_queue', JSON.stringify(this.queue));
                        
                        // NOTIFY SUCCESS
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: `☁️ Synced Order #${data.order_id}`, type: 'success' } }));

                        if(this.queue.length > 0) { this.syncing = false; this.processQueue(); return; }
                    } else {
                        console.log('Sync error, will retry');
                    }
                } catch(e){} finally { this.syncing = false; }
            }
        }));
    });
</script>
</body>
</html>