<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
  <title>POS - {{ auth()->user()->store->name ?? 'Store' }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  
  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Fira+Code:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- Libraries -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
  <!-- Barcode Scanner Library -->
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

  <style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    [x-cloak] { display: none !important; }
    .animate-fade-in { animation: fadeIn 0.2s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  </style>
</head>
@php
  $user = auth()->user();
  $store = $user->store ?? null;
  $storeId = $store ? $store->id : 0;
  $storeName = $store ? $store->name : 'My Store';
  
  $products = $products ?? collect();
  $preparedProducts = $products->map(function ($product) use ($storeId) {
    $stock = 0;
    if ($product->storeInventories) {
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
  })
  ->sortByDesc('s')
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

    <div class="flex items-center gap-4 text-xs" x-data="syncManager()">
      <div class="text-gray-400 font-mono hidden sm:block" x-text="time"></div>
      <div x-show="queueCount > 0" x-cloak 
           class="flex items-center gap-2 px-2 py-1 rounded bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 cursor-pointer hover:bg-yellow-500/30 transition"
           @click="process" title="Click to force sync">
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
    <div x-data="cartSidebar()" class="bg-white border-r border-gray-100 shadow-[4px_0_24px_rgba(0,0,0,0.02)] z-40 w-full md:w-[380px] flex flex-col h-full relative">
      <!-- Tabs -->
      <div class="flex items-center bg-white border-b border-gray-50 px-2 h-12 flex-none overflow-x-auto custom-scrollbar">
        <template x-for="(session, id) in sessions" :key="id">
          <div @click="switchTab(id)" 
               class="group relative px-4 h-full flex items-center justify-center cursor-pointer select-none transition-all mr-1"
               :class="activeTab === id ? 'text-gray-900' : 'text-gray-400 hover:text-gray-600'">
            <span class="text-xs font-bold tracking-wide" x-text="`Order #${session.number}`"></span>
            <div x-show="activeTab === id" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1 h-1 bg-purple-600 rounded-full mb-1.5"></div>
            <button x-show="Object.keys(sessions).length > 1" @click.stop="closeTab(id)" class="ml-2 text-[10px] opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition-opacity"><i class="fas fa-times"></i></button>
          </div>
        </template>
        <button @click="createTab" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-50 text-gray-400 hover:text-purple-600 transition-colors ml-1"><i class="fas fa-plus text-xs"></i></button>
      </div>

      <!-- Header -->
      <div class="px-5 py-3 flex justify-between items-center bg-white/80 backdrop-blur-sm z-10">
         <div>
             <h2 class="font-bold text-gray-800 text-sm">Cart Items</h2>
             <p class="text-[10px] text-gray-400 font-medium"><span x-text="count"></span> items added</p>
         </div>
         <button @click="clear" x-show="count > 0" class="text-[10px] font-bold text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-full transition-colors">Clear</button>
      </div>

      <!-- Cart List -->
      <div class="flex-1 overflow-y-auto px-2 pb-2 custom-scrollbar space-y-1" id="cart-container">
        <template x-if="count === 0">
          <div class="h-full flex flex-col items-center justify-center text-gray-300 select-none pb-20">
            <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-3"><i class="fas fa-basket-shopping text-xl opacity-30"></i></div>
            <p class="text-xs font-medium">Cart is empty</p>
          </div>
        </template>
        <template x-for="(item, key) in currentItems" :key="key">
          <div class="group relative flex justify-between items-start p-3 hover:bg-gray-50/80 rounded-xl transition-colors border border-transparent hover:border-gray-100">
            <div class="flex-1 min-w-0 pr-3">
              <div class="text-sm font-semibold text-gray-800 leading-tight truncate" x-text="item.n"></div>
              <div class="flex items-center flex-wrap gap-2 mt-1">
                 <div x-show="item.v_name" class="flex items-center text-[10px] bg-purple-50 rounded px-1.5 py-0.5 border border-purple-100">
                    <span class="font-bold text-purple-700" x-text="item.v_name"></span>
                    <span x-show="item.unit_qty > 1" class="ml-1 pl-1 border-l border-purple-200 text-purple-600 font-mono"><span x-text="item.unit_qty"></span> &times; <span x-text="item.qty"></span></span>
                 </div>
                 <span class="text-[10px] text-gray-400 font-mono">@ ₦<span x-text="format(item.p)"></span></span>
              </div>
              <div x-show="item.unit_qty > 1" class="text-[9px] text-gray-400 mt-0.5 font-medium pl-0.5"><i class="fas fa-box-open mr-1"></i>Total: <span class="text-gray-600" x-text="item.unit_qty * item.qty"></span> units</div>
            </div>
            <div class="flex flex-col items-end gap-1.5">
               <span class="font-bold text-sm text-gray-900">₦<span x-text="format(item.p * item.qty)"></span></span>
               <div class="flex items-center bg-white border border-gray-200 rounded-lg h-7 shadow-sm">
                 <button @click="mod(key, -1)" class="w-7 h-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-l-lg"><i class="fas fa-minus text-[8px]"></i></button>
                 <div class="w-6 text-center text-[11px] font-bold text-gray-700 select-none" x-text="item.qty"></div>
                 <button @click="mod(key, 1)" class="w-7 h-full flex items-center justify-center text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-r-lg"><i class="fas fa-plus text-[8px]"></i></button>
               </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Checkout -->
      <div class="px-5 py-4 bg-white border-t border-gray-50 z-20">
        <div class="flex justify-between items-baseline mb-4">
          <span class="text-xs font-medium text-gray-400">Total Amount</span>
          <span class="text-2xl font-black text-gray-900 tracking-tight">₦<span x-text="total"></span></span>
        </div>
        <div class="grid grid-cols-3 gap-3 h-11">
          <button @click="pay('cash')" :disabled="count===0" class="group relative overflow-hidden bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-500/20 disabled:opacity-50 transition-all active:scale-95"><div class="flex flex-col items-center justify-center h-full"><span class="text-[10px] font-bold uppercase tracking-wider">Cash</span></div></button>
          <button @click="pay('pos')" :disabled="count===0" class="group relative overflow-hidden bg-blue-500 hover:bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-500/20 disabled:opacity-50 transition-all active:scale-95"><div class="flex flex-col items-center justify-center h-full"><span class="text-[10px] font-bold uppercase tracking-wider">POS</span></div></button>
          <button @click="pay('bank')" :disabled="count===0" class="group relative overflow-hidden bg-purple-500 hover:bg-purple-600 text-white rounded-xl shadow-lg shadow-purple-500/20 disabled:opacity-50 transition-all active:scale-95"><div class="flex flex-col items-center justify-center h-full"><span class="text-[10px] font-bold uppercase tracking-wider">Transfer</span></div></button>
        </div>
      </div>
    </div>

    <!-- RIGHT: PRODUCT GRID -->
    <div x-data="productGrid()" class="flex-1 flex flex-col bg-slate-100 overflow-hidden">
      <!-- Search -->
      <div class="bg-white p-3 border-b shadow-sm flex items-center justify-between gap-3 sticky top-0 z-30">
        <div class="relative w-full max-w-lg flex gap-2">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input id="product-search" type="text" x-model="search" @input.debounce.150ms="updateFilter"
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border-transparent focus:bg-white focus:border-purple-500 rounded-lg text-sm transition-all focus:ring-0 shadow-inner" 
                    placeholder="Search Name or Scan Barcode (F2)" @keydown.enter.prevent="enterSearch" />
                <button x-show="search" @click="search=''; updateFilter()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1"><i class="fas fa-times-circle"></i></button>
            </div>
            <button @click="$dispatch('toggle-scanner')" class="bg-purple-600 text-white w-10 h-10 rounded-lg flex-none flex items-center justify-center hover:bg-purple-700 active:scale-95 shadow-md transition-all"><i class="fas fa-qrcode"></i></button>
        </div>
        <div class="text-xs text-gray-500 hidden md:flex flex-col items-end">
            <span class="font-bold text-gray-800"><span x-text="filteredCount"></span> Products</span>
            <span class="text-[10px]">Visible</span>
        </div>
      </div>

      <!-- Grid -->
      <div class="flex-1 overflow-y-auto p-5 custom-scrollbar bg-gray-50/50" id="product-area">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 pb-24">
          <template x-for="p in visibleProducts" :key="p.id">
            <button @click="click(p)" class="group relative flex flex-col justify-between bg-white rounded-xl p-4 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] transition-all duration-200 hover:shadow-md hover:border-gray-300 text-left h-[130px]">
              <div class="flex justify-between items-center w-full mb-1">
                 <span class="font-bold text-base text-gray-900 tracking-tight group-hover:text-purple-600 transition-colors">₦<span x-text="format(p.p)"></span></span>
                 <i x-show="p.v.length" class="fas fa-layer-group text-[10px] text-gray-300 group-hover:text-purple-500 transition-colors"></i>
              </div>
              <div class="flex-1 flex flex-col justify-center">
                <h3 class="font-medium text-[13px] text-gray-600 leading-snug line-clamp-2 group-hover:text-gray-900 transition-colors" x-text="p.n"></h3>
                <p class="text-[10px] text-gray-300 font-mono mt-1 truncate" x-show="p.b" x-text="p.b"></p>
              </div>
              <div class="mt-2 w-full">
                <div x-show="p.s <= 0" class="flex items-center gap-1.5"><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div><span class="text-[10px] font-medium text-red-500">Out of stock</span></div>
                <div x-show="p.s > 0 && p.s <= 5" class="flex items-center gap-1.5"><span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-orange-500"></span></span><span class="text-[10px] font-medium text-orange-600"><span x-text="p.s"></span> left</span></div>
                <div x-show="p.s > 5" class="flex items-center gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity"><div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div><span class="text-[10px] font-medium text-gray-500"><span x-text="p.s"></span> in stock</span></div>
              </div>
            </button>
          </template>
        </div>
        <div x-show="visibleProducts.length < filteredCount" class="py-8 text-center"><button @click="limit += 24" class="text-xs font-semibold text-gray-500 hover:text-purple-600 transition-colors border-b border-transparent hover:border-purple-200 pb-0.5">Load More Products</button></div>
        <div x-show="filteredCount === 0" class="flex flex-col items-center justify-center pt-24 opacity-40"><i class="fas fa-search text-3xl mb-2 text-gray-300"></i><p class="text-sm font-medium text-gray-400">No products found</p></div>
      </div>
    </div>
  </div>

  <!-- MODALS -->

  <!-- Variant Modal -->
  <div x-data="variantModal" x-show="show" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div @click.away="show=false" class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden animate-fade-in">
        <div class="p-3 bg-gray-50 border-b flex justify-between items-center"><span class="font-bold text-sm truncate pr-2" x-text="product?.n"></span><button @click="show=false" class="w-8 h-8 rounded-full hover:bg-gray-200 text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button></div>
        <div class="p-2 space-y-1 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <template x-for="v in product?.v" :key="v.id">
                <button @click="select(v)" class="w-full flex justify-between items-center p-3 hover:bg-purple-50 rounded-lg border border-transparent hover:border-purple-200 text-sm transition-all group">
                    <div class="text-left"><div class="font-bold text-gray-800 group-hover:text-purple-700" x-text="v.n"></div><div class="text-xs text-gray-500" x-text="'Unit Qty: '+v.q"></div></div>
                    <div class="font-bold text-purple-700 bg-purple-100 px-2 py-1 rounded">₦<span x-text="format(v.p)"></span></div>
                </button>
            </template>
        </div>
    </div>
  </div>

  <!-- Scanner Modal -->
  <div x-data="scannerComponent()" x-show="open" x-cloak class="fixed inset-0 z-[80] bg-black/95 flex flex-col items-center justify-center p-4">
    <button @click="stop" class="absolute top-4 right-4 text-white bg-gray-800/50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-red-600 z-50"><i class="fas fa-times"></i></button>
    <div class="w-full max-w-sm relative"><div class="text-white text-center mb-4 font-bold text-lg">Scan Barcode</div><div id="reader" class="bg-black w-full rounded-2xl overflow-hidden shadow-2xl border-4 border-purple-500"></div></div>
  </div>

  <!-- RECEIPT MODAL -->
  <div x-data="receiptModal" x-show="show" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm flex flex-col h-[85vh] animate-fade-in">
        <div class="p-3 border-b flex justify-between items-center bg-emerald-50">
            <h3 class="font-bold text-emerald-700 flex items-center gap-2"><i class="fas fa-check-circle"></i> Paid Successfully</h3>
            <button @click="close" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-emerald-100"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="flex-1 bg-gray-100 overflow-y-auto p-6 flex justify-center custom-scrollbar">
            <!-- Receipt Paper -->
            <div id="receipt-paper" class="bg-white w-[80mm] min-h-[100mm] shadow-xl p-4 text-[12px] font-mono leading-tight flex flex-col">
                <!-- Content injected via JS -->
            </div>
        </div>

        <div class="p-3 border-t bg-white grid grid-cols-2 gap-3">
             <!-- SHARE TO WHATSAPP BUTTON -->
             <a :href="'https://wa.me/?text=' + encodeURIComponent(whatsappText)" target="_blank" class="py-3 bg-green-500 text-white rounded-lg font-bold hover:bg-green-600 transition-colors shadow-lg flex items-center justify-center"><i class="fab fa-whatsapp mr-2 text-lg"></i> Share</a>
             <button @click="print" class="py-3 bg-gray-800 text-white rounded-lg font-bold hover:bg-black transition-colors shadow-lg"><i class="fas fa-print mr-2"></i> Print</button>
        </div>
        <div class="p-2 text-center bg-gray-50 border-t"><button @click="close" class="text-xs text-gray-500 hover:text-purple-600 font-bold">Close & New Order</button></div>
    </div>
  </div>

  <!-- Notifications -->
  <div x-data="{ msgs: [] }" @notify.window="msgs.push($event.detail); setTimeout(()=>msgs.shift(), 3000)" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
    <template x-for="m in msgs">
       <div class="px-4 py-3 rounded-lg shadow-xl text-sm animate-fade-in font-bold border-l-4 flex items-center gap-2" :class="m.type === 'error' ? 'bg-white text-red-600 border-red-500' : (m.type === 'success' ? 'bg-white text-emerald-600 border-emerald-500' : 'bg-gray-800 text-white border-gray-600')"><i class="fas" :class="m.type === 'error' ? 'fa-exclamation-circle' : (m.type === 'success' ? 'fa-check-circle' : 'fa-info-circle')"></i><span x-text="m.message || m"></span></div>
    </template>
  </div>

  <script>
    const RAW_PRODUCTS = @json($preparedProducts);
    const STORE_INFO = { name: "{{ $storeName }}", id: "{{ $storeId }}", address: "{{ auth()->user()->store->address ?? 'Main Branch' }}", phone: "{{ auth()->user()->store->phone ?? '' }}" };
    const CURRENCY = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2 });
    const CHECKOUT_URL = "{{ route('checkout.process') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    const format = (n) => CURRENCY.format(n);
    const playBeep = () => { if(audioCtx.state === 'suspended') audioCtx.resume(); const osc = audioCtx.createOscillator(); const gain = audioCtx.createGain(); osc.connect(gain); gain.connect(audioCtx.destination); osc.frequency.value = 850; gain.gain.value = 0.05; osc.start(); gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 0.1); osc.stop(audioCtx.currentTime + 0.1); };

    function app() { return { time: '', init() { setInterval(() => this.time = new Date().toLocaleTimeString('en-US', {hour12:true, hour:'numeric', minute:'2-digit'}), 1000); }, handleGlobalKeys(e) { if(['F2','F8','F9','F10'].includes(e.key)) e.preventDefault(); if(e.key === 'F2') { document.getElementById('product-search').focus(); document.getElementById('product-search').select(); } if(e.key === 'F8') this.$dispatch('pay-request', 'cash'); if(e.key === 'F9') this.$dispatch('pay-request', 'pos'); if(e.key === 'F10') this.$dispatch('pay-request', 'bank'); if(e.key === 'Escape') window.dispatchEvent(new CustomEvent('close-modals')); if(e.key === 'Delete') this.$dispatch('clear-cart'); } } }

    function productGrid() {
        return {
            all: RAW_PRODUCTS, filtered: RAW_PRODUCTS, search: '', limit: 24,
            init() { window.addEventListener('camera-scan', (e) => { const code = e.detail; const exact = this.all.find(p => p.b == code); if (exact) { this.click(exact); window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Item Added', type: 'success' } })); } else { this.search = code; this.updateFilter(); window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Product not found', type: 'error' } })); } }); },
            get visibleProducts() { return this.filtered.slice(0, this.limit); },
            get filteredCount() { return this.filtered.length; },
            updateFilter() { const s = this.search.toLowerCase().trim(); this.limit = 24; if(!s) { this.filtered = this.all; return; } this.filtered = this.all.filter(p => p.n.toLowerCase().includes(s) || (p.b && p.b.includes(s))); },
            enterSearch() { const s = this.search.trim(); if(!s) return; const exact = this.all.find(p => p.b == s); if(exact) { this.click(exact); this.search = ''; this.updateFilter(); } },
            click(p) { if(p.v && p.v.length) window.dispatchEvent(new CustomEvent('open-variant', { detail: p })); else window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { p } })); },
            format
        }
    }

    function scannerComponent() { return { open: false, scanner: null, init() { window.addEventListener('toggle-scanner', () => { this.open ? this.stop() : this.start(); }); window.addEventListener('close-modals', () => this.stop()); }, start() { this.open = true; this.$nextTick(() => { if(this.scanner) return; this.scanner = new Html5Qrcode("reader"); this.scanner.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 }, (decodedText) => this.onScanSuccess(decodedText), () => {}).catch(() => { this.open = false; }); }); }, stop() { if (this.scanner) { this.scanner.stop().then(() => { this.scanner.clear(); this.scanner = null; this.open = false; }).catch(() => { this.open = false; }); } else { this.open = false; } }, onScanSuccess(code) { playBeep(); this.stop(); window.dispatchEvent(new CustomEvent('camera-scan', { detail: code })); } } }

    function cartSidebar() {
        return {
            sessions: {}, activeTab: null,
            init() { this.load(); window.addEventListener('add-to-cart', e => this.add(e.detail.p, e.detail.v)); window.addEventListener('pay-request', e => this.pay(e.detail)); window.addEventListener('clear-cart', () => this.clear()); },
            load() { const s = localStorage.getItem('pos_cart_v3'); this.sessions = s ? JSON.parse(s) : { 'default': { number: 1, items: {} } }; this.activeTab = Object.keys(this.sessions)[0] || 'default'; if(!this.sessions[this.activeTab]) this.sessions[this.activeTab] = { number: 1, items: {} }; },
            save() { localStorage.setItem('pos_cart_v3', JSON.stringify(this.sessions)); },
            createTab() { const id = 't' + Date.now(); this.sessions[id] = { number: Object.keys(this.sessions).length + 1, items: {} }; this.activeTab = id; this.save(); },
            closeTab(id) { delete this.sessions[id]; if(Object.keys(this.sessions).length === 0) this.createTab(); else this.activeTab = Object.keys(this.sessions)[0]; this.save(); },
            switchTab(id) { this.activeTab = id; },
            get currentItems() { return this.sessions[this.activeTab]?.items || {}; },
            get count() { return Object.keys(this.currentItems).length; },
            get total() { return format(Object.values(this.currentItems).reduce((a, i) => a + (i.p * i.qty), 0)); },
            add(p, v=null) { playBeep(); const vid = v ? v.id : 'base'; const key = `${p.id}_${vid}`; const items = this.sessions[this.activeTab].items; if(items[key]) items[key].qty++; else items[key] = { id: p.id, n: p.n, p: v?v.p:p.p, v_name: v?v.n:null, vid: v?v.id:null, unit_qty: v?v.q:1, qty: 1 }; this.save(); this.$nextTick(() => { const el = document.getElementById('cart-container'); if(el) el.scrollTop = el.scrollHeight; }); },
            mod(k, n) { const i = this.sessions[this.activeTab].items; if(i[k]) { i[k].qty += n; if(i[k].qty < 1) delete i[k]; this.save(); } },
            clear() { if(confirm('Clear current order?')) { this.sessions[this.activeTab].items = {}; this.save(); } },
            pay(method) { if(this.count === 0) return; const orderData = { id: 'ORD-' + Math.floor(Date.now() / 1000), date: new Date().toLocaleString('en-NG'), method: method, items: Object.values(this.currentItems), total: this.total, store_id: STORE_INFO.id }; window.dispatchEvent(new CustomEvent('show-receipt', { detail: orderData })); window.dispatchEvent(new CustomEvent('queue-push', { detail: orderData })); this.sessions[this.activeTab].items = {}; this.save(); }, format
        }
    }

    const variantModal = { show: false, product: null, init() { window.addEventListener('open-variant', e => { this.product=e.detail; this.show=true; }); window.addEventListener('close-modals', () => this.show=false); }, select(v) { window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { p: this.product, v } })); this.show = false; }, format };

    // --- RECEIPT MODAL (UPDATED) ---
    const receiptModal = {
        show: false,
        data: null,
        whatsappText: '',
        init() { window.addEventListener('show-receipt', e => { this.data = e.detail; this.generateContent(); this.show = true; }); window.addEventListener('close-modals', () => this.show=false); },
        generateContent() {
            if(!this.data) return;
            // 1. Generate Plain Text for WhatsApp/QR
            let txt = `*${STORE_INFO.name}*\n`;
            txt += `Date: ${this.data.date}\n`;
            txt += `Receipt: ${this.data.id}\n`;
            txt += `----------------\n`;
            this.data.items.forEach(i => {
                txt += `${i.qty}x ${i.n} ${i.v_name ? '('+i.v_name+')' : ''}\n`;
                txt += `= ${format(i.p * i.qty)}\n`;
            });
            txt += `----------------\n`;
            txt += `*TOTAL: N${this.data.total}*`;
            this.whatsappText = txt;

            // 2. Generate HTML
            const itemsHtml = this.data.items.map(i => `
                <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                    <span>${i.qty} x ${i.n} ${i.v_name ? '<br><small>('+i.v_name+')</small>' : ''}</span>
                    <span style="font-weight:bold">${format(i.p * i.qty)}</span>
                </div>
            `).join('');

            // QR Code URL (using free API)
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&margin=0&data=${encodeURIComponent(txt)}`;

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
                <div style="margin-bottom:10px; border-bottom:1px dashed #000; padding-bottom:10px; min-height:100px">
                    ${itemsHtml}
                </div>
                <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:bold; margin-bottom:15px">
                    <span>TOTAL</span>
                    <span>₦${this.data.total}</span>
                </div>
                <div style="text-align:center; margin-top:auto; padding-top:10px; border-top:1px dashed #ddd">
                     <img src="${qrUrl}" style="width:100px; height:100px; margin:0 auto 5px auto; display:block" alt="Receipt QR" />
                     <p style="font-size:9px; color:#555">Scan for e-receipt details</p>
                     <p style="font-size:10px; font-weight:bold; margin-top:5px">Thank you for your patronage!</p>
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

    function syncManager() { return { queue: [], online: navigator.onLine, syncing: false, init() { try { this.queue = JSON.parse(localStorage.getItem('pos_queue') || '[]'); } catch(e) { this.queue = []; } window.addEventListener('online', () => { this.online=true; this.process(); }); window.addEventListener('offline', () => this.online=false); window.addEventListener('queue-push', e => { this.queue.push(e.detail); this.save(); this.process(); }); if(this.online && this.queue.length) setTimeout(() => this.process(), 500); }, save() { localStorage.setItem('pos_queue', JSON.stringify(this.queue)); }, get queueCount() { return this.queue.length; }, async process() { if(!this.online || this.syncing || this.queue.length === 0) return; this.syncing = true; const order = this.queue[0]; const payload = { cart: order.items.map(i => ({ product_id: i.id, variant_id: i.vid, quantity: i.qty, price: i.p })), paymentMethod: order.method, store_id: order.store_id, total: order.total.replace(/,/g, '') }; try { const res = await fetch(CHECKOUT_URL, { method: 'POST', headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF}, body: JSON.stringify(payload) }); if(res.ok || res.status === 422) { this.queue.shift(); this.save(); window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Cloud Sync Complete', type: 'success' } })); } } catch(e) { console.log('Network Error'); } this.syncing = false; if(this.queue.length > 0 && this.online) setTimeout(() => this.process(), 2000); } } }
  </script>
</body>
</html>