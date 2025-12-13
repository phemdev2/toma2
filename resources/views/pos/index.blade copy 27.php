<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
  <title>POS - {{ auth()->user()->store->name ?? 'Store' }}</title>
  <meta name="description" content="Offline POS" />
  <link rel="icon" href="/favicon.ico" sizes="any" />
  <meta name="theme-color" content="#581c87">
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
          animation: { 'fade-in': 'fadeIn 0.18s ease-out', 'pulse-fast': 'pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite' },
          keyframes: { fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } } }
        }
      }
    };
  </script>

  <!-- Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

  <style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    [x-cloak] { display: none !important; }
    /* Hide number input spinners */
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  </style>
</head>

@php
  $user = auth()->user();
  $store = $user->store ?? null;
  $storeId = $store ? $store->id : 0;
  $storeName = $store ? $store->name : 'My Store';
  
  // Optimization: Ensure we don't crash if $products isn't passed
  $products = $products ?? collect();

  $preparedProducts = $products->map(function ($product) use ($storeId) {
    // Calculate stock specifically for this store
    $stock = 0;
    if ($product->storeInventories) {
        $stock = (int) $product->storeInventories
                        ->where('store_id', $storeId)
                        ->sum('quantity');
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
      'b'  => (string)($product->barcode ?? ''), // Ensure string for search
      'p'  => (float) ($product->sale ?? $product->price ?? 0),
      's'  => $stock,
      'v'  => $variants
    ];
  })
  // ---------------------------------------------------------
  // MODIFICATION: Sort by stock ('s') Descending
  // ---------------------------------------------------------
  ->sortByDesc('s') 
  ->values()
  ->all();
@endphp

