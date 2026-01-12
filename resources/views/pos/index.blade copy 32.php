<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
  <title>POS - {{ auth()->user()->store->name ?? 'Store' }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="theme-color" content="#0f172a" />
  
  <link rel="manifest" href="{{ asset('manifest.json') }}" />
  <link rel="apple-touch-icon" href="{{ asset('images/pos-icon.png') }}" />

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- Libraries -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

  <style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    [x-cloak] { display: none !important; }
    .touch-manipulation { touch-action: manipulation; }
    #reader video { object-fit: cover; border-radius: 0.75rem; width: 100%; height: 100%; }
    .animate-fade-in { animation: fadeIn 0.2s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
    .safe-area-pb { padding-bottom: env(safe-area-inset-bottom, 20px); }
    /* Prevent text selection to make it feel more app-like */
    .no-select { user-select: none; -webkit-user-select: none; }
  </style>
</head>

@php
  // Null Coalescing for Safety
  $user = auth()->user();
  $store = $user ? ($user->store ?? null) : null;
  $storeId = $store ? $store->id : 0;
  $storeName = $store ? $store->name : 'My Store';
  
  // Ensure $products is a collection even if passed as null
  $productsRaw = $products ?? collect(); 

  $preparedProducts = $productsRaw->map(function ($product) use ($storeId) {
    // Get stock specific to this store, default to 0
    $stock = $product->storeInventories ? (int)$product->storeInventories->where('store_id', $storeId)->sum('quantity') : 0;
    
    // Process Variants
    $variants = collect($product->variants ?? [])->map(function ($v, $k) {
      $get = fn($key) => is_array($v) ? ($v[$key] ?? null) : ($v->$key ?? null);
      return [
        'id' => $get('id') ?? $k, 
        'n' => $get('variant_name') ?? $get('unit_type') ?? 'Option',
        'q' => $get('unit_qty') ?? 1, 
        'p' => (float) ($get('price') ?? 0)
      ];
    })->values()->all();

    return [
      'id' => $product->id, 
      'n' => $product->name, 
      'b' => (string)($product->barcode ?? ''),
      'p' => (float) ($product->sale ?? $product->price ?? 0), 
      's' => $stock, 
      'v' => $variants
    ];
  })->sortByDesc('s')->values()->all();
@endphp

<body class="bg-slate-100 font-sans text-gray-800 h-[100dvh] overflow-hidden flex flex-col no-select" 
      x-data="app()" 
      @click="initAudio()"
      @keydown.window="handleGlobalKeys($event)">

  <!-- TOP NAV -->
  <nav class="bg-slate-900 text-white h-14 md:h-12 flex-none z-50 shadow-md flex justify-between items-center px-3 md:px-4">
    <!-- LEFT -->
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded bg-purple-600 flex items-center justify-center font-bold text-lg shadow-lg">P</div>
            <span class="font-bold text-lg hidden lg:block tracking-tight">{{ Str::limit($storeName, 15) }}</span>
        </div>
        <!-- Mobile Switcher -->
        <div class="flex md:hidden bg-slate-800 p-1 rounded-lg ml-1">
            <button @click="mobileView = 'products'" :class="mobileView === 'products' ? 'bg-purple-600 text-white' : 'text-gray-400'" class="px-3 py-1 rounded text-[10px] font-bold uppercase">Items</button>
            <button @click="mobileView = 'cart'" :class="mobileView === 'cart' ? 'bg-purple-600 text-white' : 'text-gray-400'" class="px-3 py-1 rounded text-[10px] font-bold uppercase flex gap-2">
                Cart <span x-show="cartCount > 0" class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
            </button>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="flex items-center gap-2 md:gap-4">
        <!-- LINK PHONE BUTTON -->
        <button @click="$dispatch('open-mobile-link')" class="hidden md:flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold border border-slate-700 transition-all mr-2 shadow-sm" title="Use Phone as Scanner">
            <i class="fas fa-mobile-alt"></i> Link Phone
        </button>

        <button @click="$dispatch('toggle-shortcuts')" class="text-gray-400 hover:text-white hidden md:block" title="Shortcuts (?)"><i class="far fa-keyboard"></i></button>
        
        <!-- Sync Status -->
        <div class="flex items-center gap-2 md:gap-4 text-xs" x-data="syncManager()">
            <div class="text-gray-400 font-mono hidden xl:block" x-text="time"></div>
            <div x-show="queueCount > 0" x-cloak class="flex items-center gap-1 px-2 py-1 rounded bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 cursor-pointer hover:bg-yellow-500/30 transition-colors" @click="process" title="Sync Pending Orders">
                <i class="fas fa-sync" :class="syncing ? 'fa-spin' : ''"></i>
                <span x-text="queueCount" class="font-bold"></span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full transition-all duration-300" :class="online ? 'bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)]' : 'bg-red-500'"></div>
                <span class="hidden md:inline text-gray-300" x-text="online ? 'Online' : 'Offline'"></span>
            </div>
        </div>

        <!-- User -->
        <div x-data="{ open: false }" class="relative" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-2 hover:bg-slate-800 rounded-lg py-1 px-2 transition-colors">
                <div class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold border border-slate-600">{{ substr($user->name ?? 'U', 0, 1) }}</div>
                <span class="hidden md:block text-sm font-medium">{{ $user->name ?? 'User' }}</span>
                <i class="fas fa-chevron-down text-[10px] text-gray-500"></i>
            </button>
            <div x-show="open" x-cloak class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-[100] border border-gray-100 animate-fade-in origin-top-right">
                <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-500">Logged in as <br><strong class="text-gray-800">{{ $user->name ?? 'Guest' }}</strong></div>
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm hover:bg-purple-50 text-gray-700">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" @submit.prevent="confirmLogout($event)">@csrf<button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</button></form>
            </div>
        </div>
    </div>
  </nav>

  <div class="flex-1 flex overflow-hidden relative">
    <!-- LEFT: CART SIDEBAR -->
    <div x-data="cartSidebar()" class="bg-white border-r border-gray-200 shadow-xl z-40 flex flex-col h-full transition-transform duration-200 w-full md:w-[420px]" :class="mobileView === 'cart' ? 'absolute inset-0 translate-x-0' : 'hidden md:flex relative md:translate-x-0'">
      <div class="flex bg-gray-50 border-b overflow-x-auto custom-scrollbar h-11 flex-none items-end px-1">
        <template x-for="(session, id) in sessions" :key="id">
          <div @click="switchTab(id)" class="px-4 py-2.5 flex items-center justify-between min-w-[100px] cursor-pointer text-xs font-bold border-r select-none transition-colors rounded-t-lg mx-1 mb-[-1px] border-t border-l" :class="activeTab === id ? 'bg-white text-purple-700 border-b-white border-t-purple-600 z-10' : 'bg-gray-100 text-gray-500 border-b-gray-200 hover:bg-gray-50'">
            <span x-text="`Order ${session.number}`"></span>
            <button x-show="Object.keys(sessions).length > 1" @click.stop="closeTab(id)" class="ml-2 text-gray-400 hover:text-red-500">&times;</button>
          </div>
        </template>
        <button @click="createTab" class="px-3 py-2 text-gray-500 hover:text-purple-600"><i class="fas fa-plus"></i></button>
      </div>
      <div class="p-3 bg-white border-b shadow-sm flex-none grid grid-cols-3 gap-2" :class="refundMode ? 'bg-red-50' : ''">
         <button @click="$dispatch('open-custom-item')" class="flex items-center justify-center gap-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-2 py-2 rounded text-xs font-bold border border-indigo-100"><i class="fas fa-pen"></i> Misc</button>
         <button @click="toggleRefund" class="flex items-center justify-center gap-1 px-2 py-2 rounded text-xs font-bold border transition-all" :class="refundMode ? 'bg-red-600 text-white border-red-700 shadow-inner' : 'bg-gray-50 text-gray-600 hover:bg-red-50 border-gray-200'"><i class="fas fa-undo"></i> <span x-text="refundMode ? 'Refunding' : 'Return'"></span></button>
         <button @click="clear" :disabled="count===0" class="flex items-center justify-center gap-1 bg-white text-red-600 hover:bg-red-50 disabled:opacity-50 px-2 py-2 rounded text-xs font-bold border border-red-100"><i class="fas fa-trash-alt"></i> Clear</button>
      </div>
      <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar" :class="refundMode ? 'bg-red-50/30' : 'bg-slate-50'" id="cart-container">
        <template x-if="count === 0"><div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-60 select-none pointer-events-none"><i class="fas" :class="refundMode ? 'fa-undo text-red-300' : 'fa-cash-register text-gray-300'" class="text-5xl mb-4"></i><p class="text-sm font-medium" x-text="refundMode ? 'Scan items to return' : 'Scan items to sell'"></p></div></template>
        <template x-for="(item, key) in currentItems" :key="key">
          <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 flex justify-between group animate-fade-in touch-manipulation relative overflow-hidden" :class="item.qty < 0 ? 'border-l-4 border-l-red-500' : ''">
            <div class="flex-1 min-w-0 pr-2">
              <div class="text-sm font-bold text-gray-800 leading-tight truncate" x-text="item.n"></div>
              <div class="text-[11px] text-gray-500 mt-1.5 flex items-center flex-wrap gap-1">
                 <span x-show="item.v_name" class="bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded text-[10px] border border-indigo-100 font-semibold" x-text="item.v_name"></span>
                 <span class="text-gray-400" x-text="`${Math.abs(item.qty)} x ₦${format(item.p)}`"></span>
                 <span x-show="item.b" class="text-gray-300 font-mono text-[9px]"><i class="fas fa-barcode"></i> <span x-text="item.b"></span></span>
              </div>
            </div>
            <div class="flex flex-col items-end justify-between">
               <span class="font-bold" :class="item.qty < 0 ? 'text-red-600' : 'text-gray-900'">₦<span x-text="format(item.p * item.qty)"></span></span>
               <div class="flex items-center bg-gray-100 rounded-lg mt-2 border border-gray-200">
                 <button @click="mod(key, refundMode ? 1 : -1)" class="w-8 h-7 hover:bg-white rounded text-gray-600 flex items-center justify-center"><i class="fas fa-minus text-[10px]"></i></button>
                 <span class="w-8 text-center text-xs font-bold" x-text="Math.abs(item.qty)"></span>
                 <button @click="mod(key, refundMode ? -1 : 1)" class="w-8 h-7 hover:bg-white rounded text-green-600 flex items-center justify-center"><i class="fas fa-plus text-[10px]"></i></button>
               </div>
            </div>
          </div>
        </template>
      </div>
      <div class="p-4 bg-white border-t z-20 flex-none shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] safe-area-pb">
        <div class="space-y-1 mb-3 text-xs">
            <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>₦<span x-text="subtotal"></span></span></div>
            <div class="flex justify-between text-emerald-600 cursor-pointer hover:bg-emerald-50 rounded px-1 -mx-1" @click="$dispatch('open-discount')"><span class="flex items-center gap-1"><i class="fas fa-tag"></i> Discount <span x-show="discount > 0" class="text-[10px] bg-emerald-100 px-1 rounded" x-text="discountType === 'percent' ? discount + '%' : 'Fixed'"></span></span><span>-₦<span x-text="discountAmt"></span></span></div>
        </div>
        <div class="flex justify-between items-end mb-4 pt-2 border-t border-dashed">
          <div class="text-xs text-gray-500 font-bold uppercase" x-text="totalVal < 0 ? 'Total Refund' : 'Total Payable'"></div>
          <div class="text-3xl font-black tracking-tight" :class="totalVal < 0 ? 'text-red-600' : 'text-gray-900'">₦<span x-text="total"></span></div>
        </div>
        <div class="grid grid-cols-3 gap-2 h-12">
          <button @click="pay('cash')" :disabled="count===0" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs uppercase shadow active:scale-95 transition-all disabled:opacity-50 flex flex-col items-center justify-center leading-none gap-1"><span>Cash</span><span class="text-[9px] opacity-70">(F8)</span></button>
          <button @click="pay('pos')" :disabled="count===0" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs uppercase shadow active:scale-95 transition-all disabled:opacity-50 flex flex-col items-center justify-center leading-none gap-1"><span>POS</span><span class="text-[9px] opacity-70">(F9)</span></button>
          <button @click="pay('bank')" :disabled="count===0" class="bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-bold text-xs uppercase shadow active:scale-95 transition-all disabled:opacity-50 flex flex-col items-center justify-center leading-none gap-1"><span>Trans</span><span class="text-[9px] opacity-70">(F10)</span></button>
        </div>
      </div>
    </div>

    <!-- RIGHT: PRODUCT GRID -->
    <div x-data="productGrid()" class="flex-1 flex flex-col bg-slate-100 overflow-hidden w-full" :class="mobileView === 'cart' ? 'hidden md:flex' : 'flex'">
      <div class="bg-white p-3 md:p-4 border-b shadow-sm flex items-center justify-between gap-3 sticky top-0 z-30">
        <div class="relative w-full md:max-w-xl flex gap-2">
            <div class="relative flex-1 group">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-purple-500 transition-colors"></i>
                <input id="product-search" type="text" x-model="search" @input.debounce.150ms="updateFilter" class="w-full pl-10 pr-8 py-3 bg-gray-100 border-2 border-transparent focus:bg-white focus:border-purple-500 rounded-xl text-sm transition-all focus:ring-0 shadow-inner outline-none font-medium" placeholder="Search Products (F2) or Scan Barcode" @keydown.enter.prevent="enterSearch" />
                <button x-show="search" @click="search=''; updateFilter()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 p-2 transition-colors"><i class="fas fa-times-circle"></i></button>
            </div>
            <button @click="$dispatch('toggle-scanner')" class="bg-purple-600 text-white w-12 h-12 rounded-xl flex-none flex items-center justify-center hover:bg-purple-700 active:scale-95 shadow-lg shadow-purple-600/30 transition-all">
                <i class="fas fa-qrcode text-lg"></i>
            </button>
        </div>
        <div class="text-xs text-gray-500 hidden lg:flex flex-col items-end"><span class="font-bold text-gray-800 text-sm"><span x-text="filteredCount"></span> Items</span></div>
      </div>
      <div class="flex-1 overflow-y-auto p-3 md:p-5 custom-scrollbar bg-slate-50/50" id="product-area">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 md:gap-4 pb-24">
          <template x-for="p in visibleProducts" :key="p.id">
            <button @click="click(p)" :class="{'opacity-60 grayscale': p.s <= 0}" class="group relative flex flex-col justify-between bg-white rounded-xl p-3 md:p-4 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] transition-all duration-200 active:scale-95 md:hover:shadow-lg md:hover:border-purple-200 text-left h-[135px] md:h-[145px] touch-manipulation hover:-translate-y-0.5">
              <div class="flex justify-between items-start w-full mb-1"><span class="font-bold text-sm md:text-base text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md tracking-tight">₦<span x-text="format(p.p)"></span></span><i x-show="p.v.length" class="fas fa-layer-group text-xs text-emerald-500 bg-emerald-50 p-1 rounded"></i></div>
              <div class="flex-1 flex flex-col justify-center my-1"><h3 class="font-semibold text-[13px] text-gray-700 leading-snug line-clamp-2 group-hover:text-purple-700 transition-colors" x-text="p.n"></h3><div x-show="p.b" class="text-[10px] text-gray-400 font-mono mt-1 truncate"><i class="fas fa-barcode"></i> <span x-text="p.b"></span></div></div>
              <div class="w-full flex items-center gap-1"><div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full rounded-full" :class="p.s <= 5 ? 'bg-red-500' : 'bg-emerald-500'" :style="`width: ${Math.min(100, (p.s/20)*100)}%`"></div></div><span class="text-[10px] font-bold" :class="p.s <= 0 ? 'text-red-500' : (p.s < 5 ? 'text-orange-500' : 'text-gray-400')"><span x-text="p.s <= 0 ? 'Out' : p.s"></span></span></div>
            </button>
          </template>
        </div>
        <div x-show="visibleProducts.length < filteredCount" class="py-8 text-center"><button @click="limit += 24" class="text-xs font-bold text-purple-600 bg-white border border-purple-100 shadow-sm px-6 py-2.5 rounded-full hover:bg-purple-50">Load More</button></div>
        <div x-show="filteredCount === 0" class="flex flex-col items-center justify-center pt-20 text-gray-400"><i class="fas fa-search text-4xl mb-3 opacity-50"></i><p>No products found matching "<span x-text="search"></span>"</p></div>
      </div>
    </div>
  </div>

  <!-- MODALS -->
  <div x-data="{show: false}" x-show="show" @toggle-shortcuts.window="show = !show" @keydown.escape.window="show = false" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
    <div @click.away="show=false" class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm"><h3 class="font-bold text-lg mb-4">Shortcuts</h3><div class="space-y-2 text-sm"><div class="flex justify-between"><span class="text-gray-500">Search Product</span> <kbd class="bg-gray-100 px-2 rounded font-bold">F2</kbd></div><div class="flex justify-between"><span class="text-gray-500">Pay Cash</span> <kbd class="bg-gray-100 px-2 rounded font-bold">F8</kbd></div><div class="flex justify-between"><span class="text-gray-500">Pay POS</span> <kbd class="bg-gray-100 px-2 rounded font-bold">F9</kbd></div><div class="flex justify-between"><span class="text-gray-500">Pay Transfer</span> <kbd class="bg-gray-100 px-2 rounded font-bold">F10</kbd></div></div><button @click="show=false" class="mt-6 w-full py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-bold">Close</button></div>
  </div>

  <!-- NEW: MOBILE LINK MODAL -->
  <div x-data="mobileLinkModal" x-show="show" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div @click.away="close" class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm text-center animate-fade-in">
        <h3 class="font-bold text-xl mb-2 text-gray-800">Use Phone as Scanner</h3>
        <p class="text-sm text-gray-500 mb-4">Scan this with your phone to connect.</p>
        <div class="flex justify-center mb-4"><canvas id="qr-canvas"></canvas></div>
        <div class="bg-blue-50 text-blue-700 text-xs p-3 rounded-lg font-mono mb-4">
            Status: <span x-text="connected ? 'Connected' : 'Waiting...'" class="font-bold"></span>
            <i x-show="connected" class="fas fa-check-circle text-emerald-500 ml-1"></i>
        </div>
        <button @click="close" class="w-full py-3 bg-gray-100 hover:bg-gray-200 rounded-lg font-bold text-gray-600">Close</button>
    </div>
  </div>

  <div x-data="variantModal" x-show="show" x-cloak class="fixed inset-0 z-[60] flex items-end md:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 md:p-4">
    <div @click.away="show=false" class="bg-white rounded-t-2xl md:rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-fade-in max-h-[80vh] flex flex-col">
        <div class="p-4 bg-gray-50 border-b flex justify-between items-center"><div class="min-w-0"><span class="block text-[10px] text-gray-500 uppercase font-bold">Select Option</span><span class="font-bold text-gray-800 truncate block text-lg" x-text="product?.n"></span></div><button @click="show=false" class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-500 hover:bg-gray-100 flex items-center justify-center"><i class="fas fa-times"></i></button></div>
        <div class="p-2 space-y-2 overflow-y-auto custom-scrollbar flex-1 bg-gray-50/50"><template x-for="v in product?.v" :key="v.id"><button @click="select(v)" class="w-full flex justify-between items-center p-3 bg-white border border-gray-100 shadow-sm hover:border-purple-300 rounded-xl group transition-all"><div class="text-left"><div class="font-bold text-gray-800 group-hover:text-purple-700" x-text="v.n"></div><div class="text-xs text-gray-500" x-text="'Unit Qty: '+v.q"></div></div><div class="font-bold text-purple-700 bg-purple-50 px-3 py-1.5 rounded-lg">₦<span x-text="format(v.p)"></span></div></button></template></div>
    </div>
  </div>

  <div x-data="customItemModal" x-show="show" x-cloak class="fixed inset-0 z-[65] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"><div @click.away="close" class="bg-white rounded-xl shadow-2xl w-full max-w-xs p-5 animate-fade-in"><h3 class="font-bold text-gray-800 mb-4 text-lg">Add Misc Item</h3><div class="space-y-3"><div><label class="text-xs font-bold text-gray-500 uppercase">Name</label><input type="text" x-model="name" class="w-full border-gray-200 rounded-lg bg-gray-50 p-2"></div><div><label class="text-xs font-bold text-gray-500 uppercase">Price</label><input type="number" x-model="price" class="w-full border-gray-200 rounded-lg bg-gray-50 p-2 font-bold"></div></div><div class="grid grid-cols-2 gap-3 mt-6"><button @click="close" class="py-2 rounded-lg border border-gray-200 text-gray-600 font-bold text-sm">Cancel</button><button @click="add" class="py-2 rounded-lg bg-purple-600 text-white font-bold text-sm">Add</button></div></div></div>
  <div x-data="discountModal" x-show="show" x-cloak class="fixed inset-0 z-[65] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"><div @click.away="close" class="bg-white rounded-xl shadow-2xl w-full max-w-xs p-5 animate-fade-in"><h3 class="font-bold text-gray-800 mb-4 text-lg">Discount</h3><div class="flex bg-gray-100 p-1 rounded-lg mb-4"><button @click="type='fixed'" class="flex-1 py-1.5 rounded-md text-xs font-bold transition-all" :class="type==='fixed' ? 'bg-white shadow text-purple-700' : 'text-gray-500'">Fixed (₦)</button><button @click="type='percent'" class="flex-1 py-1.5 rounded-md text-xs font-bold transition-all" :class="type==='percent' ? 'bg-white shadow text-purple-700' : 'text-gray-500'">Percent (%)</button></div><div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold" x-text="type==='fixed' ? '₦' : '%'"></span><input type="number" x-model="val" x-ref="discInput" class="w-full pl-8 border-gray-200 rounded-lg p-2 font-bold text-lg"></div><div class="grid grid-cols-2 gap-3 mt-6"><button @click="remove" class="py-2 rounded-lg border border-red-100 text-red-600 font-bold text-sm">Remove</button><button @click="apply" class="py-2 rounded-lg bg-emerald-600 text-white font-bold text-sm">Apply</button></div></div></div>
  <div x-data="scannerComponent()" x-show="open" x-cloak class="fixed inset-0 z-[80] bg-black flex flex-col"><div class="absolute top-0 left-0 w-full p-4 flex justify-between items-center z-50 bg-gradient-to-b from-black/80 to-transparent"><span class="text-white font-bold drop-shadow-md flex items-center gap-2"><i class="fas fa-qrcode"></i> Scan</span><button @click="stop" class="text-white bg-white/20 backdrop-blur rounded-full px-4 py-1.5 text-xs font-bold">Close</button></div><div class="flex-1 flex items-center justify-center bg-black relative"><div id="reader" class="w-full h-full"></div><div x-show="open" class="absolute pointer-events-none border-2 border-green-400 w-64 h-64 rounded-xl shadow-[0_0_0_9999px_rgba(0,0,0,0.7)]"></div></div></div>

  <div x-data="receiptModal" x-show="show" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm flex flex-col h-[85vh] animate-fade-in overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center bg-emerald-50"><h3 class="font-bold text-emerald-700 flex items-center gap-2"><i class="fas fa-check-circle"></i> Complete</h3><button @click="close" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-emerald-100"><i class="fas fa-times"></i></button></div>
        <div class="flex-1 bg-gray-200 overflow-y-auto p-4 flex justify-center custom-scrollbar"><div id="receipt-paper" class="bg-white w-full shadow-md p-4 text-[12px] relative transform transition-transform"></div></div>
        <div class="p-4 border-t bg-white grid grid-cols-2 gap-3 safe-area-pb"><button @click="print" class="py-3 bg-slate-800 text-white rounded-lg font-bold shadow-lg flex items-center justify-center gap-2"><i class="fas fa-print"></i> Print</button><button @click="close" class="py-3 bg-gray-100 text-gray-800 rounded-lg font-bold border border-gray-200">New Sale</button></div>
    </div>
  </div>

  <div x-data="{ msgs: [] }" @notify.window="msgs.push($event.detail); setTimeout(()=>msgs.shift(), 3000)" class="fixed bottom-6 right-4 z-[100] flex flex-col gap-2 pointer-events-none w-full px-6 md:w-auto md:px-0"><template x-for="m in msgs"><div class="px-4 py-3 rounded-xl shadow-xl text-sm animate-fade-in font-bold border-l-4 flex items-center gap-3 bg-white min-w-[280px]" :class="m.type === 'error' ? 'text-red-600 border-red-500' : 'text-emerald-600 border-emerald-500'"><i class="fas" :class="m.type === 'error' ? 'fa-exclamation' : 'fa-check'"></i><span x-text="m.message || m"></span></div></template></div>

  <script>
    // --- 1. CONFIGURATION & DATA INJECTION ---
    const RAW_PRODUCTS = @json($preparedProducts);
    
    // UPDATED: Extended Store Information for Receipt
    const STORE_INFO = { 
        name: "{{ $store->name ?? 'My Store' }}", 
        company: "{{ $store->company ?? '' }}", 
        id: "{{ $storeId }}", 
        address: "{{ $store->address ?? 'Main Branch' }}", 
        phone: "{{ $store->phone ?? '' }}",
        email: "{{ $store->email ?? '' }}",
        website: "{{ $store->website ?? '' }}",
        thank_you: "{{ $store->receipt_thank_you ?? 'Thank you for your patronage!' }}",
        visit_again: "{{ $store->receipt_visit_again ?? 'Please visit us again.' }}"
    };

    const USER_INFO = { id: {{ $user->id ?? 0 }}, name: "{{ $user->name ?? 'Guest' }}" };
    const CURRENCY = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2 });
    const CHECKOUT_URL = "{{ route('checkout.process') }}";
    const UPDATES_URL = "{{ route('pos.updates') }}"; 
    let CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // --- 2. AUDIO UTILITIES ---
    let audioCtx = null;
    function initAudio() { 
        if(!audioCtx) {
            try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch(e) { console.warn('Audio not supported'); }
        } 
    }
    function playBeep() { 
        if(localStorage.getItem('pos_mute') === 'true') return; 
        if(!audioCtx) initAudio(); 
        if(audioCtx && audioCtx.state === 'suspended') audioCtx.resume(); 
        if(audioCtx) {
            const osc = audioCtx.createOscillator(); 
            const gain = audioCtx.createGain(); 
            osc.connect(gain); 
            gain.connect(audioCtx.destination); 
            osc.frequency.setValueAtTime(1000, audioCtx.currentTime); 
            gain.gain.setValueAtTime(0.05, audioCtx.currentTime); 
            osc.start(); 
            osc.stop(audioCtx.currentTime + 0.1); 
        }
    }
    function round(num) { return Math.round((num + Number.EPSILON) * 100) / 100; }

    // --- 3. MAIN APP CONTROLLER ---
    function app() {
        return {
            time: '', mobileView: 'products', soundEnabled: localStorage.getItem('pos_mute') !== 'true',
            get cartCount() { return document.getElementById('cart-count-hook')?.innerText || 0; },
            init() { setInterval(() => this.time = new Date().toLocaleTimeString('en-US', {hour12:true, hour:'numeric', minute:'2-digit'}), 1000); },
            initAudio, toggleSound() { this.soundEnabled = !this.soundEnabled; localStorage.setItem('pos_mute', !this.soundEnabled); },
            handleGlobalKeys(e) { if(['F2','F8','F9','F10'].includes(e.key)) e.preventDefault(); if(e.key === 'F2') { document.getElementById('product-search').focus(); this.mobileView = 'products'; } if(e.key === 'F8') this.$dispatch('pay-request', 'cash'); if(e.key === 'F9') this.$dispatch('pay-request', 'pos'); if(e.key === 'F10') this.$dispatch('pay-request', 'bank'); if(e.key === '?') this.$dispatch('toggle-shortcuts'); if(e.key === 'Escape') window.dispatchEvent(new CustomEvent('close-modals')); },
            confirmLogout(e) { const queue = JSON.parse(localStorage.getItem('pos_queue') || '[]'); if(queue.length > 0 && !confirm(`You have ${queue.length} orders pending sync. Logout anyway?`)) { return; } e.target.submit(); }
        }
    }

    // --- 4. PRODUCT GRID (CORE LOGIC) ---
    function productGrid() {
        return {
            all: RAW_PRODUCTS,
            filtered: RAW_PRODUCTS,
            search: '',
            limit: 24,
            lastSync: new Date().toISOString(), 
            
            init() {
                // A. Camera Listener
                window.addEventListener('camera-scan', (e) => {
                    const exact = this.all.find(p => p.b == e.detail);
                    if (exact) {
                        this.click(exact);
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Item Added', type: 'success' } }));
                    } else {
                        this.search = e.detail;
                        this.updateFilter();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Product not found', type: 'error' } }));
                    }
                });

                // B. Local Stock Optimistic Update
                window.addEventListener('local-stock-update', (e) => {
                    e.detail.forEach(item => {
                        const p = this.all.find(x => x.id == item.id);
                        if (p) p.s = p.s - item.qty; 
                    });
                    this.updateFilter();
                });

                // C. Server Polling (Every 5 seconds)
                setInterval(() => this.pollUpdates(), 5000);
            },

            async pollUpdates() {
                if (!navigator.onLine) return; 

                try {
                    const res = await fetch(`${UPDATES_URL}?last_sync=${this.lastSync}`);
                    if(!res.ok) return;

                    const data = await res.json();
                    this.lastSync = data.timestamp;

                    if (data.products && data.products.length > 0) {
                        let hasChanges = false;
                        
                        data.products.forEach(newItem => {
                            const index = this.all.findIndex(p => p.id === newItem.id);
                            
                            if (index !== -1) {
                                this.all[index] = newItem;
                                hasChanges = true;
                            } else {
                                this.all.unshift(newItem);
                                hasChanges = true;
                            }
                        });

                        if (hasChanges) {
                            this.updateFilter();
                            
                            // 1. Dispatch event to update Cart Items (Prices/Names)
                            window.dispatchEvent(new CustomEvent('sync-cart-items', { detail: data.products }));

                            // 2. Notification
                            let msg = `Synced: ${data.products.length} updates`;
                            if(data.products.length === 1) msg = `Updated: ${data.products[0].n}`;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: msg, type: 'success' } }));
                        }
                    }
                } catch (e) { console.error("Sync Error:", e); }
            },

            get visibleProducts() { return this.filtered.slice(0, this.limit); },
            get filteredCount() { return this.filtered.length; },
            
            updateFilter() {
                const s = this.search.toLowerCase().trim();
                this.limit = 24;
                if (!s) { this.filtered = this.all; return; }
                this.filtered = this.all.filter(p => p.n.toLowerCase().includes(s) || (p.b && p.b.includes(s)));
            },
            
            enterSearch() {
                const s = this.search.trim();
                if (!s) return;
                const exact = this.all.find(p => p.b == s);
                if (exact) { this.click(exact); this.search = ''; this.updateFilter(); }
            },
            
            click(p) {
                if (p.s <= 0) window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Low Stock Warning', type: 'error' } }));
                if (p.v && p.v.length) window.dispatchEvent(new CustomEvent('open-variant', { detail: p }));
                else window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { p } }));
            },
            
            format: (n) => CURRENCY.format(n)
        }
    }

    // --- 5. QR SCANNER COMPONENT ---
    function scannerComponent() { return { open: false, scanner: null, init() { window.addEventListener('toggle-scanner', () => { this.open ? this.stop() : this.start(); }); window.addEventListener('close-modals', () => this.stop()); }, start() { this.open = true; this.$nextTick(() => { if(this.scanner) return; this.scanner = new Html5Qrcode("reader"); this.scanner.start({ facingMode: "environment" }, { fps: 15, qrbox: { width: 250, height: 250 } }, (decodedText) => { playBeep(); this.stop(); window.dispatchEvent(new CustomEvent('camera-scan', { detail: decodedText })); }, (err) => {}).catch(err => { this.open = false; }); }); }, stop() { if (this.scanner) { this.scanner.stop().then(() => { this.scanner.clear(); this.scanner = null; this.open = false; }).catch(() => { this.open=false; }); } else { this.open = false; } } } }

    // --- 6. MOBILE LINK MODAL ---
    const mobileLinkModal = {
        show: false, sessionId: 'pos_' + Date.now(), connected: false, poller: null,
        init() { window.addEventListener('open-mobile-link', () => { this.show = true; this.generateQR(); this.startPolling(); }); window.addEventListener('close-modals', () => { this.show=false; this.stopPolling(); }); },
        generateQR() { this.$nextTick(() => { const url = window.location.origin + "/mobile-scanner/" + this.sessionId; new QRious({ element: document.getElementById('qr-canvas'), value: url, size: 200, level: 'H' }); }); },
        startPolling() { if(this.poller) return; this.poller = setInterval(() => { if(!this.show) return; fetch("{{ route('scanner.fetch') }}?session_id=" + this.sessionId)
            .then(res => res.json())
            .then(data => { if (data.scans && data.scans.length > 0) { this.connected = true; data.scans.forEach(scan => { playBeep(); window.dispatchEvent(new CustomEvent('camera-scan', { detail: scan.code })); }); } }); }, 1500); },
        stopPolling() { if(this.poller) { clearInterval(this.poller); this.poller = null; } },
        close() { this.show = false; this.stopPolling(); }
    };

    // --- 7. CART SIDEBAR (LOGIC & STATE) ---
    function cartSidebar() {
        return {
            sessions: {}, activeTab: null, refundMode: false,
            init() { 
                this.load(); 
                window.addEventListener('add-to-cart', e => this.add(e.detail.p, e.detail.v)); 
                window.addEventListener('pay-request', e => this.pay(e.detail)); 
                window.addEventListener('update-discount', e => this.setDiscount(e.detail)); 
                
                // CRITICAL: Listener to Auto-Update Cart Details
                window.addEventListener('sync-cart-items', e => {
                    const updatedProducts = e.detail;
                    const items = this.sessions[this.activeTab].items;
                    let cartChanged = false;

                    updatedProducts.forEach(prod => {
                        Object.keys(items).forEach(key => {
                            if (items[key].id === prod.id) {
                                if (items[key].n !== prod.n) { items[key].n = prod.n; cartChanged = true; }
                                
                                if (!items[key].vid) {
                                    if (items[key].p !== prod.p) { items[key].p = prod.p; cartChanged = true; }
                                } else {
                                    const v = prod.v.find(variant => variant.id == items[key].vid);
                                    if (v) {
                                        if (items[key].p !== v.p) { items[key].p = v.p; cartChanged = true; }
                                        if (items[key].v_name !== v.n) { items[key].v_name = v.n; cartChanged = true; }
                                    }
                                }
                            }
                        });
                    });

                    if (cartChanged) {
                        this.save();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Cart prices updated!', type: 'error' } }));
                    }
                });

                this.updateBadge(); 
            },
            toggleRefund() { this.refundMode = !this.refundMode; },
            load() { const s = localStorage.getItem('pos_cart_v5'); this.sessions = s ? JSON.parse(s) : { 't1': { number: 1, items: {}, discount: 0, discountType: 'fixed' } }; this.activeTab = Object.keys(this.sessions)[0] || 't1'; if(!this.sessions[this.activeTab]) this.sessions[this.activeTab] = { number: 1, items: {}, discount: 0, discountType: 'fixed' }; },
            save() { localStorage.setItem('pos_cart_v5', JSON.stringify(this.sessions)); this.updateBadge(); },
            createTab() { const id = 't' + Date.now(); this.sessions[id] = { number: Object.keys(this.sessions).length + 1, items: {}, discount: 0, discountType: 'fixed' }; this.activeTab = id; this.save(); },
            closeTab(id) { delete this.sessions[id]; if(Object.keys(this.sessions).length === 0) this.createTab(); else this.activeTab = Object.keys(this.sessions)[0]; this.save(); },
            switchTab(id) { this.activeTab = id; },
            get currentSession() { return this.sessions[this.activeTab]; }, get currentItems() { return this.currentSession?.items || {}; }, get count() { return Object.keys(this.currentItems).length; }, get discount() { return this.currentSession.discount || 0; }, get discountType() { return this.currentSession.discountType || 'fixed'; },
            get rawSubtotal() { return Object.values(this.currentItems).reduce((a, i) => a + (i.p * i.qty), 0); },
            get subtotal() { return CURRENCY.format(this.rawSubtotal); },
            get discountAmt() { let amt = 0; if(this.discountType === 'percent') amt = this.rawSubtotal * (this.discount / 100); else amt = this.discount; return CURRENCY.format(amt); },
            get totalVal() { let t = this.rawSubtotal; if(this.discountType === 'percent') t = t - (t * (this.discount / 100)); else t = t - this.discount; return round(t); },
            get total() { return CURRENCY.format(this.totalVal); },
            add(p, v=null) { playBeep(); const vid = v ? v.id : 'base'; const key = `${p.id}_${vid}`; const items = this.sessions[this.activeTab].items; const qtyToAdd = this.refundMode ? -1 : 1; if(items[key]) items[key].qty += qtyToAdd; else items[key] = { id: p.id, n: p.n, b: p.b, p: v?v.p:p.p, v_name: v?v.n:null, vid: v?v.id:null, qty: qtyToAdd }; if(items[key].qty === 0) delete items[key]; this.save(); if(window.innerWidth < 768) window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Updated Cart', type: 'success' } })); },
            mod(k, n) { const i = this.sessions[this.activeTab].items; if(i[k]) { i[k].qty += n; if(i[k].qty === 0) delete i[k]; this.save(); } },
            setDiscount(data) { this.sessions[this.activeTab].discount = data.val; this.sessions[this.activeTab].discountType = data.type; this.save(); },
            clear() { if(confirm('Clear cart?')) { this.sessions[this.activeTab].items = {}; this.sessions[this.activeTab].discount=0; this.save(); } },
            updateBadge() { const hook = document.querySelector('[x-show="cartCount > 0"]'); if(hook) hook.style.display = this.count > 0 ? 'block' : 'none'; },
            format: (n) => CURRENCY.format(Math.abs(n)),
            pay(method) {
                if(this.count === 0) return;
                const now = new Date();
                const orderData = { id: 'ORD-' + Math.floor(Date.now() / 1000), date: now.toLocaleString('en-NG'), iso_date: now.toISOString(), method: method, items: Object.values(this.currentItems), subtotal: this.subtotal, discount: this.discountAmt, total: this.total, store_id: STORE_INFO.id, raw_total: this.totalVal, original_user_id: USER_INFO.id, original_user_name: USER_INFO.name };
                window.dispatchEvent(new CustomEvent('local-stock-update', { detail: Object.values(this.currentItems) }));
                window.dispatchEvent(new CustomEvent('show-receipt', { detail: orderData })); 
                window.dispatchEvent(new CustomEvent('queue-push', { detail: orderData }));
                this.sessions[this.activeTab].items = {}; this.sessions[this.activeTab].discount = 0; this.save();
            }
        }
    }

    // --- 8. SMALL MODAL LOGIC & RECEIPT GENERATOR ---
    const variantModal={show:false,product:null,init(){window.addEventListener('open-variant',e=>{this.product=e.detail;this.show=true;});window.addEventListener('close-modals',()=>this.show=false);},select(v){window.dispatchEvent(new CustomEvent('add-to-cart',{detail:{p:this.product,v}}));this.show=false;},format:(n)=>CURRENCY.format(n)};
    const customItemModal={show:false,name:'',price:'',init(){window.addEventListener('open-custom-item',()=>{this.name='';this.price='';this.show=true;});window.addEventListener('close-modals',()=>this.show=false);},close(){this.show=false;},add(){if(!this.name||!this.price)return;const p={id:'custom-'+Date.now(),n:this.name,p:parseFloat(this.price),s:999};window.dispatchEvent(new CustomEvent('add-to-cart',{detail:{p}}));this.close();}};
    const discountModal={show:false,val:'',type:'fixed',init(){window.addEventListener('open-discount',()=>{this.show=true;this.$nextTick(()=>this.$refs.discInput.focus());});window.addEventListener('close-modals',()=>this.show=false);},close(){this.show=false;},apply(){window.dispatchEvent(new CustomEvent('update-discount',{detail:{val:parseFloat(this.val)||0,type:this.type}}));this.close();},remove(){window.dispatchEvent(new CustomEvent('update-discount',{detail:{val:0,type:'fixed'}}));this.close();}};
    
    // UPDATED RECEIPT MODAL
    const receiptModal={
        show:false, data:null,
        init(){window.addEventListener('show-receipt',e=>{this.data=e.detail;this.generateHtml();this.show=true;});window.addEventListener('close-modals',()=>this.show=false);},
        generateHtml(){
            if(!this.data)return;
            const itemsHtml=this.data.items.map(i=>`<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 6px;"><span style="max-width:70%; word-wrap:break-word; line-height:1.2;">${i.qty} x ${i.n} ${i.v_name?'<br><small>('+i.v_name+')</small>':''}</span><span style="font-weight:bold">${CURRENCY.format(i.p*i.qty)}</span></div>`).join('');
            
            document.getElementById('receipt-paper').innerHTML=`
                <div style="font-family: 'Space Mono', monospace; width: 100%; color: #000; font-size: 12px; line-height: 1.4;">
                    <div style="text-align:center; margin-bottom: 10px;">
                        <div style="font-size:16px; font-weight:800; text-transform:uppercase;">${STORE_INFO.company}</div>
                        ${STORE_INFO.company ? `<div style="font-size:10px; font-weight:bold; margin-top:2px;">${STORE_INFO.name}</div>` : ''}
                        ${STORE_INFO.phone ? `<div style="font-size:10px;">Tel: ${STORE_INFO.phone}</div>` : ''}
                        ${STORE_INFO.email ? `<div style="font-size:10px;">${STORE_INFO.email}</div>` : ''}
                        ${STORE_INFO.website ? `<div style="font-size:10px;">${STORE_INFO.website}</div>` : ''}
                    </div>
                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
                    <div style="display:flex; justify-content:space-between; font-size:10px;"><span>${this.data.date}</span><span>Ref: ${this.data.id.substr(-6)}</span></div>
                    <div style="font-size:10px; margin-bottom:5px;">Method: ${this.data.method.toUpperCase()}</div>
                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
                    <div style="margin-bottom: 10px;">${itemsHtml}</div>
                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
                    <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><span>₦${this.data.subtotal}</span></div>
                    ${parseFloat(this.data.discount.replace(/,/g,''))>0?`<div style="display:flex; justify-content:space-between;"><span>Discount</span><span>-₦${this.data.discount}</span></div>`:''}
                    <div style="display:flex; justify-content:space-between; font-weight:800; font-size:16px; margin-top:5px;"><span>TOTAL</span><span>₦${this.data.total}</span></div>
                    <div style="text-align:center; margin-top:20px; font-size:10px;">
                        <p>Cashier: ${this.data.original_user_name || 'Staff'}</p>
                        <p style="margin-top:10px; font-weight:bold;">${STORE_INFO.thank_you}</p>
                        <p style="margin-top:2px;">${STORE_INFO.visit_again}</p>
                    </div>
                </div>`;
        },
        print(){const content=document.getElementById('receipt-paper').innerHTML;const win=window.open('','','height=600,width=400');win.document.write('<html><head><title>Print</title><style>@import url("https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap"); body { font-family: "Space Mono", monospace; margin: 0; padding: 0; } @page { size: 72mm auto; margin: 0; } @media print { body { width: 100%; margin: 0 2mm; } }</style></head><body>');win.document.write(content);win.document.write('</body></html>');win.document.close();win.focus();setTimeout(()=>{win.print();win.close();},500);},
        close(){this.show=false;}
    };

    // --- 9. SYNC MANAGER (OFFLINE/ONLINE) ---
    function syncManager() {
        return {
            queue: [], online: navigator.onLine, syncing: false,
            init() { try { this.queue = JSON.parse(localStorage.getItem('pos_queue') || '[]'); } catch(e) { this.queue = []; } window.addEventListener('online', () => { this.online=true; this.process(); }); window.addEventListener('offline', () => this.online=false); window.addEventListener('queue-push', e => { this.queue.push(e.detail); this.save(); this.process(); }); if(this.online && this.queue.length) setTimeout(() => this.process(), 500); },
            save() { localStorage.setItem('pos_queue', JSON.stringify(this.queue)); },
            get queueCount() { return this.queue.length; },
            async process() {
                if(!this.online || this.syncing || this.queue.length === 0) return;
                this.syncing = true; const order = this.queue[0];
                const payload = { cart: order.items.map(i => ({ product_id: i.id.toString().startsWith('custom') ? null : i.id, custom_name: i.id.toString().startsWith('custom') ? i.n : null, variant_id: i.vid, quantity: i.qty, price: i.p })), paymentMethod: order.method, store_id: order.store_id, discount: order.discount.replace(/,/g, ''), total: order.total.replace(/,/g, ''), raw_total: order.raw_total, offline_user_id: order.original_user_id, offline_created_at: order.iso_date };
                try {
                    const res = await fetch(CHECKOUT_URL, { method: 'POST', headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF}, body: JSON.stringify(payload) });
                    const data = await res.json(); 
                    if(res.ok || res.status === 422) { 
                        this.queue.shift(); this.save(); 
                        if (data.updated_stock) window.dispatchEvent(new CustomEvent('stock-update', { detail: data.updated_stock })); 
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Order Synced', type: 'success' } })); 
                    } 
                    else if (res.status === 419) { 
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Session Expired - Reloading', type: 'error' } })); 
                        setTimeout(() => window.location.reload(), 2000); 
                    }
                } catch(e) {}
                this.syncing = false; 
                if(this.queue.length > 0 && this.online) setTimeout(() => this.process(), 2000);
            }
        }
    }
  </script>
</body>
</html>