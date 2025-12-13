@php
  $user = auth()->user();
  $userName = $user->name ?? 'Cashier';
  
  $store = $user->store ?? null;
  $storeId = $store ? $store->id : 0;
  $storeName = $store ? $store->name : 'POS Terminal';
  $storeAddress = $store ? $store->address : 'Main Branch';
  
  $products = $products ?? collect();

  $preparedProducts = $products->map(function ($product) use ($storeId) {
    $stock = 0;
    if ($product->relationLoaded('storeInventories') || $product->storeInventories) {
        $stock = (int) $product->storeInventories->where('store_id', $storeId)->sum('quantity');
    }

    $variants = collect($product->variants ?? [])->map(function ($v, $k) {
      $get = fn($key) => is_array($v) ? ($v[$key] ?? null) : ($v->$key ?? null);
      return [
        'id' => $get('id') ?? $k,
        'n'  => $get('variant_name') ?? $get('unit_type') ?? 'Option',
        'q'  => $get('unit_qty') ?? 1,
        'p'  => (float) ($get('price') ?? 0)
      ];
    })->values()->all();

    return [
      'id' => $product->id,
      'n'  => $product->name,
      'b'  => (string)($product->barcode ?? ''),
      'p'  => (float) ($product->sale ?? $product->price ?? 0),
      's'  => $stock,
      'v'  => $variants
    ];
  })->sortByDesc('s')->values()->all();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover" />
  <title>POS - {{ $storeName }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="theme-color" content="#0f172a" />
  
  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fira+Code:wght@500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- Libraries -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

  <style>
    body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
    [x-cloak] { display: none !important; }
    
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    .transition-width { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .cart-panel { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
  </style>
</head>

<body class="bg-slate-100 text-gray-800 h-[100dvh] overflow-hidden flex flex-col" 
      x-data="app()" 
      @keydown.window="handleGlobalKeys($event)">

  <!-- TOP NAV -->
  <nav class="bg-slate-900 text-white h-14 flex-none z-40 shadow-md flex justify-between items-center px-4 safe-area-top">
    <div class="flex items-center gap-3 w-1/3">
       <div class="w-9 h-9 rounded-lg bg-purple-600 flex items-center justify-center font-bold text-lg shadow-lg">P</div>
       <div class="leading-tight overflow-hidden">
           <div class="font-bold tracking-tight truncate">{{ $storeName }}</div>
           <div class="text-[10px] text-gray-400 md:hidden">POS System</div>
       </div>
    </div>
    <div class="hidden md:flex items-center justify-center gap-2 text-xs text-gray-300 w-1/3">
        <i class="fas fa-map-marker-alt text-purple-400"></i>
        <span class="truncate max-w-[250px]">{{ $storeAddress }}</span>
    </div>
    <div class="flex items-center justify-end gap-4 w-1/3 text-xs">
      <div x-data="syncManager()" class="flex items-center gap-3">
          <div x-show="queueCount > 0" x-cloak class="flex items-center gap-2 px-2 py-1 rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 cursor-pointer" @click="process">
             <i class="fas fa-sync" :class="syncing ? 'fa-spin' : ''"></i><span class="font-bold hidden sm:inline" x-text="queueCount"></span>
          </div>
          <div class="w-2.5 h-2.5 rounded-full" :class="online ? 'bg-emerald-500' : 'bg-red-500'"></div>
      </div>
      <div class="flex items-center gap-2 pl-4 border-l border-gray-700">
          <div class="text-right hidden sm:block">
              <div class="font-bold text-white">{{ $userName }}</div>
              <div class="text-[10px] text-gray-400" x-text="time"></div>
          </div>
          <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-gray-300 border border-gray-600"><i class="fas fa-user"></i></div>
      </div>
    </div>
  </nav>

  <!-- MAIN LAYOUT -->
  <div class="flex-1 flex overflow-hidden relative flex-row">

    <!-- 1. CART SIDEBAR (NOW ON LEFT) -->
    <!-- Added border-r (right) instead of border-l -->
    <div x-data="cartSidebar()" 
         class="fixed inset-0 z-[100] md:static md:inset-auto md:z-30 md:bg-white md:border-r md:border-gray-200 transition-width flex flex-col pointer-events-none md:pointer-events-auto"
         :class="expanded ? 'md:w-[50%]' : 'md:w-[400px]'"
         @keydown.escape.window="mobileCartOpen = false">
      
      <!-- Mobile Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm md:hidden pointer-events-auto transition-opacity duration-300" 
           x-show="mobileCartOpen" x-transition.opacity @click="mobileCartOpen = false"></div>

      <!-- Cart Container -->
      <div class="absolute bottom-0 left-0 right-0 h-[90dvh] md:h-full md:static bg-white rounded-t-2xl md:rounded-none shadow-2xl md:shadow-none flex flex-col cart-panel pointer-events-auto"
           :class="mobileCartOpen ? 'translate-y-0' : 'translate-y-full md:translate-y-0'">
        
        <!-- Mobile Header -->
        <div class="md:hidden flex justify-between items-center p-4 border-b border-gray-100 bg-gray-50 rounded-t-2xl" @touchstart.passive="$event.stopPropagation()">
            <h2 class="font-bold text-lg text-gray-800">Current Order</h2>
            <button @click="mobileCartOpen = false" class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-600 shadow-sm"><i class="fas fa-chevron-down"></i></button>
        </div>

        <!-- Desktop Header / Tabs -->
        <div class="hidden md:flex items-center justify-between bg-white border-b border-gray-100 px-2 h-12 flex-none">
            <div class="flex overflow-x-auto hide-scroll flex-1">
                <template x-for="(session, id) in sessions" :key="id">
                  <div @click="switchTab(id)" class="group relative px-4 h-full flex items-center justify-center cursor-pointer text-xs font-bold transition-colors min-w-[80px]" :class="activeTab === id ? 'text-gray-900' : 'text-gray-400 hover:text-gray-600'">
                    <span x-text="`Order #${session.number}`"></span>
                    <div x-show="activeTab === id" class="absolute bottom-0 w-full h-0.5 bg-purple-600"></div>
                    <button x-show="Object.keys(sessions).length > 1" @click.stop="closeTab(id)" class="ml-2 text-[10px] opacity-0 group-hover:opacity-100 hover:text-red-500"><i class="fas fa-times"></i></button>
                  </div>
                </template>
                <button @click="createTab" class="w-8 h-8 ml-1 flex items-center justify-center text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-full"><i class="fas fa-plus"></i></button>
            </div>
            <!-- Resize Button -->
            <button @click="expanded = !expanded" class="ml-2 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-800 rounded-lg"><i class="fas" :class="expanded ? 'fa-compress-alt' : 'fa-expand-alt'"></i></button>
        </div>

        <!-- Summary Bar -->
        <div class="px-4 py-2 bg-purple-50/50 border-b border-purple-50 flex justify-between items-center text-xs">
             <div class="font-bold text-purple-900 flex items-center gap-2"><i class="fas fa-shopping-basket"></i><span x-text="count + ' Items'"></span></div>
             <button @click="clear" x-show="count > 0" class="text-red-500 font-bold hover:bg-red-50 px-2 py-1 rounded transition">Clear All</button>
        </div>

        <!-- Items List -->
        <div class="flex-1 overflow-y-auto px-3 py-2 space-y-2 custom-scroll bg-white" id="cart-container">
            <template x-for="(item, key) in currentItems" :key="key">
              <div class="flex justify-between items-start p-3 bg-white border border-gray-100 rounded-xl hover:border-purple-200 transition-colors shadow-sm">
                <div class="flex-1 pr-3 min-w-0">
                  <div class="text-sm font-bold text-gray-800 leading-tight truncate" x-text="item.n"></div>
                  <div class="flex items-center gap-2 mt-1">
                      <span x-show="item.v_name" class="text-[10px] font-bold bg-gray-100 px-1.5 rounded text-gray-500 border border-gray-200" x-text="item.v_name"></span>
                      <span class="text-[10px] text-gray-400 font-mono">@ ₦<span x-text="format(item.p)"></span></span>
                  </div>
                </div>
                <div class="flex flex-col items-end gap-2">
                   <div class="font-bold text-sm text-gray-900">₦<span x-text="format(calculateLineTotal(item))"></span></div>
                   <div class="flex items-center bg-gray-50 rounded-lg h-7 border border-gray-200 shadow-sm">
                     <button @click="mod(key, -1)" class="w-8 h-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-l-lg transition"><i class="fas fa-minus text-[10px]"></i></button>
                     <div class="w-8 text-center text-xs font-bold text-gray-700 font-mono" x-text="item.qty"></div>
                     <button @click="mod(key, 1)" class="w-8 h-full flex items-center justify-center text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-r-lg transition"><i class="fas fa-plus text-[10px]"></i></button>
                   </div>
                </div>
              </div>
            </template>
            <div x-show="count === 0" class="flex flex-col items-center justify-center h-48 text-gray-300">
                <i class="fas fa-basket-shopping text-3xl mb-3 opacity-30"></i><p class="text-xs font-medium">Cart is empty</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 bg-gray-50 border-t border-gray-200 safe-area-bottom">
            <div class="flex justify-between items-end mb-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total</span>
                <span class="text-3xl font-black text-gray-900 tracking-tight">₦<span x-text="total"></span></span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <button @click="pay('cash')" :disabled="count===0" class="h-12 bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white rounded-xl font-bold text-sm shadow-lg disabled:opacity-50 transition-all">CASH</button>
                <button @click="pay('pos')" :disabled="count===0" class="h-12 bg-blue-500 hover:bg-blue-600 active:scale-95 text-white rounded-xl font-bold text-sm shadow-lg disabled:opacity-50 transition-all">POS</button>
                <button @click="pay('bank')" :disabled="count===0" class="h-12 bg-purple-500 hover:bg-purple-600 active:scale-95 text-white rounded-xl font-bold text-sm shadow-lg disabled:opacity-50 transition-all">BANK</button>
            </div>
        </div>
      </div>
    </div>

    <!-- 2. PRODUCT GRID (NOW ON RIGHT) -->
    <div x-data="productGrid()" class="flex-1 flex flex-col bg-slate-50 h-full relative z-0 min-w-0">
      <div class="bg-white p-3 border-b border-gray-200 shadow-sm flex items-center gap-3 flex-none sticky top-0 z-30">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input id="product-search" type="text" x-model="search" @input.debounce.150ms="updateFilter"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-100 focus:bg-white border-transparent focus:border-purple-500 focus:ring-0 rounded-lg text-sm transition-all outline-none shadow-inner" 
                placeholder="Search products..." />
            <button x-show="search" @click="search=''; updateFilter()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 p-2"><i class="fas fa-times-circle"></i></button>
        </div>
        <button @click="$dispatch('toggle-scanner')" class="bg-gray-900 text-white w-10 h-10 rounded-lg flex-none flex items-center justify-center active:scale-95 shadow-md hover:bg-gray-800 transition-colors">
            <i class="fas fa-qrcode text-lg"></i>
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-3 pb-24 md:pb-5 custom-scroll" id="product-area">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3">
          <template x-for="p in visibleProducts" :key="p.id">
            <button @click="click(p)" class="group relative flex flex-col justify-between bg-white rounded-xl p-3 border border-gray-200 shadow-sm active:scale-[0.98] transition-all text-left h-[135px] hover:border-purple-300 hover:shadow-md">
              <div class="flex justify-between w-full items-start mb-1">
                 <span class="font-bold text-base text-purple-700 tracking-tight">₦<span x-text="format(p.p)"></span></span>
                 <i x-show="p.v.length" class="fas fa-layer-group text-[10px] text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded"></i>
              </div>
              <div class="flex-1 flex flex-col justify-center">
                  <h3 class="font-semibold text-[13px] text-gray-800 leading-snug line-clamp-2" x-text="p.n"></h3>
                  <div class="text-[10px] text-gray-400 font-mono mt-0.5 truncate" x-show="p.b" x-text="p.b"></div>
              </div>
              <div class="mt-2 w-full pt-2 border-t border-gray-50">
                <div x-show="p.s <= 0" class="text-[10px] font-bold text-red-500">Out of Stock</div>
                <div x-show="p.s > 0" class="text-[10px] text-gray-600 font-medium"><span x-text="p.s"></span> left</div>
              </div>
            </button>
          </template>
        </div>
        <div x-show="visibleProducts.length < filteredCount" class="py-8 text-center"><button @click="limit += 24" class="text-xs font-bold text-purple-600 px-6 py-2 bg-purple-50 rounded-full">Load More</button></div>
      </div>
    </div>

    <!-- MOBILE FLOATING BTN -->
    <div x-data class="md:hidden fixed bottom-6 left-4 right-4 z-40 transition-transform duration-300 ease-in-out" :class="$store.global.cartCount > 0 && !$store.global.mobileCartOpen ? 'translate-y-0' : 'translate-y-[200%]'" x-cloak>
        <button @click="$dispatch('open-mobile-cart')" class="w-full bg-gray-900 text-white h-14 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex justify-between items-center px-4 active:scale-95 transition-transform border border-gray-700/50 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <div class="bg-purple-600 w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-md border border-white/20" x-text="$store.global.cartCount"></div>
                <span class="text-sm font-semibold text-gray-100">View Order</span>
            </div>
            <span class="font-bold text-lg tracking-tight">₦<span x-text="$store.global.cartTotal"></span></span>
        </button>
    </div>
  </div>

  <!-- MODALS -->
  <div x-data="variantModal" x-show="show" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div @click.away="show=false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center bg-gray-50"><h3 class="font-bold text-gray-800 text-sm" x-text="product?.n"></h3><button @click="show=false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 text-gray-500"><i class="fas fa-times"></i></button></div>
        <div class="p-2 space-y-2 max-h-[50vh] overflow-y-auto custom-scroll">
            <template x-for="v in product?.v" :key="v.id">
                <button @click="select(v)" class="w-full flex justify-between items-center p-3 hover:bg-purple-50 rounded-xl border border-transparent hover:border-purple-200 transition-all group">
                    <div class="text-left"><div class="font-bold text-gray-800 text-sm group-hover:text-purple-700" x-text="v.n"></div><div class="text-xs text-gray-400" x-text="'Unit Qty: ' + v.q"></div></div>
                    <div class="font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded text-sm">₦<span x-text="format(v.p)"></span></div>
                </button>
            </template>
        </div>
    </div>
  </div>

  <div x-data="receiptModal" x-show="show" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm flex flex-col max-h-[90vh] shadow-2xl overflow-hidden">
        <div class="p-4 bg-emerald-500 text-white flex justify-between items-center"><span class="font-bold flex items-center gap-2"><i class="fas fa-check-circle"></i> Success</span><button @click="close" class="text-white/70 hover:text-white"><i class="fas fa-times"></i></button></div>
        <div class="flex-1 overflow-y-auto p-6 bg-gray-100 flex justify-center custom-scroll"><div id="receipt-paper" class="bg-white w-full shadow-lg p-5 text-xs font-mono text-gray-700 leading-tight"></div></div>
        <div class="p-4 bg-white border-t grid grid-cols-2 gap-3"><button @click="print" class="py-3 bg-gray-900 text-white rounded-xl font-bold shadow-lg">Print</button><button @click="close" class="py-3 bg-gray-100 text-gray-800 rounded-xl font-bold border border-gray-200">Close</button></div>
    </div>
  </div>
  
  <div x-data="scannerComponent()" x-show="open" x-cloak class="fixed inset-0 z-[130] bg-black flex flex-col">
    <div class="absolute top-0 w-full p-6 flex justify-between items-start z-10 bg-gradient-to-b from-black/90 to-transparent">
        <div><h2 class="font-bold text-white text-xl">Scanner</h2><p class="text-xs text-gray-300" x-text="error ? error : 'Point at barcode'">Point at barcode</p></div>
        <button @click="stop" class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white"><i class="fas fa-times"></i></button>
    </div>
    <div x-show="!isSecure" class="absolute inset-0 flex items-center justify-center z-50 bg-black/90 text-white text-center p-6"><div><i class="fas fa-lock text-red-500 text-4xl mb-4"></i><h3 class="font-bold text-lg">Camera blocked</h3><p class="text-sm text-gray-400 mt-2">Browser requires HTTPS.</p></div></div>
    <div class="flex-1 relative bg-black flex items-center justify-center"><div id="reader" class="w-full h-full object-cover"></div></div>
  </div>

  <div x-data="{ msgs: [] }" @notify.window="msgs.push($event.detail); setTimeout(()=>msgs.shift(), 2500)" class="fixed top-16 right-4 z-[150] flex flex-col gap-2 pointer-events-none w-auto max-w-xs">
    <template x-for="(m, i) in msgs" :key="i">
       <div class="px-4 py-3 rounded-xl shadow-xl text-xs font-bold flex items-center gap-3 animate-enter text-white min-w-[200px]" :class="m.type === 'error' ? 'bg-red-500' : 'bg-emerald-500'"><i class="fas" :class="m.type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'"></i><span x-text="m.message"></span></div>
    </template>
  </div>

  <script>
    const RAW_PRODUCTS = @json($preparedProducts);
    const STORE_INFO = { name: "{{ $storeName }}", address: "{{ $storeAddress }}", id: "{{ $storeId }}" };
    const CURRENCY = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    const CHECKOUT_URL = "{{ route('checkout.process') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    document.addEventListener('alpine:init', () => { Alpine.store('global', { mobileCartOpen: false, cartCount: 0, cartTotal: '0' }); });

    const format = (n) => CURRENCY.format(n);
    const vibrate = () => { if(navigator.vibrate) navigator.vibrate(40); };
    const playBeep = () => { const audio = new AudioContext(); const osc = audio.createOscillator(); const gain = audio.createGain(); osc.connect(gain); gain.connect(audio.destination); osc.frequency.value = 1200; gain.gain.value = 0.05; osc.start(); setTimeout(() => osc.stop(), 80); };

    function app() { return { time: '', init() { setInterval(() => this.time = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}), 1000); }, handleGlobalKeys(e) { if(e.key === 'Escape') window.dispatchEvent(new CustomEvent('close-modals')); } } }
    
    function productGrid() {
        return {
            all: RAW_PRODUCTS, filtered: RAW_PRODUCTS, search: '', limit: 24,
            init() { window.addEventListener('camera-scan', (e) => { const exact = this.all.find(p => p.b && p.b.toString() === e.detail.toString()); if (exact) { this.click(exact); window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Added: ' + exact.n, type: 'success' } })); } else { window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Not found', type: 'error' } })); } }); },
            get visibleProducts() { return this.filtered.slice(0, this.limit); },
            get filteredCount() { return this.filtered.length; },
            updateFilter() { const s = this.search.toLowerCase().trim(); this.limit = 24; if(!s) { this.filtered = this.all; return; } this.filtered = this.all.filter(p => p.n.toLowerCase().includes(s) || (p.b && p.b.includes(s))); },
            click(p) { vibrate(); if(p.v && p.v.length) window.dispatchEvent(new CustomEvent('open-variant', { detail: p })); else window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { p } })); },
            format
        }
    }

    function cartSidebar() {
        return {
            sessions: { 'default': { number: 1, items: {} } }, activeTab: 'default', mobileCartOpen: false, expanded: false,
            init() {
                const s = localStorage.getItem('pos_cart_final'); if(s) this.sessions = JSON.parse(s);
                window.addEventListener('add-to-cart', e => this.add(e.detail.p, e.detail.v));
                window.addEventListener('pay-request', e => this.pay(e.detail));
                window.addEventListener('open-mobile-cart', () => { this.mobileCartOpen = true; }); 
                this.$watch('mobileCartOpen', val => { Alpine.store('global').mobileCartOpen = val; if(val) vibrate(); });
                this.updateGlobal();
            },
            save() { localStorage.setItem('pos_cart_final', JSON.stringify(this.sessions)); this.updateGlobal(); },
            updateGlobal() { Alpine.store('global').cartCount = this.count; Alpine.store('global').cartTotal = this.total; },
            switchTab(id) { this.activeTab = id; },
            createTab() { const id = 't'+Date.now(); this.sessions[id] = { number: Object.keys(this.sessions).length + 1, items: {} }; this.activeTab = id; this.save(); },
            closeTab(id) { delete this.sessions[id]; if(Object.keys(this.sessions).length === 0) this.createTab(); else this.activeTab = Object.keys(this.sessions)[0]; this.save(); },
            get currentItems() { return this.sessions[this.activeTab]?.items || {}; },
            get count() { return Object.keys(this.currentItems).length; },
            calculateLineTotal(item) { return (Math.round((item.p * 100) * item.qty) / 100); },
            get total() { const raw = Object.values(this.currentItems).reduce((a, i) => a + (Math.round((i.p * 100) * i.qty) / 100), 0); return format(raw); },
            add(p, v=null) { const key = `${p.id}_${v?v.id:'base'}`; const items = this.sessions[this.activeTab].items; if(items[key]) items[key].qty++; else items[key] = { id: p.id, n: p.n, p: v?v.p:p.p, v_name: v?v.n:null, vid: v?v.id:null, qty: 1 }; this.save(); },
            mod(k, n) { const i = this.sessions[this.activeTab].items; if(i[k]) { i[k].qty += n; if(i[k].qty < 1) delete i[k]; this.save(); } },
            clear() { if(confirm('Clear cart?')) { this.sessions[this.activeTab].items = {}; this.save(); this.mobileCartOpen = false; } },
            pay(method) { if(this.count === 0) return; const order = { id: 'ORD-'+Math.floor(Date.now()/1000), date: new Date().toLocaleString(), method: method, items: Object.values(this.currentItems), total: this.total, store_id: STORE_INFO.id }; window.dispatchEvent(new CustomEvent('show-receipt', { detail: order })); window.dispatchEvent(new CustomEvent('queue-push', { detail: order })); this.sessions[this.activeTab].items = {}; this.save(); this.mobileCartOpen = false; },
            format
        }
    }

    const variantModal = { show: false, product: null, init() { window.addEventListener('open-variant', e => { this.product=e.detail; this.show=true; }); window.addEventListener('close-modals', ()=>this.show=false); }, select(v) { window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { p: this.product, v } })); this.show = false; vibrate(); }, format };
    
    function scannerComponent() {
        return {
            open: false, scanner: null, error: null, isSecure: location.protocol === 'https:' || location.hostname === 'localhost',
            init() { window.addEventListener('toggle-scanner', () => { this.open ? this.stop() : this.start(); }); window.addEventListener('close-modals', () => this.stop()); },
            start() {
                if(!this.isSecure) return; this.open = true; this.error = null;
                this.$nextTick(() => { setTimeout(() => { if(this.scanner) return; this.scanner = new Html5Qrcode("reader"); const config = { fps: 15, qrbox: { width: 250, height: 150 }, aspectRatio: 1.0 }; this.scanner.start({ facingMode: "environment" }, config, this.onScan, this.onError).catch(() => { this.scanner.start({ facingMode: "user" }, config, this.onScan, this.onError).catch(err => { this.error = "Camera access denied"; }); }); }, 300); });
            },
            onScan(decodedText) { playBeep(); vibrate(); if (this.scanner) { this.scanner.stop().then(() => { this.scanner.clear(); this.scanner = null; }).catch(()=>{}); } this.open = false; window.dispatchEvent(new CustomEvent('camera-scan', { detail: decodedText })); },
            onError(err) { },
            stop() { if (this.scanner) { this.scanner.stop().then(() => { this.scanner.clear(); this.scanner = null; }).catch(()=>{}); } this.open = false; }
        }
    }
    
    const receiptModal = { show: false, data: null, init() { window.addEventListener('show-receipt', e => { this.data=e.detail; this.generate(); this.show=true; }); window.addEventListener('close-modals', ()=>this.show=false); }, generate() { if(!this.data) return; const items = this.data.items.map(i => `<div style="display:flex;justify-content:space-between;margin-bottom:5px"><span>${i.qty} x ${i.n}</span><span>${format(i.p*i.qty)}</span></div>`).join(''); const html = `<div style="text-align:center;border-bottom:1px dashed #ccc;padding-bottom:10px;margin-bottom:10px"><h3 style="margin:0;font-size:16px">${STORE_INFO.name}</h3><p style="margin:2px 0;font-size:10px">${STORE_INFO.address}</p><p style="margin:2px 0;font-size:10px">${this.data.date}</p><p style="margin:0;font-size:10px">Ref: ${this.data.id}</p></div><div style="margin-bottom:15px; border-bottom:1px dashed #ccc; padding-bottom:10px">${items}</div><div style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px"><span>TOTAL</span><span>₦${this.data.total}</span></div><div style="text-align:center;margin-top:15px;font-size:10px">Paid via ${this.data.method.toUpperCase()}</div>`; document.getElementById('receipt-paper').innerHTML = html; }, print() { const win = window.open('','','width=300,height=500'); win.document.write(`<html><body style="font-family:monospace;font-size:12px;padding:10px">${document.getElementById('receipt-paper').innerHTML}</body></html>`); win.print(); win.close(); }, close() { this.show = false; } };
    function syncManager() { return { queue: JSON.parse(localStorage.getItem('pos_queue') || '[]'), online: navigator.onLine, syncing: false, init() { window.addEventListener('online', () => { this.online=true; this.process(); }); window.addEventListener('offline', () => this.online=false); window.addEventListener('queue-push', e => { this.queue.push(e.detail); localStorage.setItem('pos_queue', JSON.stringify(this.queue)); this.process(); }); if(this.online && this.queue.length) setTimeout(()=>this.process(),1000); }, get queueCount() { return this.queue.length; }, async process() { if(!this.online || this.syncing || this.queue.length === 0) return; this.syncing = true; const order = this.queue[0]; try { await fetch(CHECKOUT_URL, { method: 'POST', headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF}, body: JSON.stringify({ cart: order.items.map(i=>({product_id:i.id, variant_id:i.vid, quantity:i.qty, price:i.p})), paymentMethod: order.method, store_id: order.store_id, total: parseFloat(order.total.replace(/,/g,'')) }) }); this.queue.shift(); localStorage.setItem('pos_queue', JSON.stringify(this.queue)); window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Synced', type: 'success' } })); } catch(e) {} this.syncing = false; if(this.queue.length && this.online) setTimeout(()=>this.process(),2000); } } }
  </script>
</body>
</html>