<body class="bg-slate-100 font-sans text-gray-800 h-screen overflow-hidden flex flex-col" x-data>

  <!-- TOP NAV -->
  <nav class="bg-slate-900 text-white h-14 flex-none z-50 shadow-md flex justify-between items-center px-4">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded bg-purple-600 flex items-center justify-center font-bold text-lg">P</div>
      <span class="font-bold text-lg hidden md:block">POS System</span>
    </div>

    <div class="text-center leading-tight">
      <div class="font-bold text-sm">{{ $storeName }}</div>
      <div class="text-[10px] text-gray-400" id="clock-display"></div>
    </div>

    <div class="flex items-center gap-4 text-sm" x-data="syncManager()">
      
      <!-- Sync Status Indicator -->
      <div x-show="queueCount > 0" x-cloak
           class="flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-500 text-black font-bold text-xs animate-pulse-fast cursor-pointer hover:bg-yellow-400"
           @click="forceSync" title="Click to force sync">
         <i class="fas fa-sync" :class="syncing ? 'fa-spin' : ''"></i>
         <span x-text="queueCount + ' Pending'"></span>
      </div>

      <div class="flex items-center gap-2 px-3 py-1 rounded-full transition-colors duration-300 font-bold text-xs"
           :class="online ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-600 text-white shadow-lg animate-pulse'">
         <div class="w-2 h-2 rounded-full" :class="online ? 'bg-emerald-400' : 'bg-white'"></div>
         <span x-text="online ? 'Online' : 'Offline'"></span>
      </div>

      <!-- User Profile -->
      <div class="hidden md:flex items-center gap-2 border-l border-gray-700 pl-4">
        <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold">{{ substr($user->name, 0, 1) }}</div>
      </div>
    </div>
  </nav>

  <div class="flex-1 flex overflow-hidden">

    <!-- CART SIDEBAR -->
    <div x-data="cartSidebar()" class="bg-white border-r border-gray-200 shadow-xl z-40 w-full md:w-[420px] lg:w-[450px] flex flex-col h-full relative transition-all">
      
      <!-- Tabs -->
      <div class="flex items-center bg-gray-100 border-b border-gray-200 overflow-x-auto custom-scrollbar">
        <template x-for="(session, id) in tabIds" :key="id">
          <div @click="switchTab(id)" class="group relative min-w-[100px] max-w-[140px] px-3 py-2 text-xs font-bold cursor-pointer border-r border-gray-200 select-none flex items-center justify-between transition-colors"
               :class="activeTab === id ? 'bg-white text-purple-700 border-t-2 border-t-purple-600' : 'text-gray-500 hover:bg-gray-200'">
            <span class="truncate mr-2" x-text="`Order ${session.number}`"></span>
            <button x-show="tabCount > 1" @click.stop="closeTab(id)" class="text-gray-300 hover:text-red-500"><i class="fas fa-times"></i></button>
          </div>
        </template>
        <button @click="createTab" class="px-3 py-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors" title="New Order Tab"><i class="fas fa-plus"></i></button>
      </div>

      <!-- Header -->
      <div class="p-3 bg-gradient-to-r from-purple-800 to-indigo-800 text-white shadow-sm z-10">
        <div class="flex justify-between items-center mb-1">
          <h2 class="font-bold flex items-center gap-2 text-sm">
            <i class="fas fa-shopping-basket"></i>
            <span>Current Order</span>
            <span x-show="count > 0" class="bg-white/20 text-[10px] px-2 py-0.5 rounded-full" x-text="count + ' items'"></span>
          </h2>
          <button @click="clear" x-show="count > 0" class="text-[10px] bg-red-500/20 hover:bg-red-500/40 px-2 py-1 rounded border border-red-400/30 transition-colors">Clear All</button>
        </div>
      </div>

      <!-- Cart Items Container -->
      <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar bg-slate-50" id="cart-container">
        <template x-if="count === 0">
          <div class="h-full flex flex-col items-center justify-center text-gray-400 select-none">
            <i class="fas fa-barcode text-5xl opacity-20 mb-3"></i>
            <p class="text-sm font-medium">Cart is empty</p>
            <p class="text-xs opacity-75">Scan product or select from grid</p>
          </div>
        </template>

        <template x-for="(item, key) in currentItems" :key="key">
          <div class="bg-white p-2 rounded border border-gray-200 shadow-sm relative animate-fade-in group hover:border-purple-300 transition-colors">
            <div class="flex justify-between items-start gap-2">
              <div class="flex-1 min-w-0">
                <div class="font-bold text-sm text-gray-800 truncate" x-text="item.n"></div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                   <span x-show="item.v_name" class="bg-indigo-50 text-indigo-600 px-1 rounded text-[10px] border border-indigo-100" x-text="item.v_name"></span>
                   <span>@ ₦<span x-text="format(item.p)"></span></span>
                </div>
              </div>
              <div class="font-bold text-purple-700">₦<span x-text="format(item.p * item.qty)"></span></div>
            </div>

            <div class="flex justify-between items-center mt-2 pt-2 border-t border-dashed border-gray-100">
               <!-- Quantity Controls -->
               <div class="flex items-center shadow-sm rounded-md overflow-hidden border border-gray-200">
                 <button @click="mod(key, -1)" class="w-8 h-7 flex items-center justify-center bg-gray-50 hover:bg-gray-100 active:bg-gray-200 text-gray-600 transition-colors"><i class="fas fa-minus text-[10px]"></i></button>
                 <input type="number" x-model.number="item.qty" @change="check(key)" class="w-10 h-7 text-center bg-white text-sm font-bold focus:outline-none" />
                 <button @click="mod(key, 1)" class="w-8 h-7 flex items-center justify-center bg-gray-50 hover:bg-gray-100 active:bg-gray-200 text-green-600 transition-colors"><i class="fas fa-plus text-[10px]"></i></button>
               </div>
               
               <button @click="remove(key)" class="w-7 h-7 flex items-center justify-center rounded hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>
        </template>
      </div>

      <!-- Payment Footer -->
      <div class="p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-20">
        <div class="flex justify-between items-end mb-4">
          <div class="flex flex-col">
            <span class="text-gray-500 text-xs font-bold uppercase tracking-wider">Total Payable</span>
            <span class="text-[10px] text-gray-400" x-text="count + ' Items'"></span>
          </div>
          <span class="text-3xl font-extrabold text-gray-900 tracking-tight">₦<span x-text="total"></span></span>
        </div>
        
        <div class="grid grid-cols-3 gap-2">
          <button @click="pay('cash')" :disabled="count === 0 || loading" class="relative overflow-hidden flex flex-col items-center justify-center py-3 px-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg shadow-md transition-all active:scale-95 group">
            <span class="text-xs font-bold uppercase relative z-10">Cash</span>
            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform"></div>
          </button>
          
          <button @click="pay('pos')" :disabled="count === 0 || loading" class="relative overflow-hidden flex flex-col items-center justify-center py-3 px-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg shadow-md transition-all active:scale-95 group">
            <span class="text-xs font-bold uppercase relative z-10">POS</span>
            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform"></div>
          </button>
          
          <button @click="pay('bank')" :disabled="count === 0 || loading" class="relative overflow-hidden flex flex-col items-center justify-center py-3 px-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg shadow-md transition-all active:scale-95 group">
            <span class="text-xs font-bold uppercase relative z-10">Transfer</span>
            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform"></div>
          </button>
        </div>
      </div>
      
      <!-- Loading Overlay for Cart -->
      <div x-show="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="flex flex-col items-center">
            <i class="fas fa-circle-notch fa-spin text-3xl text-purple-600 mb-2"></i>
            <span class="text-sm font-bold text-gray-600">Processing...</span>
        </div>
      </div>
    </div>

    <!-- PRODUCT GRID -->
    <div x-data="productGrid()" class="flex-1 flex flex-col h-full bg-slate-100 overflow-hidden relative">
      <div class="bg-white border-b border-gray-200 px-6 py-3 flex flex-col md:flex-row gap-3 justify-between items-center shadow-sm z-30">
        
        <!-- Search Bar -->
        <div class="relative w-full max-w-xl">
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input id="product-search" type="text" x-model.debounce.250ms="search" 
                 class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-white transition-all shadow-sm" 
                 placeholder="Search product name or barcode (F2)" 
                 @keydown.enter.prevent="enterSearch" />
          <button x-show="search" @click="search=''" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times-circle"></i>
          </button>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
           <div class="hidden lg:flex flex-col items-end mr-2">
             <span class="text-xs font-bold text-gray-700"><span x-text="filteredCount"></span> Products</span>
             <span class="text-[10px] text-gray-400">Visible</span>
           </div>
           <!-- Reprint Button -->
           <button @click="$dispatch('open-receipt-modal', {url: lastReceiptUrl})" :disabled="!lastReceiptUrl" class="p-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:text-purple-600 hover:border-purple-300 disabled:opacity-50 disabled:hover:border-gray-300 transition-all shadow-sm" title="Reprint Last Receipt">
             <i class="fas fa-print"></i>
           </button>
        </div>
      </div>

      <!-- Grid Area -->
      <div class="flex-1 p-4 md:p-6 overflow-y-auto custom-scrollbar" id="product-scroll-area">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 pb-20">
          <template x-for="p in visibleProducts" :key="p.id">
            <button @click="click(p)" class="group bg-white rounded-xl border border-gray-200 p-4 flex flex-col justify-between hover:border-purple-500 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 h-full relative text-left">
              
              <div class="absolute top-0 right-0">
                 <span x-show="p.s <= 5 && p.s > 0" class="bg-orange-100 text-orange-600 text-[10px] font-bold px-2 py-1 rounded-bl-lg">Low Stock</span>
                 <span x-show="p.s <= 0" class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded-bl-lg">Out of Stock</span>
              </div>

              <div>
                <h3 class="font-semibold text-gray-800 text-sm leading-snug mb-1 line-clamp-2 h-10" x-text="p.n"></h3>
                <p class="text-xs text-gray-400 font-mono mb-2 h-4" x-text="p.b || ''"></p>
                
                <div class="flex flex-wrap gap-1 mb-2">
                  <span x-show="p.s > 0" class="text-[10px] px-1.5 py-0.5 rounded bg-green-50 text-green-700 font-medium border border-green-100">Stock: <span x-text="p.s"></span></span>
                  <span x-show="p.v.length" class="text-[10px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-medium border border-purple-100"><i class="fas fa-layer-group mr-1"></i>Variants</span>
                </div>
              </div>

              <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between items-center w-full">
                <span class="font-bold text-lg text-gray-800">₦<span x-text="format(p.p)"></span></span>
                <div class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center transition-colors shadow-sm">
                  <i class="fas fa-plus text-xs"></i>
                </div>
              </div>
            </button>
          </template>
        </div>

        <div x-show="hasMore" class="py-6 text-center">
            <button @click="loadMore()" class="px-6 py-2 bg-white border border-gray-300 text-gray-600 rounded-full shadow-sm hover:bg-gray-50 hover:text-purple-600 text-sm font-medium transition-colors">Load More Products</button>
        </div>
        
        <div x-show="filteredCount === 0" class="flex flex-col items-center justify-center pt-20 opacity-50">
           <i class="fas fa-search text-4xl mb-3"></i>
           <p>No products found matching "<span x-text="search"></span>"</p>
        </div>
      </div>
    </div>
  </div>

  <!-- VARIANT MODAL -->
  <div x-data="variantModal()" x-show="visible" x-cloak x-trap.noscroll="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div @click.away="close" @keydown.escape.window="close" class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden animate-fade-in">
      <div class="p-3 bg-gray-50 border-b flex justify-between items-center">
        <h3 class="font-bold text-gray-800 truncate pr-4" x-text="product?.n"></h3>
        <button @click="close" class="text-gray-400 hover:text-red-500 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200"><i class="fas fa-times"></i></button>
      </div>
      <div class="p-3 space-y-2 max-h-[60vh] overflow-y-auto custom-scrollbar">
        <template x-for="(v, idx) in product?.v" :key="idx">
          <button @click="select(v)" class="w-full flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 text-left transition-all active:scale-[0.98]">
            <div>
                <div class="font-bold text-sm text-gray-800" x-text="v.n"></div>
                <div class="text-xs text-gray-500">Unit Qty: <span x-text="v.q"></span></div>
            </div>
            <div class="font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded">₦<span x-text="format(v.p)"></span></div>
          </button>
        </template>
      </div>
    </div>
  </div>

  <!-- RECEIPT MODAL -->
  <div x-data="receiptModal()" x-show="show" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg h-[85vh] flex flex-col overflow-hidden animate-fade-in" @click.away="close">
      <div class="flex justify-between items-center p-3 border-b bg-gray-50">
        <h3 class="font-bold text-gray-700 flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> Transaction Complete</h3>
        <button @click="close" class="w-8 h-8 flex items-center justify-center hover:bg-gray-200 rounded-full text-gray-500 transition-colors"><i class="fas fa-times"></i></button>
      </div>
      <div class="flex-1 bg-gray-200 relative p-4 flex justify-center">
        <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center z-10 bg-white/90">
            <i class="fas fa-circle-notch animate-spin text-4xl text-purple-600 mb-3"></i><p class="text-sm text-gray-500 font-medium">Generating Receipt...</p>
        </div>
        <!-- Paper Shadow Effect -->
        <iframe x-ref="receiptFrame" class="w-full h-full border-0 bg-white shadow-lg max-w-[380px]" @load="stopLoading()"></iframe>
      </div>
      <div class="p-4 bg-white border-t border-gray-200 grid grid-cols-2 gap-3">
        <button @click="print()" :disabled="loading" class="flex items-center justify-center py-3 rounded-lg bg-gray-100 text-gray-800 font-bold hover:bg-gray-200 border border-gray-300 transition-all disabled:opacity-50"><i class="fas fa-print mr-2"></i> Print</button>
        <button @click="newOrder()" class="flex items-center justify-center py-3 rounded-lg bg-purple-600 text-white font-bold hover:bg-purple-700 shadow-md transition-all active:scale-95"><i class="fas fa-plus-circle mr-2"></i> New Order</button>
      </div>
    </div>
  </div>

  <!-- NOTIFICATIONS -->
  <div x-data="{ notifications: [] }" x-init="
    window.addEventListener('notify', e => {
      const id = Date.now() + Math.random();
      const type = e.detail.type || 'info';
      notifications.push({id, message: e.detail.message, type});
      // Auto dismiss
      setTimeout(()=> notifications = notifications.filter(n=>n.id !== id), 5000);
    });
  " class="fixed right-4 bottom-4 flex flex-col gap-2 z-[100] max-w-xs w-full pointer-events-none">
    <template x-for="n in notifications" :key="n.id">
      <div class="pointer-events-auto px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transform transition-all animate-fade-in border-l-4" 
           :class="{
             'bg-white border-green-500 text-gray-800': n.type === 'success',
             'bg-white border-red-500 text-gray-800': n.type === 'error',
             'bg-white border-yellow-500 text-gray-800': n.type === 'warning',
             'bg-gray-800 border-gray-600 text-white': n.type === 'info'
           }">
        <i class="fas" :class="{
             'fa-check-circle text-green-500': n.type === 'success',
             'fa-exclamation-circle text-red-500': n.type === 'error',
             'fa-exclamation-triangle text-yellow-500': n.type === 'warning',
             'fa-info-circle text-blue-400': n.type === 'info'
        }"></i>
        <div class="text-sm font-medium" x-text="n.message"></div>
      </div>
    </template>
  </div>

  <script>
    /* --------------------
       Globals & Config
       -------------------- */
    const rawData = @json($preparedProducts);
    const STORE_ID = "{{ $storeId }}";
    const BASE_URL = "{{ url('/') }}";
    const CHECKOUT_ROUTE = "{{ route('checkout.process') }}";
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    const formatter = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    // Audio Context Singleton (Prevent memory leaks)
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    let audioCtx = new AudioContext();

    /* --------------------
       Helpers
       -------------------- */
    function money(n){ return formatter.format(n); }
    
    // Floating point safe math for totals
    function precisionRound(number, precision = 2) {
      const factor = Math.pow(10, precision);
      return Math.round(number * factor) / factor;
    }

    function playBeep() {
      if (audioCtx.state === 'suspended') audioCtx.resume();
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.frequency.value = 850;
      gain.gain.value = 0.05;
      osc.start();
      setTimeout(() => {
          // Fade out to avoid clicking sound
          gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 0.04);
          osc.stop(audioCtx.currentTime + 0.05);
      }, 80);
    }

    // Time Display
    setInterval(() => {
      const now = new Date();
      const el = document.getElementById('clock-display');
      if(el) el.innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }, 1000);

    /* --------------------
       Cart Logic
       -------------------- */
    function cartSidebar(){
      return {
        sessions: {},
        activeTab: null,
        loading: false,

        init(){
          this.load();
          window.addEventListener('add', e => this.add(e.detail.p, e.detail.v));
        },

        load(){
          const saved = localStorage.getItem('pos_sessions');
          if(saved) {
            try { this.sessions = JSON.parse(saved); } catch(e){ this.sessions = {}; }
          }
          if(Object.keys(this.sessions).length === 0) this.createTab();
          
          // Ensure active tab exists
          if(!this.activeTab || !this.sessions[this.activeTab]){
              const keys = Object.keys(this.sessions);
              this.activeTab = keys[keys.length-1];
          }
        },

        save(){ localStorage.setItem('pos_sessions', JSON.stringify(this.sessions)); },

        createTab(){
          const id = 'tab_' + Date.now();
          this.sessions[id] = { number: Object.keys(this.sessions).length + 1, items: {} };
          this.activeTab = id;
          this.save();
        },

        get tabIds(){ return Object.keys(this.sessions).reduce((acc, k) => (acc[k] = this.sessions[k], acc), {}); },
        get tabCount(){ return Object.keys(this.sessions).length; },

        switchTab(id){ if(this.sessions[id]) this.activeTab = id; },

        closeTab(id){
          delete this.sessions[id];
          if(Object.keys(this.sessions).length === 0) this.createTab();
          else this.activeTab = Object.keys(this.sessions)[0];
          this.save();
        },

        get currentItems(){ return this.sessions[this.activeTab]?.items || {}; },
        get count(){ return Object.keys(this.currentItems).length; },
        get total(){ 
          const sum = Object.values(this.currentItems).reduce((acc, i) => acc + (i.p * i.qty), 0);
          return money(precisionRound(sum)); 
        },

        format(n){ return money(n); },

        add(p, v = null){
          const vid = v ? v.id : 'base';
          const key = `${p.id}_${vid}`;
          const items = this.sessions[this.activeTab].items;
          
          if(items[key]) {
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
          playBeep();
        },

        remove(k){ delete this.sessions[this.activeTab].items[k]; this.save(); },

        mod(k, n){
          const i = this.sessions[this.activeTab].items;
          if(i[k]) {
            i[k].qty += n;
            if(i[k].qty < 1) {
              if(confirm('Remove this item from cart?')) delete i[k];
              else i[k].qty = 1;
            }
            this.save();
          }
        },

        check(k){ const i = this.sessions[this.activeTab].items; if(i[k] && (i[k].qty < 1 || isNaN(i[k].qty))) { i[k].qty = 1; this.save(); } },

        clear(){ if(confirm('Clear current cart?')){ this.sessions[this.activeTab].items = {}; this.save(); } },

        scrollToBottom(){ setTimeout(()=>{ const el = document.getElementById('cart-container'); if(el) el.scrollTop = el.scrollHeight; }, 50); },

        async pay(method){
          if(this.count === 0) return;
          this.loading = true;
          
          const cartItems = Object.values(this.currentItems).map(i => ({ 
              product_id: i.id, 
              variant_id: i.vid, 
              quantity: i.qty, 
              price: i.p 
          }));

          const payload = {
            id: 'ord_' + Date.now(), // Local ID for queue tracking
            store_id: STORE_ID,
            paymentMethod: method,
            cart: cartItems,
            total: this.total.replace(/,/g, ''), // Send raw number
            timestamp: Date.now()
          };

          // 1. Offline Mode Check
          if(!navigator.onLine){
            window.dispatchEvent(new CustomEvent('queue-order', { detail: payload }));
            this.finishOrderLocal(true);
            return;
          }

          // 2. Online Request
          try {
            const res = await fetch(CHECKOUT_ROUTE, {
              method: 'POST',
              headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
              body: JSON.stringify(payload)
            });
            
            const data = await res.json();
            
            if(!res.ok) throw new Error(data.message || 'Transaction failed');
            
            // Success
            this.finishOrderLocal(false);
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: '✅ Transaction Successful', type: 'success' } }));
            
            // Open Receipt (using server ID)
            if(data.order_id) {
                window.dispatchEvent(new CustomEvent('open-receipt-modal', { detail: { url: `/receipt/${data.order_id}` } }));
            }

          } catch(e) {
            console.error(e);
            // Fallback to queue if it's a network error, NOT a logic error
            // Note: simple try/catch catches everything, ideally check if it's a fetch error
            window.dispatchEvent(new CustomEvent('queue-order', { detail: payload }));
            this.finishOrderLocal(true);
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: '⚠️ Network issue. Saved to offline queue.', type: 'warning' } }));
          }
        },

        finishOrderLocal(isOffline){
            this.sessions[this.activeTab].items = {};
            this.save();
            this.loading = false;
        }
      };
    }

    /* --------------------
       Product Grid
       -------------------- */
    function productGrid(){
      return {
        all: rawData, // already mapped in PHP
        search: '',
        displayLimit: 24,
        lastReceiptUrl: localStorage.getItem('last_receipt_url'),

        init(){ 
            window.addEventListener('open-receipt-modal', e => {
                this.lastReceiptUrl = e.detail.url;
                localStorage.setItem('last_receipt_url', e.detail.url);
            });
            this.$watch('search', () => { 
                this.displayLimit = 24; 
                document.getElementById('product-scroll-area').scrollTop = 0; 
            }); 
        },

        get filtered(){
          const s = this.search.toLowerCase().trim();
          if(!s) return this.all;
          // Performance: use a simple loop or filter
          return this.all.filter(x => 
              x.n.toLowerCase().includes(s) || 
              (x.b && x.b.toLowerCase().includes(s))
          );
        },

        get visibleProducts(){ return this.filtered.slice(0, this.displayLimit); },
        get filteredCount(){ return this.filtered.length; },
        get hasMore(){ return this.displayLimit < this.filtered.length; },

        loadMore(){ this.displayLimit += 24; },
        
        enterSearch(){
          // Barcode scanner helper: if exact match found, add immediately and clear search
          const exact = this.all.find(p => p.b === this.search);
          if(exact){ 
            this.click(exact); 
            this.search=''; 
            // Keep focus on search input for next scan
          }
        },

        format(n){ return money(n); },

        click(p){
          if(p.v && p.v.length > 0) window.dispatchEvent(new CustomEvent('var', { detail: p }));
          else window.dispatchEvent(new CustomEvent('add', { detail: { p } }));
        }
      };
    }

    /* --------------------
       Variant Modal
       -------------------- */
    function variantModal(){
      return {
        visible: false,
        product: null,
        init(){
          window.addEventListener('var', e => { this.product = e.detail; this.visible = true; });
        },
        close(){ this.visible = false; setTimeout(() => this.product = null, 200); },
        select(v){
          window.dispatchEvent(new CustomEvent('add', { detail: { p: this.product, v } }));
          this.close();
        },
        format(n){ return money(n); }
      };
    }

    /* --------------------
       Receipt Modal
       -------------------- */
    function receiptModal(){
      return {
        show: false,
        loading: true,
        init(){
          window.addEventListener('open-receipt-modal', e => this.fetchReceipt(e.detail.url));
        },
        async fetchReceipt(url){
          this.show = true; this.loading = true;
          try {
            const r = await fetch(url);
            if(!r.ok) throw new Error('Receipt load failed');
            let html = await r.text();
            
            // Inject styles for print friendliness if simpler receipt
            // Ensure base href is set so relative images/css work
            if(!html.includes('<base')) {
                html = html.replace('<head>', `<head><base href="${BASE_URL}/">`);
            }
            
            this.$refs.receiptFrame.srcdoc = html;
          } catch(e){
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Failed to load receipt visual', type: 'error' } }));
            this.loading = false;
          }
        },
        stopLoading(){ this.loading = false; },
        close(){ this.show = false; this.$refs.receiptFrame.srcdoc = ''; },
        newOrder(){ this.close(); },
        print(){ 
            const f = this.$refs.receiptFrame; 
            if(f && f.contentWindow){ 
                f.contentWindow.focus(); 
                f.contentWindow.print(); 
            } 
        }
      };
    }

    /* --------------------
       Sync Manager (Robust)
       -------------------- */
    function syncManager(){
      return {
        online: navigator.onLine,
        queue: [],
        failedQueue: [],
        syncing: false,

        init(){
          // Load queues
          try { this.queue = JSON.parse(localStorage.getItem('pos_queue') || '[]'); } catch(e){ this.queue = []; }
          try { this.failedQueue = JSON.parse(localStorage.getItem('pos_failed_queue') || '[]'); } catch(e){ this.failedQueue = []; }

          // Network Listeners
          window.addEventListener('online', () => { 
              this.online = true; 
              window.dispatchEvent(new CustomEvent('notify',{detail:{message:'🌐 Online: Syncing pending orders...', type:'info'}})); 
              this.processQueue(); 
          });
          window.addEventListener('offline', () => { 
              this.online = false; 
              window.dispatchEvent(new CustomEvent('notify',{detail:{message:'📡 You are offline', type:'warning'}})); 
          });

          window.addEventListener('queue-order', e => { 
              this.queue.push(e.detail); 
              this.saveQueue(); 
          });

          // Attempt sync on load
          if(this.online && this.queue.length > 0) setTimeout(()=> this.processQueue(), 20);
        },

        get queueCount(){ return this.queue.length; },

        saveQueue(){ localStorage.setItem('pos_queue', JSON.stringify(this.queue)); },
        saveFailed(){ localStorage.setItem('pos_failed_queue', JSON.stringify(this.failedQueue)); },

        forceSync(){
          if(!this.online){ 
              window.dispatchEvent(new CustomEvent('notify',{detail:{message:'Still offline. Cannot sync.', type:'error'}})); 
              return; 
          }
          this.processQueue();
        },

        async processQueue() {
    if (!this.online || this.syncing || this.queue.length === 0) return;

    this.syncing = true;

    const payload = this.queue[0]; // first in line

    try {
        const res = await fetch(CHECKOUT_ROUTE, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        });

        let data = null;

        // Try to parse JSON safely
        try {
            data = await res.clone().json();
        } catch (_) {
            data = null; // server returned HTML or empty response
        }

        if (res.ok) {
            // ✔ SUCCESS
            this.queue.shift();
            this.saveQueue();

            window.dispatchEvent(new CustomEvent("notify", {
                detail: { message: "☁️ Synced", type: "success" }
            }));

            this.syncing = false;
            this.processQueue(); // process next
            return;
        }

        // ❗ VALIDATION FAILURE (422)
        if (res.status === 422) {
            this.queue.shift();
            this.failedQueue.push({ ...payload, error: data?.message || "Validation failed" });
            this.saveQueue();
            this.saveFailed();

            window.dispatchEvent(new CustomEvent("notify", {
                detail: { message: "❌ Order rejected by server", type: "error" }
            }));

            this.syncing = false;
            this.processQueue();
            return;
        }

        // ❗ SERVER ERROR (500/503/404)
        // Leave item in queue — but DO NOT retry immediately
        window.dispatchEvent(new CustomEvent("notify", {
            detail: { message: "⚠️ Server error. Will retry later.", type: "warning" }
        }));

        // Delay retries to prevent infinite loop hammering
        setTimeout(() => {
            this.syncing = false;
        }, 5000);

    } catch (e) {
        // ❗ Network exception (offline or DNS)
        console.error("Network error during sync:", e);

        window.dispatchEvent(new CustomEvent("notify", {
            detail: { message: "📡 Network error. Queue preserved.", type: "warning" }
        }));

        this.syncing = false;
    }
}

      };
    }

    /* --------------------
       Keybindings
       -------------------- */
    document.addEventListener('alpine:init', () => {
  
  Alpine.store('shortcuts', {
    init() {
      window.addEventListener('keydown', (e) => {
        const mode = Alpine.store('mode').value;
        const grid = Alpine.store('grid');
        const cart = Alpine.store('cart');

        // Global prevent overrides for specific keys
        const blockKeys = ['ArrowUp','ArrowDown','Enter','Delete','Backspace','+','-','/'];
        if (blockKeys.includes(e.key)) e.preventDefault();

        // --- F2 Search Focus ---
        if (e.key === 'F2') {
          e.preventDefault();
          const el = document.getElementById('product-search');
          if (el) {
            el.focus();
            el.select();
          }
          return; // stop other handlers
        }

        // All other actions
        const actions = {
          'Tab': () => Alpine.store('mode').toggle(),

          'ArrowDown': () => mode === 'products'
            ? grid?.moveSelection(1)
            : cart?.moveSelection(1),

          'ArrowUp': () => mode === 'products'
            ? grid?.moveSelection(-1)
            : cart?.moveSelection(-1),

          'Enter': () => mode === 'products'
            ? grid?.addActive()
            : cart?.removeActive(),

          '+': () => mode === 'cart' && cart?.increaseQty(),
          '-': () => mode === 'cart' && cart?.decreaseQty(),

          'Delete': () => mode === 'cart' && cart?.removeActive(),
          'Backspace': () => mode === 'cart' && cart?.removeActive(),

          'c': () => cart?.clearCart(),
          'C': () => cart?.clearCart(),

          '1': () => cart?.checkout('cash'),
          '2': () => cart?.checkout('bank'),
          '3': () => cart?.checkout('pos'),

          '/': () => {
            const search = document.querySelector('[x-data="ProductGrid"] input[type="text"]');
            search?.focus();
          },

          'p': () => Alpine.store('receiptModal').reprint(),
          'P': () => Alpine.store('receiptModal').reprint(),
        };

        if (actions[e.key]) actions[e.key]();
      });
    }
  });

});
  </script>
</body>
</html>