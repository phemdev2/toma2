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
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
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
  $storeId = $user->store_id ?? 0;
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

<body class="bg-slate-100 font-sans text-gray-800 h-screen overflow-hidden selection:bg-purple-200">

  <!-- Network Status Indicator -->
  <div x-data="{ online: navigator.onLine }" 
       x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false);"
       x-show="!online" x-cloak
       class="fixed top-0 inset-x-0 h-6 bg-red-500 text-white text-xs font-bold flex items-center justify-center z-[60] shadow-md">
       <i class="fas fa-wifi-slash mr-2"></i> OFFLINE MODE - CHECK CONNECTION
  </div>
<!-- Enhanced Navbar -->
      <nav class="glass border-b border-gray-200/50 shadow-sm sticky top-0 z-30">
        <div class="px-6 py-4">
          <div class="flex justify-between items-center">
            <!-- Logo & Mode -->
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <div
                  class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                  <i class="fas fa-store"></i>
                </div>
                <div>
                  <span
                    class="text-lg font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">ModernPOS</span>
                  <span class="ml-2 px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700 font-semibold">
                    <i class="fas fa-layer-group mr-1"></i>
                    <span x-text="$store.mode.current.toUpperCase()"></span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Navigation -->
            <ul class="hidden lg:flex gap-1">
              <li>
                <a href="#"
                  class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700 flex items-center gap-2">
                  <i class="fas fa-home"></i> Dashboard
                </a>
              </li>
              <li>
                <a href="#"
                  class="px-4 py-2 rounded-lg text-sm font-medium bg-purple-100 text-purple-700 flex items-center gap-2">
                  <i class="fas fa-shopping-bag"></i> POS
                </a>
              </li>
              <li>
                <a href="#"
                  class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700 flex items-center gap-2">
                  <i class="fas fa-chart-line"></i> Reports
                </a>
              </li>
              <li>
                <a href="#"
                  class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700 flex items-center gap-2">
                  <i class="fas fa-cog"></i> Settings
                </a>
              </li>
            </ul>

            <!-- User Info -->
            <div class="flex items-center gap-3">
              <div class="hidden md:flex items-center gap-3 text-xs">
                <!-- Store Name -->
                <div class="px-3 py-1.5 rounded-lg bg-gray-100 font-medium flex items-center gap-2">
                  <i class="fas fa-store mr-1 text-gray-500"></i>
                  {{ Auth::user()->store->name }}
                </div>

                <!-- User Avatar + Name -->
                <div class="flex items-center gap-2">
                  <!-- Avatar with initial -->
                  <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 
                flex items-center justify-center text-white font-semibold text-sm">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                  </div>
                  <span class="font-medium">{{ Auth::user()->name }}</span>
                </div>
              </div>


              <!-- Quick Actions -->
              <div class="flex items-center gap-2">
                <button @click="$store.receiptModal.reprint()"
                  class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-600">
                  <i class="fas fa-print"></i>
                </button>
                <button
                  class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-600 relative">
                  <i class="fas fa-bell"></i>
                  <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </nav>
  <!-- Layout Wrapper -->
  <div class="flex w-full h-full pt-0">

    <!-- LEFT: Cart Sidebar -->
    <div x-data="CartSidebar" class="glass border-r border-gray-200/80 shadow-2xl z-40 w-full lg:w-[450px] flex flex-col h-full relative">
      
      <!-- Cart Header -->
      <div class="p-4 bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-md z-10">
        <div class="flex justify-between items-center mb-2">
          <h2 class="text-lg font-bold flex items-center gap-2">
            <i class="fas fa-shopping-cart"></i> Current Sale
            <span x-show="count > 0" class="bg-white/20 text-xs px-2 py-0.5 rounded-full" x-text="count"></span>
          </h2>
          <button @click="clear()" x-show="count > 0" 
                  class="text-xs bg-red-500/20 hover:bg-red-500/40 px-3 py-1.5 rounded transition-colors border border-red-400/30">
            <i class="fas fa-trash-alt mr-1"></i> Clear
          </button>
        </div>
        <div class="flex justify-between text-xs opacity-90 font-mono">
          <span><i class="fas fa-store mr-1"></i> {{ $storeId }}</span>
          <span>{{ substr(auth()->user()->name, 0, 15) }}</span>
        </div>
      </div>

      <!-- Cart Items List -->
      <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar bg-slate-50" id="cart-container">
        
        <!-- Empty State -->
        <template x-if="count === 0">
          <div class="h-full flex flex-col items-center justify-center text-gray-400">
            <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mb-4">
              <i class="fas fa-basket-shopping text-3xl opacity-50"></i>
            </div>
            <p class="font-medium">Cart is empty</p>
            <p class="text-sm">Scan barcode or select product</p>
          </div>
        </template>

        <!-- Items -->
        <template x-for="(item, key) in cart" :key="key">
          <div class="group bg-white p-3 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all animate-slide-up relative">
            <div class="flex justify-between items-start gap-2">
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-sm text-gray-800 truncate" x-text="item.n"></h4>
                <div class="flex items-center gap-2 mt-1">
                  <span x-show="item.v_name" class="text-[10px] bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded border border-indigo-100 font-semibold" x-text="item.v_name"></span>
                  <span class="text-xs text-gray-500 font-mono">@ ₦<span x-text="format(item.p)"></span></span>
                </div>
              </div>
              <div class="text-right">
                <p class="font-bold text-purple-700">₦<span x-text="format(item.p * item.qty)"></span></p>
              </div>
            </div>

            <!-- Controls -->
            <div class="flex justify-between items-end mt-2 pt-2 border-t border-dashed border-gray-100">
               <button @click="remove(key)" class="text-red-400 hover:text-red-600 p-1" title="Remove Item">
                 <i class="fas fa-trash-alt"></i>
               </button>
               
               <div class="flex items-center bg-gray-100 rounded-md shadow-inner">
                 <button @click="mod(key, -1)" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded-l-md active:bg-gray-300">
                   <i class="fas fa-minus text-xs"></i>
                 </button>
                 <input type="number" x-model.number="item.qty" @change="check(key)" 
                        class="w-12 h-8 text-center bg-transparent text-sm font-bold focus:outline-none focus:bg-white transition-colors" />
                 <button @click="mod(key, 1)" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded-r-md active:bg-gray-300">
                   <i class="fas fa-plus text-xs"></i>
                 </button>
               </div>
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
          
          <button @click="pay('bank')" :disabled="count === 0 || loading"
            class="flex flex-col items-center justify-center py-3 px-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg shadow transition-all active:scale-95">
            <i x-show="!loading" class="fas fa-university mb-1 text-lg"></i>
            <i x-show="loading" class="fas fa-circle-notch animate-spin mb-1 text-lg"></i>
            <span class="text-xs font-bold uppercase">Transfer</span>
          </button>
        </div>
      </div>
    </div>

    <!-- RIGHT: Product Grid (Lazy Loading) -->
    <div x-data="ProductGrid" class="flex-1 flex flex-col h-full bg-slate-100 overflow-hidden">
      
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
           <!-- Using x-text prevents Blade PHP errors -->
           <div class="hidden md:flex flex-col items-end mr-2">
             <span class="text-xs text-gray-500"><span x-text="filteredCount"></span> items found</span>
           </div>
           
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
        <!-- Loader (Shows while fetching HTML) -->
        <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center z-10 bg-white">
            <i class="fas fa-circle-notch animate-spin text-4xl text-purple-600 mb-3"></i>
            <p class="text-sm text-gray-500 font-medium">Loading Receipt...</p>
        </div>
        
        <!-- 
            We use 'x-ref' to target this iframe in JS.
            We use 'srcdoc' (populated via JS) to render content instantly without a second server round-trip.
        -->
        <iframe x-ref="receiptFrame" 
                class="w-full h-full border-0 bg-white" 
                @load="stopLoading()"></iframe>
      </div>

      <!-- Footer Actions -->
      <div class="p-4 bg-white border-t border-gray-200 grid grid-cols-2 gap-3">
        <!-- Print Button -->
        <button @click="print()" 
                :disabled="loading"
                class="flex items-center justify-center py-3 rounded-lg bg-gray-100 text-gray-800 font-bold hover:bg-gray-200 border border-gray-300 transition-all disabled:opacity-50">
            <i class="fas fa-print mr-2"></i> Print
        </button>

        <!-- New Order Button -->
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
    const formatter = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    // Get the site base URL for relative assets in receipt
    const BASE_URL = "{{ url('/') }}";

    document.addEventListener('alpine:init', () => {

        // --- CART LOGIC ---
        Alpine.data('CartSidebar', () => ({
            cart: {},
            loading: false,
            
            init() {
                this.cart = JSON.parse(localStorage.getItem('pos_cart') || '{}');
                window.addEventListener('add', e => this.add(e.detail.p, e.detail.v));
            },
            
            get count() { return Object.keys(this.cart).length; },
            
            get total() { 
                return formatter.format(Object.values(this.cart).reduce((sum, i) => sum + (i.p * i.qty), 0));
            },

            format(n) { return formatter.format(n); },

            add(p, v = null) {
                const vid = v ? v.id : 'base';
                const key = `${p.id}_${vid}`;
                
                if (this.cart[key]) {
                    this.cart[key].qty++;
                } else {
                    this.cart[key] = {
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

            remove(key) { delete this.cart[key]; this.save(); },
            
            mod(key, n) {
                if(!this.cart[key]) return;
                this.cart[key].qty += n;
                if(this.cart[key].qty < 1) {
                    if(confirm('Remove item from cart?')) delete this.cart[key];
                    else this.cart[key].qty = 1;
                }
                this.save();
            },

            check(k) { 
                if(!this.cart[k]) return;
                if(this.cart[k].qty < 1) this.cart[k].qty = 1; 
                this.save(); 
            },

            clear() { 
                if(confirm('Clear entire cart?')) { this.cart = {}; this.save(); } 
            },

            save() { localStorage.setItem('pos_cart', JSON.stringify(this.cart)); },

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
                    cart: Object.values(this.cart).map(i => ({
                        product_id: i.id,
                        variant_id: i.vid,
                        quantity: i.qty,
                        price: i.p
                    }))
                };

                try {
                    const res = await fetch('{{ route("checkout.process") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if(!res.ok) throw new Error(data.message || 'Validation Failed');

                    // Success
                    this.cart = {}; 
                    this.save();
                    
                    // Trigger Receipt Load
                    const receiptUrl = `/receipt/${data.order_id}`; 
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
                this.$watch('search', () => {
                    this.displayLimit = 24; 
                    document.getElementById('product-scroll-area').scrollTop = 0;
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

        // --- RECEIPT MODAL (IMPROVED) ---
        Alpine.data('ReceiptModal', () => ({
            show: false,
            loading: true,

            init() {
                // Listen for event to fetch receipt
                window.addEventListener('open-receipt-modal', (e) => {
                    this.fetchReceipt(e.detail.url);
                });
            },

            async fetchReceipt(url) {
                this.show = true;
                this.loading = true;
                
                try {
                    // Fetch HTML text directly
                    const response = await fetch(url);
                    if(!response.ok) throw new Error('Failed to load');
                    
                    let html = await response.text();

                    // Inject <base> tag so relative CSS/Images in receipt work
                    if(!html.includes('<base')) {
                        html = html.replace('<head>', `<head><base href="${BASE_URL}/">`);
                    }

                    // Inject into iframe
                    const frame = this.$refs.receiptFrame;
                    // srcdoc allows instant rendering without new network request context
                    frame.srcdoc = html;
                    
                    // Wait slightly for rendering
                    setTimeout(() => { this.loading = false; }, 500);

                } catch(error) {
                    console.error(error);
                    alert("Could not load receipt.");
                    this.show = false;
                }
            },

            stopLoading() {
                this.loading = false;
            },

            close() {
                this.show = false;
                this.$refs.receiptFrame.srcdoc = ''; // Clear memory
            },

            newOrder() {
                this.close();
                // Cart is already cleared
            },

            print() {
                const frame = this.$refs.receiptFrame;
                if(frame && frame.contentWindow) {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                }
            }
        }));

        // --- VARIANT MODAL ---
        Alpine.data('VariantModal', () => ({
            show: false,
            p: null,
            init() { window.addEventListener('var', e => { this.p = e.detail; this.show = true; }); },
            close() { this.show = false; },
            sel(v) {
                window.dispatchEvent(new CustomEvent('add', { detail: { p: this.p, v: v } }));
                this.close();
            },
            format(n) { return formatter.format(n); }
        }));
    });
  </script>
</body>
</html>