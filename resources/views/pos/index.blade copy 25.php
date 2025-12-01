<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
  <title>POS - {{ auth()->user()->store->name ?? 'Store' }}</title>
  <meta name="description" content="High Performance POS System" />
  <link rel="icon" href="/favicon.ico" sizes="any" />

  <!-- CSRF TOKEN -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <!-- Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <!-- Tailwind CSS v3 -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          animation: { 'fade-in': 'fadeIn 0.2s ease-out', 'slide-up': 'slideUp 0.3s ease-out' },
          keyframes: { 
            fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
            slideUp: { '0%': { transform: 'translateY(10px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } }
          }
        }
      }
    }
  </script>

  <!-- Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

  <style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    /* Utility */
    [x-cloak] { display: none !important; }
    .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    
    /* Hide number arrows */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  </style>
</head>

{{-- 
    DATA OPTIMIZATION SECTION 
    We use short keys to reduce JSON payload size:
    n=name, b=barcode, p=price, s=stock, v=variants
--}}
@php
  $user = auth()->user();
  $store = $user->store ?? null;
  $storeId = $store ? $store->id : 0;
  $storeName = $store ? $store->name : 'My Store';
  $storeAddress = $store ? $store->address : 'Location N/A';
  
  $products = $products ?? collect();

  $preparedProducts = $products->map(function ($product) use ($storeId) {
    // Calculate stock safely
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

  <!-- 1. TOP NAVIGATION BAR -->
  <nav class="bg-slate-900 text-white h-14 flex-none z-50 shadow-md flex justify-between items-center px-4">
    <!-- Left: Brand -->
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded bg-purple-600 flex items-center justify-center font-bold text-lg">P</div>
      <span class="font-bold text-lg hidden md:block">POS System</span>
    </div>

    <!-- Center: Store Info -->
    <div class="text-center leading-tight">
      <div class="font-bold text-sm">{{ $storeName }}</div>
      <div class="text-[10px] text-gray-400"><i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($storeAddress, 40) }}</div>
    </div>

    <!-- Right: User & Status -->
    <div class="flex items-center gap-4 text-sm">
      
      <!-- UPDATED NETWORK STATUS INDICATOR -->
      <div x-data="{ online: navigator.onLine }" 
           x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false);"
           class="flex items-center gap-2 px-3 py-1 rounded-full transition-colors duration-300 font-bold text-xs"
           :class="online ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-600 text-white shadow-lg animate-pulse'">
         
         <!-- The Dot -->
         <div class="w-2 h-2 rounded-full" 
              :class="online ? 'bg-emerald-400' : 'bg-white'"></div>
         
         <!-- The Text -->
         <span x-text="online ? 'Online' : 'Offline'"></span>
      </div>

      <div class="hidden md:flex items-center gap-2 border-l border-gray-700 pl-4">
        <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold">
            {{ substr($user->name, 0, 1) }}
        </div>
        <span>{{ $user->name }}</span>
      </div>
    </div>
  </nav>

  <!-- 2. MAIN LAYOUT -->
  <div class="flex-1 flex overflow-hidden">

    <!-- LEFT: Cart Sidebar -->
    <div x-data="CartSidebar" class="bg-white border-r border-gray-200 shadow-xl z-40 w-full lg:w-[450px] flex flex-col h-full relative">
      
      <!-- TABS BAR -->
      <div class="flex items-center bg-gray-100 border-b border-gray-200 overflow-x-auto custom-scrollbar">
        <template x-for="(session, id) in sessions" :key="id">
            <div @click="switchTab(id)" 
                 class="group relative min-w-[100px] max-w-[140px] px-3 py-2 text-xs font-bold cursor-pointer border-r border-gray-200 select-none flex items-center justify-between transition-colors"
                 :class="activeTab === id ? 'bg-white text-purple-700 border-t-2 border-t-purple-600' : 'text-gray-500 hover:bg-gray-200'">
                 
                <span class="truncate mr-2" x-text="`Order ${session.number}`"></span>
                
                <!-- Close Tab Button -->
                <button x-show="Object.keys(sessions).length > 1" 
                        @click.stop="closeTab(id)"
                        class="text-gray-300 hover:text-red-500 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </template>
        
        <!-- Add New Tab Button -->
        <button @click="createTab()" class="px-3 py-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors" title="New Order Tab">
            <i class="fas fa-plus"></i>
        </button>
      </div>

      <!-- Cart Header -->
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

      <!-- Cart Items List -->
      <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar bg-slate-50" id="cart-container">
        
        <!-- Empty State -->
        <template x-if="count === 0">
          <div class="h-full flex flex-col items-center justify-center text-gray-400">
            <i class="fas fa-cart-plus text-4xl opacity-20 mb-2"></i>
            <p class="text-xs">Scan item or select from grid</p>
          </div>
        </template>

        <!-- Cart Item Loop -->
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

            <!-- Qty Controls -->
            <div class="flex justify-between items-center mt-2 pt-2 border-t border-dashed border-gray-100">
               <button @click="mod(key, -1)" class="w-6 h-6 flex items-center justify-center text-red-500 bg-red-50 rounded hover:bg-red-100"><i class="fas fa-minus text-[10px]"></i></button>
               <input type="number" x-model.number="item.qty" @change="check(key)" 
                      class="w-12 h-8 text-center bg-transparent text-sm font-bold focus:outline-none focus:bg-white transition-colors" />
               <button @click="mod(key, 1)" class="w-6 h-6 flex items-center justify-center text-green-600 bg-green-50 rounded hover:bg-green-100"><i class="fas fa-plus text-[10px]"></i></button>
               <button @click="remove(key)" class="ml-auto text-gray-300 hover:text-red-500"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>
        </template>
      </div>

      <!-- Cart Footer -->
      <div class="p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-20">
        <div class="flex justify-between items-end mb-4">
          <span class="text-gray-500 text-sm font-medium">Total Payable</span>
          <span class="text-3xl font-extrabold text-gray-800">₦<span x-text="total"></span></span>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <button @click="pay('cash')" :disabled="count === 0 || loading"
            class="flex flex-col items-center justify-center py-3 px-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg shadow transition-all active:scale-95">
            <i x-show="!loading" class="fas fa-money-bill-wave mb-1 text-lg"></i>
            <i x-show="loading" class="fas fa-circle-notch animate-spin mb-1 text-lg"></i>
            <span class="text-xs font-bold uppercase">Cash</span>
          </button>

          <button @click="pay('pos')" :disabled="count === 0 || loading"
            class="flex flex-col items-center justify-center py-3 px-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg shadow transition-all active:scale-95">
            <i x-show="!loading" class="fas fa-credit-card mb-1 text-lg"></i>
            <i x-show="loading" class="fas fa-circle-notch animate-spin mb-1 text-lg"></i>
            <span class="text-xs font-bold uppercase">POS</span>
          </button>
          
          <button @click="pay('bank_transfer')" :disabled="count === 0 || loading"
            class="flex flex-col items-center justify-center py-3 px-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg shadow transition-all active:scale-95">
            <i x-show="!loading" class="fas fa-university mb-1 text-lg"></i>
            <i x-show="loading" class="fas fa-circle-notch animate-spin mb-1 text-lg"></i>
            <span class="text-xs font-bold uppercase">Transfer</span>
          </button>
        </div>
      </div>
    </div>

    <!-- RIGHT: Product Grid (Lazy Loading) -->
    <div x-data="ProductGrid" class="flex-1 flex flex-col h-full bg-slate-100 overflow-hidden relative">
      
      <!-- Top Bar -->
      <div class="bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center shadow-sm z-30">
        <!-- Search -->
        <div class="relative w-full max-w-xl">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" x-model="search" 
                 class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition-shadow shadow-sm"
                 placeholder="Search products or scan barcode (F2)..." 
                 @keydown.window.f2.prevent="$el.focus()" autofocus 
                 @keydown.enter="enterSearch()"/>
          <button x-show="search.length > 0" @click="search = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-gray-400 hover:text-gray-600">
            <i class="fas fa-times-circle"></i>
          </button>
        </div>

        <!-- Info & Actions -->
        <div class="flex items-center gap-3 ml-4">
           <div class="hidden md:flex flex-col items-end mr-2">
             <span class="text-xs text-gray-500"><span x-text="filteredCount"></span> items found</span>
           </div>
           
           <!-- Reprint Button -->
           <button @click="$dispatch('open-receipt-modal', {url: lastReceiptUrl})" 
                   :disabled="!lastReceiptUrl"
                   class="p-2.5 bg-white border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-purple-600 disabled:opacity-50 transition-colors shadow-sm" 
                   title="Reprint Last Receipt">
             <i class="fas fa-print"></i>
           </button>
        </div>
      </div>

      <!-- Grid Content -->
      <div class="flex-1 p-6 overflow-y-auto custom-scrollbar" id="product-scroll-area">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 pb-20">
          
          <template x-for="p in visibleProducts" :key="p.id">
            <button @click="click(p)" 
              class="group bg-white rounded-xl border border-gray-200 p-4 flex flex-col justify-between hover:border-purple-400 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 text-left relative overflow-hidden h-full">
              
              <div class="absolute top-0 right-0 p-0">
                <span x-show="p.s <= 5" class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded-bl-lg">Low</span>
              </div>

              <div>
                <h3 class="font-semibold text-gray-800 text-sm leading-tight mb-1 line-clamp-2 h-[2.5em]" x-text="p.n"></h3>
                <p class="text-xs text-gray-400 font-mono mb-2" x-text="p.b || ''"></p>
                
                <div class="flex flex-wrap gap-1 mb-2">
                  <span x-show="p.s > 0" class="text-[10px] px-1.5 py-0.5 rounded bg-green-50 text-green-700 font-medium">Stock: <span x-text="p.s"></span></span>
                  <span x-show="p.s <= 0" class="text-[10px] px-1.5 py-0.5 rounded bg-red-50 text-red-700 font-medium">Out Stock</span>
                  <span x-show="p.v.length" class="text-[10px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-medium"><i class="fas fa-layer-group mr-1"></i>Var</span>
                </div>
              </div>

              <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between items-center w-full">
                <span class="font-bold text-lg text-gray-800">₦<span x-text="format(p.p)"></span></span>
                <div class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center transition-colors">
                  <i class="fas fa-plus text-xs"></i>
                </div>
              </div>
            </button>
          </template>

          <!-- No Results -->
          <div x-show="filteredCount === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-gray-400">
            <i class="fas fa-search text-4xl mb-3 opacity-50"></i>
            <p class="text-lg font-medium">No products found</p>
          </div>
        </div>

        <!-- Load More Trigger -->
        <div x-show="hasMore" class="py-6 text-center">
            <button @click="loadMore()" class="px-6 py-2 bg-white border border-gray-300 text-gray-600 rounded-full shadow-sm hover:bg-gray-50 text-sm font-medium transition-colors">
                Load More Products (<span x-text="remainingCount"></span>)
            </button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODALS -->

  <!-- Variant Selector -->
  <div x-data="VariantModal" x-show="show" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-slide-up" @click.away="close()">
      <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
        <h3 class="font-bold text-lg text-gray-800 truncate" x-text="p?.n"></h3>
        <button @click="close()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
      </div>
      
      <div class="p-4 space-y-2 max-h-[60vh] overflow-y-auto custom-scrollbar">
        <template x-for="(v, idx) in p?.v" :key="idx">
          <button @click="sel(v)" class="w-full flex items-center justify-between p-3 border border-gray-200 rounded-xl hover:bg-purple-50 hover:border-purple-500 transition-all text-left group">
            <div>
              <div class="font-bold text-gray-800 group-hover:text-purple-700" x-text="v.n"></div>
              <div class="text-xs text-gray-500">Size: <span x-text="v.q"></span></div>
            </div>
            <div class="font-bold text-purple-600">₦<span x-text="format(v.p)"></span></div>
          </button>
        </template>
      </div>
      
      <div class="p-3 bg-gray-50 border-t flex justify-end">
        <button @click="close()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Receipt Modal -->
  <div x-data="ReceiptModal" 
       x-show="show" x-cloak 
       class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg h-[85vh] flex flex-col overflow-hidden animate-slide-up" @click.away="close()">
      
      <!-- Modal Header -->
      <div class="flex justify-between items-center p-3 border-b bg-gray-50">
        <h3 class="font-bold text-gray-700 flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i> Sale Complete
        </h3>
        <button @click="close()" class="w-8 h-8 flex items-center justify-center hover:bg-gray-200 rounded-full text-gray-500 transition-colors">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Iframe Content -->
      <div class="flex-1 bg-gray-100 relative">
        <!-- Loader -->
        <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center z-10 bg-white">
            <i class="fas fa-circle-notch animate-spin text-4xl text-purple-600 mb-3"></i>
            <p class="text-sm text-gray-500 font-medium">Loading Receipt...</p>
        </div>
        
        <iframe x-ref="receiptFrame" class="w-full h-full border-0 bg-white" @load="stopLoading()"></iframe>
      </div>

      <!-- Footer Actions -->
      <div class="p-4 bg-white border-t border-gray-200 grid grid-cols-2 gap-3">
        <button @click="print()" 
                :disabled="loading"
                class="flex items-center justify-center py-3 rounded-lg bg-gray-100 text-gray-800 font-bold hover:bg-gray-200 border border-gray-300 transition-all disabled:opacity-50">
            <i class="fas fa-print mr-2"></i> Print
        </button>

        <button @click="newOrder()" 
                class="flex items-center justify-center py-3 rounded-lg bg-purple-600 text-white font-bold hover:bg-purple-700 shadow-md transition-all active:scale-95">
            <i class="fas fa-plus-circle mr-2"></i> New Order
        </button>
      </div>

    </div>
  </div>

  <script>
    // --- SERVER DATA ---
    const DB = @json($preparedProducts);
    const STORE_ID = "{{ $storeId }}";
    const BASE_URL = "{{ url('/') }}";
    const formatter = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.addEventListener('alpine:init', () => {

        // --- MULTI-TAB CART LOGIC ---
        Alpine.data('CartSidebar', () => ({
            sessions: {},
            activeTab: 'tab_1',
            loading: false,

            init() {
                // Load sessions or create default
                const saved = localStorage.getItem('pos_sessions');
                if (saved) {
                    this.sessions = JSON.parse(saved);
                    const keys = Object.keys(this.sessions);
                    if (keys.length > 0) this.activeTab = keys[keys.length - 1]; 
                    else this.createTab();
                } else {
                    this.createTab();
                }

                window.addEventListener('add', e => this.add(e.detail.p, e.detail.v));
            },

            // --- TAB MANAGEMENT ---
            createTab() {
                const id = 'tab_' + Date.now();
                const num = Object.keys(this.sessions).length + 1;
                this.sessions[id] = { number: num, items: {} };
                this.activeTab = id;
                this.save();
            },

            switchTab(id) {
                this.activeTab = id;
            },

            closeTab(id) {
                if(Object.keys(this.sessions).length <= 1) {
                    this.clear(); 
                    return;
                }
                delete this.sessions[id];
                const keys = Object.keys(this.sessions);
                this.activeTab = keys[keys.length - 1];
                this.save();
            },

            // --- GETTERS ---
            get currentItems() { return this.sessions[this.activeTab]?.items || {}; },
            get count() { return Object.keys(this.currentItems).length; },
            get total() { 
                return formatter.format(Object.values(this.currentItems).reduce((sum, i) => sum + (i.p * i.qty), 0));
            },

            // --- ITEM MANIPULATION ---
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
            },

            remove(key) { 
                delete this.sessions[this.activeTab].items[key]; 
                this.save(); 
            },

            mod(key, n) {
                const items = this.sessions[this.activeTab].items;
                if(!items[key]) return;
                items[key].qty += n;
                if(items[key].qty < 1) {
                    if(confirm('Remove item?')) delete items[key];
                    else items[key].qty = 1;
                }
                this.save();
            },

            check(k) {
                const items = this.sessions[this.activeTab].items;
                if(items[k] && items[k].qty < 1) items[k].qty = 1;
                this.save();
            },

            clear() {
                if(confirm('Clear cart for Order #' + this.sessions[this.activeTab].number + '?')) {
                    this.sessions[this.activeTab].items = {};
                    this.save();
                }
            },

            save() { localStorage.setItem('pos_sessions', JSON.stringify(this.sessions)); },

            scrollToBottom() {
                this.$nextTick(() => {
                    const el = document.getElementById('cart-container');
                    if(el) el.scrollTop = el.scrollHeight;
                });
            },

            playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = 800;
                    gain.gain.value = 0.05;
                    osc.start();
                    setTimeout(() => osc.stop(), 80);
                } catch(e) {}
            },

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

                try {
                    const res = await fetch('{{ route("checkout.process") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if(!res.ok) throw new Error(data.message || 'Validation Failed');

                    // Clear items on success
                    this.sessions[this.activeTab].items = {};
                    this.save();

                    const receiptUrl = `/receipt/${data.order_id}`; 
                    
                    // Dispatch Event
                    window.dispatchEvent(new CustomEvent('open-receipt-modal', { detail: { url: receiptUrl } }));

                } catch(e) {
                    console.error(e);
                    alert(`❌ Error: ${e.message}`);
                } finally {
                    this.loading = false;
                }
            }
        }));

        // --- PRODUCT GRID ---
        Alpine.data('ProductGrid', () => ({
            all: DB,
            search: '',
            displayLimit: 24,
            lastReceiptUrl: null,
            get filtered() {
                if(!this.search) return this.all;
                const s = this.search.toLowerCase();
                return this.all.filter(x => x.n.toLowerCase().includes(s) || (x.b && x.b.includes(s)));
            },
            get visibleProducts() { return this.filtered.slice(0, this.displayLimit); },
            get filteredCount() { return this.filtered.length; },
            get hasMore() { return this.displayLimit < this.filtered.length; },
            get remainingCount() { return this.filtered.length - this.displayLimit; },
            
            init() {
                this.$watch('search', () => { this.displayLimit = 24; document.getElementById('product-scroll-area').scrollTop = 0; });
                
                // EVENT LISTENER
                window.addEventListener('open-receipt-modal', e => {
                    this.lastReceiptUrl = e.detail.url;
                });
            },
            
            loadMore() { this.displayLimit += 24; },
            enterSearch() {
                const exact = this.filtered.find(p => p.b === this.search);
                if(exact) { this.click(exact); this.search = ''; }
            },
            format(n) { return formatter.format(n); },
            click(p) {
                if(p.v && p.v.length > 0) window.dispatchEvent(new CustomEvent('var', { detail: p }));
                else window.dispatchEvent(new CustomEvent('add', { detail: { p } }));
            }
        }));

        // --- RECEIPT MODAL ---
        Alpine.data('ReceiptModal', () => ({
            show: false, loading: true,
            init() { window.addEventListener('open-receipt-modal', e => this.fetchReceipt(e.detail.url)); },
            async fetchReceipt(url) {
                this.show = true; this.loading = true;
                try {
                    const r = await fetch(url);
                    let html = await r.text();
                    if(!html.includes('<base')) html = html.replace('<head>', `<head><base href="${BASE_URL}/">`);
                    this.$refs.receiptFrame.srcdoc = html;
                    setTimeout(() => { this.loading = false; }, 500);
                } catch(e) { alert("Load failed"); this.show = false; }
            },
            stopLoading() { this.loading = false; },
            close() { this.show = false; this.$refs.receiptFrame.srcdoc = ''; },
            newOrder() { this.close(); },
            print() {
                const f = this.$refs.receiptFrame;
                if(f && f.contentWindow) { f.contentWindow.focus(); f.contentWindow.print(); }
            }
        }));

        // --- VARIANT MODAL ---
        Alpine.data('VariantModal', () => ({
            show: false, p: null,
            init() { window.addEventListener('var', e => { this.p = e.detail; this.show = true; }); },
            close() { this.show = false; },
            sel(v) { window.dispatchEvent(new CustomEvent('add', { detail: { p: this.p, v: v } })); this.close(); },
            format(n) { return formatter.format(n); }
        }));
    });
  </script>
</body>
</html>