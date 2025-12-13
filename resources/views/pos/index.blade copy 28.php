<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
  <title>POS - {{ auth()->user()->store->name ?? 'Store' }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  
  <!-- Preload Fonts & Styles -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Fira+Code:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

  <style>
    /* Performance optimizations */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    [x-cloak] { display: none !important; }
    .animate-fade-in { animation: fadeIn 0.15s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
  </style>
</head>

@php
  // 1. Setup User & Store Info
  $user = auth()->user();
  $store = $user->store ?? null;
  $storeId = $store ? $store->id : 0;
  $storeName = $store ? $store->name : 'My Store';
  
  // 2. Prepare Products (Fail-safe)
  // We use the null coalescing operator (??) to prevent errors if $products is not passed from the controller
  $rawProducts = $products ?? collect();

  $preparedProducts = $rawProducts->map(function ($product) use ($storeId) {
      // Calculate stock specific to this store (assuming relationship exists)
      $stock = 0;
      if ($product->storeInventories) {
          $stock = (int) $product->storeInventories
                          ->where('store_id', $storeId)
                          ->sum('quantity');
      } else {
          // Fallback if no relationship loaded
          $stock = $product->quantity ?? 0;
      }

      // Map variants to a lightweight array
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
  })
  ->sortByDesc('s') // Sort by stock level (high to low)
  ->values()
  ->all();
@endphp

<body class="bg-slate-100 font-sans text-gray-800 h-screen overflow-hidden flex flex-col" 
      x-data="app()" 
      @keydown.window="handleGlobalKeys($event)">

  <!-- TOP NAV -->
  <nav class="bg-slate-900 text-white h-12 flex-none z-50 shadow-md flex justify-between items-center px-4">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded bg-purple-600 flex items-center justify-center font-bold text-lg">P</div>
      <span class="font-bold text-lg hidden md:block">POS System</span>
    </div>

    <!-- Status Bar -->
    <div class="flex items-center gap-4 text-xs" x-data="syncManager()">
      <div class="text-gray-400 font-mono hidden sm:block" x-text="time"></div>
      
      <!-- Queue Indicator -->
      <div x-show="queueCount > 0" x-cloak class="flex items-center gap-2 px-2 py-1 rounded bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 cursor-pointer" @click="process" title="Click to force sync">
         <i class="fas fa-sync" :class="syncing ? 'fa-spin' : ''"></i>
         <span x-text="queueCount + ' Pending'"></span>
      </div>

      <div class="flex items-center gap-2">
         <div class="w-2 h-2 rounded-full" :class="online ? 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]' : 'bg-red-500'"></div>
         <span x-text="online ? 'Online' : 'Offline'"></span>
      </div>
    </div>
  </nav>

  <div class="flex-1 flex overflow-hidden">

    <!-- LEFT: CART SIDEBAR -->
    <div x-data="cartSidebar()" class="bg-white border-r border-gray-200 shadow-xl z-40 w-full md:w-[400px] flex flex-col h-full">
      
      <!-- Order Tabs -->
      <div class="flex bg-gray-50 border-b overflow-x-auto custom-scrollbar h-10 flex-none">
        <template x-for="(session, id) in sessions" :key="id">
          <div @click="switchTab(id)" 
               class="px-4 flex items-center justify-between min-w-[100px] cursor-pointer text-xs font-bold border-r select-none"
               :class="activeTab === id ? 'bg-white text-purple-700 border-t-2 border-t-purple-600' : 'text-gray-500 hover:bg-gray-100'">
            <span x-text="`Order ${session.number}`"></span>
            <button x-show="Object.keys(sessions).length > 1" @click.stop="closeTab(id)" class="ml-2 text-gray-400 hover:text-red-500">&times;</button>
          </div>
        </template>
        <button @click="createTab" class="px-3 hover:bg-purple-100 text-purple-600"><i class="fas fa-plus"></i></button>
      </div>

      <!-- Cart Header -->
      <div class="p-3 bg-white border-b shadow-sm flex-none">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-gray-700 text-sm"><i class="fas fa-shopping-cart mr-1"></i> Current Order</h2>
            <button @click="clear" x-show="count > 0" class="text-xs text-red-500 font-semibold hover:underline">Clear (Del)</button>
        </div>
      </div>

      <!-- Cart Items -->
      <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar bg-slate-50" id="cart-container">
        <template x-if="count === 0">
          <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-60">
            <i class="fas fa-barcode text-6xl mb-4"></i>
            <p class="text-sm">Scan barcode or select item</p>
          </div>
        </template>

        <template x-for="(item, key) in currentItems" :key="key">
          <div class="bg-white p-2 rounded shadow-sm border border-gray-100 flex justify-between group">
            <div class="flex-1">
              <div class="text-sm font-bold text-gray-800 leading-tight" x-text="item.n"></div>
              <div class="text-[10px] text-gray-500 mt-1">
                 <span x-show="item.v_name" class="bg-indigo-50 text-indigo-700 px-1 rounded mr-1" x-text="item.v_name"></span>
                 <span x-text="`${item.qty} x ₦${format(item.p)}`"></span>
              </div>
            </div>
            
            <div class="flex flex-col items-end justify-between">
               <span class="font-bold text-gray-900">₦<span x-text="format(item.p * item.qty)"></span></span>
               
               <div class="flex items-center bg-gray-100 rounded mt-1">
                 <button @click="mod(key, -1)" class="w-6 h-6 hover:bg-gray-200 rounded text-gray-600">-</button>
                 <span class="w-6 text-center text-xs font-bold" x-text="item.qty"></span>
                 <button @click="mod(key, 1)" class="w-6 h-6 hover:bg-gray-200 rounded text-gray-600">+</button>
               </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Footer & Checkout -->
      <div class="p-3 bg-white border-t z-20 flex-none">
        <div class="flex justify-between items-end mb-3">
          <div class="text-xs text-gray-500 font-bold uppercase">Total Payable</div>
          <div class="text-3xl font-black text-gray-900 tracking-tight">₦<span x-text="total"></span></div>
        </div>
        
        <div class="grid grid-cols-3 gap-2 h-12">
          <button @click="pay('cash')" :disabled="count===0" class="bg-green-600 hover:bg-green-700 text-white rounded font-bold text-xs uppercase shadow active:scale-95 transition-transform flex flex-col items-center justify-center disabled:opacity-50">
            <span>Cash</span> <span class="text-[9px] opacity-70 hidden lg:inline">(F8)</span>
          </button>
          <button @click="pay('pos')" :disabled="count===0" class="bg-blue-600 hover:bg-blue-700 text-white rounded font-bold text-xs uppercase shadow active:scale-95 transition-transform flex flex-col items-center justify-center disabled:opacity-50">
            <span>POS</span> <span class="text-[9px] opacity-70 hidden lg:inline">(F9)</span>
          </button>
          <button @click="pay('bank')" :disabled="count===0" class="bg-purple-600 hover:bg-purple-700 text-white rounded font-bold text-xs uppercase shadow active:scale-95 transition-transform flex flex-col items-center justify-center disabled:opacity-50">
            <span>Transfer</span> <span class="text-[9px] opacity-70 hidden lg:inline">(F10)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- RIGHT: PRODUCT GRID -->
    <div x-data="productGrid()" class="flex-1 flex flex-col bg-slate-100 overflow-hidden">
      <!-- Search -->
      <div class="bg-white p-3 border-b shadow-sm flex items-center justify-between gap-4">
        <div class="relative w-full max-w-lg">
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input id="product-search" type="text" x-model="search" @input.debounce.150ms="updateFilter"
                 class="w-full pl-10 pr-4 py-2 bg-gray-100 border-transparent focus:bg-white focus:border-purple-500 rounded-lg text-sm transition-all focus:ring-0" 
                 placeholder="Search Name or Scan Barcode (F2)" 
                 @keydown.enter.prevent="enterSearch" />
        </div>
        <div class="text-xs text-gray-500 hidden md:block">
           <span class="font-bold text-gray-800" x-text="filteredCount"></span> Products
        </div>
      </div>

      <!-- Grid -->
      <div class="flex-1 overflow-y-auto p-4 custom-scrollbar" id="product-area">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 pb-20">
          <template x-for="p in visibleProducts" :key="p.id">
            <button @click="click(p)" class="bg-white p-3 rounded-lg border border-gray-200 hover:border-purple-500 hover:shadow-md transition-all text-left flex flex-col justify-between group h-28 relative overflow-hidden">
              
              <!-- Stock Indicator -->
              <div class="absolute top-0 right-0">
                  <span x-show="p.s <= 0" class="bg-red-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-bl">OUT</span>
                  <span x-show="p.s > 0 && p.s <= 5" class="bg-orange-400 text-white text-[9px] font-bold px-2 py-0.5 rounded-bl" x-text="p.s"></span>
              </div>

              <div>
                <div class="font-bold text-xs text-gray-800 line-clamp-2 leading-tight" x-text="p.n"></div>
                <div class="flex items-center gap-1 mt-1">
                   <span x-show="p.v.length" class="text-[9px] px-1 rounded bg-purple-100 text-purple-600 font-bold">Variants</span>
                   <span x-show="p.b" class="text-[9px] text-gray-400 font-mono" x-text="p.b"></span>
                </div>
              </div>
              <div class="flex justify-between items-end mt-2">
                 <span class="font-bold text-sm">₦<span x-text="format(p.p)"></span></span>
                 <div class="w-6 h-6 rounded-full bg-gray-50 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center transition-colors text-xs text-gray-400">
                    <i class="fas fa-plus"></i>
                 </div>
              </div>
            </button>
          </template>
        </div>
        
        <div x-show="visibleProducts.length < filteredCount" class="py-4 text-center">
            <button @click="limit += 24" class="text-xs font-bold text-purple-600 hover:underline">Load More Products</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODALS -->

  <!-- Variant Selector -->
  <div x-data="variantModal" x-show="show" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div @click.away="show=false" class="bg-white rounded-lg shadow-2xl w-80 overflow-hidden animate-fade-in">
        <div class="p-3 bg-gray-50 border-b flex justify-between items-center">
            <span class="font-bold text-sm truncate" x-text="product?.n"></span>
            <button @click="show=false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-2 space-y-1 max-h-80 overflow-y-auto">
            <template x-for="v in product?.v" :key="v.id">
                <button @click="select(v)" class="w-full flex justify-between items-center p-2 hover:bg-purple-50 rounded border border-transparent hover:border-purple-200 text-sm transition-colors">
                    <div class="text-left">
                        <div class="font-bold text-gray-800" x-text="v.n"></div>
                        <div class="text-xs text-gray-500" x-text="'Qty: '+v.q"></div>
                    </div>
                    <div class="font-bold text-purple-700 bg-purple-100 px-2 py-1 rounded">₦<span x-text="format(v.p)"></span></div>
                </button>
            </template>
        </div>
    </div>
  </div>

  <!-- Instant Receipt Preview -->
  <div x-data="receiptModal" x-show="show" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm flex flex-col h-[85vh]">
        <div class="p-3 border-b flex justify-between items-center bg-green-50">
            <h3 class="font-bold text-green-700 flex items-center gap-2"><i class="fas fa-check-circle"></i> Paid Successfully</h3>
            <button @click="close" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="flex-1 bg-gray-600 overflow-y-auto p-4 flex justify-center">
            <!-- Receipt Paper -->
            <div id="receipt-paper" class="bg-white w-[80mm] min-h-[100mm] shadow-lg p-4 text-[12px] font-mono leading-tight">
                <!-- Content injected via JS -->
            </div>
        </div>

        <div class="p-3 border-t bg-white grid grid-cols-2 gap-3">
            <button @click="print" class="py-3 bg-gray-800 text-white rounded font-bold hover:bg-black transition-colors"><i class="fas fa-print mr-2"></i> Print</button>
            <button @click="close" class="py-3 bg-gray-100 text-gray-800 rounded font-bold hover:bg-gray-200 transition-colors">Close (Esc)</button>
        </div>
    </div>
  </div>

  <!-- Notifications -->
  <div x-data="{ msgs: [] }" 
       @notify.window="msgs.push($event.detail); setTimeout(()=>msgs.shift(), 3000)"
       class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
    <template x-for="m in msgs">
       <div class="px-4 py-2 rounded shadow-lg text-sm animate-fade-in font-bold border-l-4"
            :class="m.includes('Error') ? 'bg-white text-red-600 border-red-500' : 'bg-gray-800 text-white border-gray-600'"
            x-text="m"></div>
    </template>
  </div>

  <script>
    // --- Configuration ---
    // Safely inject the prepared products from PHP
    const RAW_PRODUCTS = @json($preparedProducts);
    const STORE_INFO = { 
        name: "{{ $storeName }}", 
        id: "{{ $storeId }}",
        address: "{{ auth()->user()->store->address ?? '' }}", 
        phone: "{{ auth()->user()->store->phone ?? '' }}" 
    };
    const CURRENCY = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2 });
    const CHECKOUT_URL = "{{ route('checkout.process') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // --- Audio Context for Beeps ---
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    
    // --- Helpers ---
    const format = (n) => CURRENCY.format(n);
    
    const playBeep = () => {
        if(audioCtx.state === 'suspended') audioCtx.resume();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain); gain.connect(audioCtx.destination);
        osc.frequency.value = 800; 
        gain.gain.value = 0.03;
        osc.start(); 
        // Fade out
        gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 0.1);
        osc.stop(audioCtx.currentTime + 0.1);
    };

    function app() {
        return {
            time: '',
            init() {
                setInterval(() => this.time = new Date().toLocaleTimeString('en-US', {hour12:true, hour:'numeric', minute:'2-digit'}), 1000);
            },
            handleGlobalKeys(e) {
                // Prevent defaults for function keys
                if(['F2','F8','F9','F10'].includes(e.key)) e.preventDefault();
                
                if(e.key === 'F2') { 
                    const el = document.getElementById('product-search');
                    el.focus(); el.select(); 
                }
                if(e.key === 'F8') this.$dispatch('pay-request', 'cash');
                if(e.key === 'F9') this.$dispatch('pay-request', 'pos');
                if(e.key === 'F10') this.$dispatch('pay-request', 'bank');
                if(e.key === 'Escape') window.dispatchEvent(new CustomEvent('close-modals'));
                if(e.key === 'Delete') this.$dispatch('clear-cart');
            }
        }
    }

    // --- Product Grid Component ---
    function productGrid() {
        return {
            all: RAW_PRODUCTS,
            filtered: RAW_PRODUCTS,
            search: '',
            limit: 24,
            get visibleProducts() { return this.filtered.slice(0, this.limit); },
            get filteredCount() { return this.filtered.length; },
            
            updateFilter() {
                const s = this.search.toLowerCase().trim();
                this.limit = 24;
                if(!s) { this.filtered = this.all; return; }
                this.filtered = this.all.filter(p => p.n.toLowerCase().includes(s) || (p.b && p.b.includes(s)));
            },
            enterSearch() {
                const s = this.search.trim();
                if(!s) return;
                // Strict barcode match
                const exact = this.all.find(p => p.b === s);
                if(exact) { 
                    this.click(exact); 
                    this.search = ''; 
                    this.updateFilter();
                }
            },
            click(p) {
                if(p.v && p.v.length) window.dispatchEvent(new CustomEvent('open-variant', { detail: p }));
                else window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { p } }));
            },
            format
        }
    }

    // --- Cart Component ---
    function cartSidebar() {
        return {
            sessions: {},
            activeTab: null,
            init() {
                this.load();
                window.addEventListener('add-to-cart', e => this.add(e.detail.p, e.detail.v));
                window.addEventListener('pay-request', e => this.pay(e.detail));
                window.addEventListener('clear-cart', () => this.clear());
            },
            load() {
                const s = localStorage.getItem('pos_cart_v2');
                this.sessions = s ? JSON.parse(s) : { 'default': { number: 1, items: {} } };
                this.activeTab = Object.keys(this.sessions)[0] || 'default';
                if(!this.sessions[this.activeTab]) this.sessions[this.activeTab] = { number: 1, items: {} };
            },
            save() { localStorage.setItem('pos_cart_v2', JSON.stringify(this.sessions)); },
            
            // Tab Logic
            createTab() {
                const id = 't' + Date.now();
                this.sessions[id] = { number: Object.keys(this.sessions).length + 1, items: {} };
                this.activeTab = id;
                this.save();
            },
            closeTab(id) {
                delete this.sessions[id];
                if(Object.keys(this.sessions).length === 0) this.createTab();
                else this.activeTab = Object.keys(this.sessions)[0];
                this.save();
            },
            switchTab(id) { this.activeTab = id; },

            // Cart Logic
            get currentItems() { return this.sessions[this.activeTab]?.items || {}; },
            get count() { return Object.keys(this.currentItems).length; },
            get total() { 
                return format(Object.values(this.currentItems).reduce((a, i) => a + (i.p * i.qty), 0)); 
            },

            add(p, v=null) {
                playBeep();
                const vid = v ? v.id : 'base';
                const key = `${p.id}_${vid}`;
                const items = this.sessions[this.activeTab].items;
                
                if(items[key]) items[key].qty++;
                else items[key] = { 
                    id: p.id, n: p.n, p: v?v.p:p.p, v_name: v?v.n:null, vid: v?v.id:null, qty: 1 
                };
                this.save();
                
                // Scroll to bottom
                this.$nextTick(() => {
                    const el = document.getElementById('cart-container');
                    if(el) el.scrollTop = el.scrollHeight;
                });
            },
            mod(k, n) {
                const i = this.sessions[this.activeTab].items;
                if(i[k]) {
                    i[k].qty += n;
                    if(i[k].qty < 1) delete i[k];
                    this.save();
                }
            },
            clear() {
                if(confirm('Clear current order?')) {
                    this.sessions[this.activeTab].items = {};
                    this.save();
                }
            },

            // Checkout Logic
            pay(method) {
                if(this.count === 0) return;

                const orderData = {
                    id: 'ORD-' + Math.floor(Date.now() / 1000),
                    date: new Date().toLocaleString('en-NG'),
                    method: method,
                    items: Object.values(this.currentItems),
                    total: this.total,
                    store_id: STORE_INFO.id
                };

                // 1. Optimistic UI: Show Receipt immediately
                window.dispatchEvent(new CustomEvent('show-receipt', { detail: orderData }));

                // 2. Queue for Network
                window.dispatchEvent(new CustomEvent('queue-push', { detail: orderData }));
                
                // 3. Reset Cart
                this.sessions[this.activeTab].items = {};
                this.save();
            },
            format
        }
    }

    // --- Variant Modal ---
    const variantModal = {
        show: false,
        product: null,
        init() {
            window.addEventListener('open-variant', e => { this.product=e.detail; this.show=true; });
            window.addEventListener('close-modals', () => this.show=false);
        },
        select(v) {
            window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { p: this.product, v } }));
            this.show = false;
        },
        format
    };

    // --- Receipt Modal (Client Side Gen) ---
    const receiptModal = {
        show: false,
        data: null,
        init() {
            window.addEventListener('show-receipt', e => {
                this.data = e.detail;
                this.generateHtml();
                this.show = true;
            });
            window.addEventListener('close-modals', () => this.show=false);
        },
        generateHtml() {
            if(!this.data) return;
            // Generate thermal receipt structure
            const itemsHtml = this.data.items.map(i => `
                <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                    <span>${i.qty} x ${i.n} ${i.v_name ? '<br><small>('+i.v_name+')</small>' : ''}</span>
                    <span style="font-weight:bold">${format(i.p * i.qty)}</span>
                </div>
            `).join('');

            const html = `
                <div style="text-align:center; margin-bottom: 10px; padding-bottom:10px; border-bottom:1px dashed #000">
                    <h2 style="font-size:16px; font-weight:bold; margin:0; text-transform:uppercase">${STORE_INFO.name}</h2>
                    <p style="margin:2px 0; font-size:10px">${STORE_INFO.address}</p>
                    <p style="margin:0; font-size:10px">Tel: ${STORE_INFO.phone}</p>
                </div>
                <div style="margin-bottom:10px; font-size:10px;">
                    <div>Date: ${this.data.date}</div>
                    <div>Receipt: ${this.data.id}</div>
                    <div>Method: <span style="font-weight:bold; text-transform:uppercase">${this.data.method}</span></div>
                </div>
                <div style="margin-bottom:10px; border-bottom:1px dashed #000; padding-bottom:10px">
                    ${itemsHtml}
                </div>
                <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:bold;">
                    <span>TOTAL</span>
                    <span>₦${this.data.total}</span>
                </div>
                <div style="text-align:center; margin-top:15px; font-size:10px">
                    <p>Thank you for your patronage!</p>
                </div>
            `;
            document.getElementById('receipt-paper').innerHTML = html;
        },
        print() {
            const content = document.getElementById('receipt-paper').innerHTML;
            const win = window.open('','','height=600,width=400');
            win.document.write('<html><head><title>Print</title>');
            win.document.write('<style>body{font-family:monospace; font-size:12px; padding:0; margin:0} @page { size: auto; margin: 0mm; }</style>');
            win.document.write('</head><body>');
            win.document.write(content);
            win.document.write('</body></html>');
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); win.close(); }, 250);
        },
        close() { this.show = false; }
    };

    // --- Sync Manager (Background Process) ---
    function syncManager() {
        return {
            queue: [],
            online: navigator.onLine,
            syncing: false,
            init() {
                // Load queue safely
                try { this.queue = JSON.parse(localStorage.getItem('pos_queue') || '[]'); } catch(e) { this.queue = []; }
                
                window.addEventListener('online', () => { this.online=true; this.process(); });
                window.addEventListener('offline', () => this.online=false);
                
                window.addEventListener('queue-push', e => {
                    this.queue.push(e.detail);
                    this.save();
                    this.process();
                });
                
                // Start processing if pending
                if(this.online && this.queue.length) setTimeout(() => this.process(), 500);
            },
            save() { localStorage.setItem('pos_queue', JSON.stringify(this.queue)); },
            get queueCount() { return this.queue.length; },
            
            async process() {
                if(!this.online || this.syncing || this.queue.length === 0) return;
                this.syncing = true;
                
                const order = this.queue[0];
                
                // Format payload for your backend
                const payload = {
                    cart: order.items.map(i => ({ product_id: i.id, variant_id: i.vid, quantity: i.qty, price: i.p })),
                    paymentMethod: order.method,
                    store_id: order.store_id,
                    total: order.total.replace(/,/g, '') // remove commas
                };

                try {
                    const res = await fetch(CHECKOUT_URL, {
                        method: 'POST',
                        headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF},
                        body: JSON.stringify(payload)
                    });
                    
                    if(res.ok || res.status === 422) { 
                        // If 200 OK or 422 (Validation Error) we remove from queue to prevent infinite loop
                        this.queue.shift();
                        this.save();
                        window.dispatchEvent(new CustomEvent('notify', { detail: res.ok ? 'Order Synced' : 'Order Rejected (Validation)' }));
                    } else {
                        // 500 Error - keep in queue
                         console.error('Server Error');
                    }
                } catch(e) { 
                    console.log('Network Error - keeping in queue'); 
                }
                
                this.syncing = false;
                if(this.queue.length > 0 && this.online) setTimeout(() => this.process(), 2000);
            }
        }
    }
  </script>
</body>
</html>