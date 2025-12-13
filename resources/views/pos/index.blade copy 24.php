

<head>
  <meta charset="UTF-8" />
  <title>Modern POS System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="demo-token" />

  <!-- Tailwind v3 (Play CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
          },
          animation: {
            'slide-up': 'slideUp 0.3s ease-out',
            'fade-in': 'fadeIn 0.2s ease-out',
            'pulse-soft': 'pulseSoft 2s infinite'
          },
          keyframes: {
            slideUp: {
              '0%': { transform: 'translateY(10px)', opacity: '0' },
              '100%': { transform: 'translateY(0)', opacity: '1' }
            },
            fadeIn: {
              '0%': { opacity: '0' },
              '100%': { opacity: '1' }
            },
            pulseSoft: {
              '0%, 100%': { opacity: '1' },
              '50%': { opacity: '0.7' }
            }
          }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Icons + Alpine -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <style>
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    /* Smooth transitions */
    * {
      transition: all 0.15s ease;
    }

    input:focus,
    button:focus {
      transition: none;
    }

    /* Glass effect */
    .glass {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }

    @keyframes shrink {
      from {
        width: 100%;
      }

      to {
        width: 0%;
      }
    }
  </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-purple-50/30 to-blue-50/30 font-sans text-gray-800 min-h-screen">

  <!-- Status Bar (Online/Offline) -->
  <div x-data="{ online: navigator.onLine }" x-init="
         window.addEventListener('online', () => online = true);
         window.addEventListener('offline', () => online = false);
       " class="fixed top-0 left-0 right-0 h-1 z-50"
    :class="online ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gradient-to-r from-red-400 to-orange-500'">
  </div>

  <!-- Layout Wrapper -->
  <div class="flex w-full h-screen overflow-hidden pt-1">

    <!-- Cart Sidebar -->
    <div x-data="CartSidebar" x-init="$store.refs.cart = $data"
      class="glass border-r border-gray-200/50 shadow-xl z-40 w-full lg:w-2/5 h-screen flex flex-col">

      <!-- Cart Header -->
      <div
        class="p-5 border-b border-gray-200/50 bg-gradient-to-r from-purple-600 to-indigo-600 text-white sticky top-0 z-10">
        <div class="flex justify-between items-center mb-3">
          <h2 class="text-lg font-bold flex items-center gap-2">
            <i class="fas fa-shopping-basket"></i>
            Shopping Cart
            <span class="ml-2 px-2 py-0.5 text-xs bg-white/20 rounded-full">
              <span x-text="Object.keys(cart).length"></span> items
            </span>
          </h2>
          <button @click="clearCart()" x-show="Object.keys(cart).length > 0"
            class="text-xs px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur transition-all">
            <i class="fas fa-trash-alt mr-1"></i> Clear
          </button>
        </div>

        <!-- Quick Stats -->
        <div class="flex gap-4 text-xs">
          <div class="flex items-center gap-1">
            <i class="fas fa-boxes"></i>
            <span>Items: <span x-text="Object.values(cart).reduce((s,i) => s + i.quantity, 0)"
                class="font-semibold"></span></span>
          </div>
          <div class="flex items-center gap-1">
            <i class="fas fa-clock"></i>
            <span x-text="new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })"></span>
          </div>
        </div>
      </div>

      <!-- Cart Items -->
      <div class="flex-1 overflow-y-auto p-4 space-y-3" data-cart-list>
        <!-- Empty cart state -->
        <template x-if="Object.keys(cart).length === 0">
          <div class="flex flex-col items-center justify-center text-gray-400 h-full space-y-3 animate-fade-in">
            <div class="w-24 h-24 rounded-full bg-gray-100 flex items-center justify-center">
              <i class="fas fa-shopping-basket text-3xl"></i>
            </div>
            <p class="text-sm font-medium">Your cart is empty</p>
            <p class="text-xs text-center max-w-xs">Start adding products by clicking on them or using the search bar
            </p>
          </div>
        </template>

        <!-- Cart Items -->
        <template x-for="(item, key, index) in cart" :key="key">
          <div
            class="group bg-white rounded-xl p-4 shadow-sm hover:shadow-lg transition-all cursor-pointer animate-slide-up"
            :class="index === activeIndex ? 'ring-2 ring-purple-500 shadow-purple-100' : ''"
            @click="activeIndex = index" data-cart-row>

            <div class="flex justify-between items-start gap-3">
              <!-- Item details -->
              <div class="flex-1">
                <!-- Item Name with Icon -->
                <div class="flex items-start gap-2 mb-1">
                  <div
                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-box text-xs text-purple-600"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-sm text-gray-800 leading-tight" x-text="item.name"></p>
                    <p class="text-xs text-gray-500 mt-0.5">
                      <template x-if="item.variant">
                        <span>
                          <i class="fas fa-tag text-purple-400 mr-1"></i>
                          <span x-text="item.variant"></span> × <span x-text="item.unit_qty"></span>
                        </span>
                      </template>
                      <template x-if="!item.variant">
                        <span>Standard unit</span>
                      </template>
                    </p>
                  </div>
                </div>

                <!-- Price Info -->
                <div class="flex items-center gap-3 mt-2 text-xs">
                  <span class="text-gray-500">
                    ₦<span x-text="parseFloat(item.price).toFixed(2)"></span> each
                  </span>
                  <span class="text-purple-600 font-semibold">
                    Total: ₦<span x-text="(item.price * item.quantity).toFixed(2)"></span>
                  </span>
                </div>
              </div>

              <!-- Controls -->
              <div class="flex items-center gap-2">
                <div class="flex items-center rounded-lg border border-gray-200 overflow-hidden">
                  <button @click.stop="if(item.quantity > 1) { item.quantity--; saveCart(); }"
                    class="px-2 py-1 hover:bg-gray-100 text-gray-600">
                    <i class="fas fa-minus text-xs"></i>
                  </button>
                  <input type="number" min="1"
                    class="w-12 text-center border-x border-gray-200 text-sm font-semibold focus:outline-none"
                    x-model.number="item.quantity" @input="saveCart()" @click.stop>
                  <button @click.stop="item.quantity++; saveCart();" class="px-2 py-1 hover:bg-gray-100 text-gray-600">
                    <i class="fas fa-plus text-xs"></i>
                  </button>
                </div>
                <button @click.stop="removeItem(key)"
                  class="w-8 h-8 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 flex items-center justify-center">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Cart Footer -->
      <div class="p-5 border-t border-gray-200/50 glass sticky bottom-0 z-10">
        <!-- Total Display -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-4 mb-4 text-white">
          <div class="flex justify-between items-end">
            <div>
              <p class="text-xs opacity-90">Total Amount</p>
              <p class="text-3xl font-bold">₦<span x-text="cartTotal().toFixed(2)"></span></p>
            </div>
            <div class="text-right">
              <p class="text-xs opacity-90">Ready to checkout</p>
              <p class="text-sm font-semibold animate-pulse-soft">
                <i class="fas fa-check-circle mr-1"></i>
                <span x-text="Object.keys(cart).length"></span> items
              </p>
            </div>
          </div>
        </div>

        <!-- Payment Methods -->
        <div class="grid grid-cols-3 gap-2">
          <button @click="checkout('cash')" :disabled="Object.keys(cart).length === 0"
            class="group relative overflow-hidden rounded-lg py-3 font-semibold text-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white shadow-lg hover:shadow-xl">
            <i class="fas fa-money-bill-wave mr-1"></i> Cash
            <div
              class="absolute inset-0 bg-white/20 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-500">
            </div>
          </button>
          <button @click="checkout('bank')" :disabled="Object.keys(cart).length === 0"
            class="group relative overflow-hidden rounded-lg py-3 font-semibold text-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white shadow-lg hover:shadow-xl">
            <i class="fas fa-university mr-1"></i> Bank
            <div
              class="absolute inset-0 bg-white/20 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-500">
            </div>
          </button>
          <button @click="checkout('pos')" :disabled="Object.keys(cart).length === 0"
            class="group relative overflow-hidden rounded-lg py-3 font-semibold text-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-lg hover:shadow-xl">
            <i class="fas fa-credit-card mr-1"></i> POS
            <div
              class="absolute inset-0 bg-white/20 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-500">
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

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

      <!-- Product Grid Section -->
      <div x-data="ProductGrid" x-init="$store.refs.grid = $data" class="p-6 flex-1 overflow-hidden flex flex-col">

        <!-- Search & Filters -->
        <div class="sticky top-0 z-20 pb-4 bg-gradient-to-b from-slate-50/95 via-slate-50/80 to-transparent">
          <div class="flex gap-3">
            <!-- Search Bar -->
            <div class="flex-1 relative">
              <input type="text" x-model="query" @input="activeIndex = 0"
                placeholder="Search products or scan barcode..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm font-medium">
              <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <template x-if="query">
                <button @click="query = ''"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <i class="fas fa-times-circle"></i>
                </button>
              </template>
            </div>

            <!-- Filter Buttons -->
            <div class="hidden md:flex items-center gap-2">
              <button
                class="px-4 py-3 bg-white rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 text-sm font-medium text-gray-700 flex items-center gap-2">
                <i class="fas fa-filter"></i> Filter
              </button>
              <button
                class="px-4 py-3 bg-white rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 text-sm font-medium text-gray-700 flex items-center gap-2">
                <i class="fas fa-sort-amount-down"></i> Sort
              </button>
            </div>
          </div>

          <!-- Results Count -->
          <div class="flex justify-between items-center mt-3">
            <p class="text-xs text-gray-600">
              Showing <span x-text="filtered.length" class="font-semibold"></span> of <span x-text="products.length"
                class="font-semibold"></span> products
            </p>
            <div class="flex items-center gap-2 text-xs">
              <button class="px-3 py-1.5 rounded-lg bg-purple-100 text-purple-700 font-medium">All</button>
              <button class="px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-600">In Stock</button>
              <button class="px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-600">Low Stock</button>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <!-- Products Grid -->
        <div class="flex-1 overflow-y-auto">
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <template x-for="(product, index) in filtered" :key="product.id">
              <div
                class="group bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-purple-200 hover:-translate-y-1 transition-all cursor-pointer animate-fade-in"
                :class="index === activeIndex ? 'ring-2 ring-purple-500 shadow-purple-100' : ''" @click="add(product)"
                data-product-item>

                <!-- Product Info Only (No Image) -->
                <div class="p-4">
                  <div class="flex justify-between items-start mb-2">
                    <!-- Stock Badge -->
                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="getStock(product) > 10 ? 'bg-green-100 text-green-700' : getStock(product) > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'">
                      <i class="fas fa-circle text-[6px] mr-1"></i>
                      <span x-text="getStock(product) > 0 ? `${getStock(product)} in stock` : 'Out of stock'"></span>
                    </span>

                    <!-- Quick Add Icon -->
                    <div
                      class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-sm">
                      <i class="fas fa-plus"></i>
                    </div>
                  </div>

                  <h3 class="font-semibold text-sm text-gray-800 truncate mb-1" x-text="product.name"
                    :title="product.name"></h3>
                  <p class="text-xs text-gray-500 mb-2">
                    <i class="fas fa-barcode mr-1"></i>
                    <span x-text="product.barcode || 'No barcode'"></span>
                  </p>
                  <div class="flex items-end justify-between">
                    <p class="text-lg font-bold text-purple-600">
                      ₦<span x-text="parseFloat(product.sale).toFixed(2)"></span>
                    </p>
                    <template x-if="product.variants?.length">
                      <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                        <span x-text="product.variants.length"></span> variants
                      </span>
                    </template>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- No Results -->
          <template x-if="filtered.length === 0">
            <div class="flex flex-col items-center justify-center h-64 text-gray-400">
              <i class="fas fa-search text-4xl mb-3"></i>
              <p class="text-sm font-medium">No products found</p>
              <p class="text-xs">Try adjusting your search or filters</p>
            </div>
          </template>
        </div>

      </div>
    </div>
  </div>

  <!-- Enhanced Variant Modal -->
  <div x-data="VariantModal" x-show="show" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click.self="close()"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4"
    style="display: none;">
    <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95"
      x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95"
      class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-5 text-white">
        <h2 class="text-lg font-bold flex items-center gap-2">
          <i class="fas fa-layer-group"></i>
          Select Variant
        </h2>
        <p class="text-sm opacity-90 mt-1" x-text="product?.name"></p>
      </div>

      <!-- Variants List -->
      <div class="p-5 max-h-96 overflow-y-auto">
        <div class="space-y-3">
          <template x-for="(variant, index) in product?.variants" :key="variant.id">
            <label
              class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all"
              :class="index === selected ? 'border-purple-500 bg-purple-50' : ''">
              <input type="radio" name="variant" :value="index" class="w-4 h-4 text-purple-600 focus:ring-purple-500"
                :checked="index === selected" @change="select(index)">
              <div class="flex-1">
                <p class="font-semibold text-sm text-gray-800" x-text="variant.unit_type"></p>
                <p class="text-xs text-gray-600 mt-0.5">
                  Quantity: <span x-text="variant.unit_qty" class="font-semibold"></span> units
                </p>
              </div>
              <div class="text-right">
                <p class="text-lg font-bold text-purple-600">₦<span
                    x-text="parseFloat(variant.price).toFixed(2)"></span></p>
                <p class="text-xs text-gray-500">per pack</p>
              </div>
            </label>
          </template>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="p-5 bg-gray-50 flex justify-end gap-3">
        <button @click="close()"
          class="px-5 py-2.5 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium text-sm transition-all">
          Cancel
        </button>
        <button @click="add()"
          class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-medium text-sm shadow-lg hover:shadow-xl transition-all">
          <i class="fas fa-plus mr-1"></i> Add to Cart
        </button>
      </div>
    </div>
  </div>

  <!-- Enhanced Receipt Modal -->
  <div x-data="ReceiptModal" x-show="show" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" @click.self="close()"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4"
    style="display: none;">
    <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95"
      x-transition:enter-end="opacity-100 transform scale-100"
      class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl h-[85vh] flex flex-col overflow-hidden">

      <!-- Modal Header -->
      <div
        class="p-5 border-b bg-gradient-to-r from-purple-600 to-indigo-600 text-white flex justify-between items-center">
        <div>
          <h2 class="text-lg font-bold flex items-center gap-2">
            <i class="fas fa-receipt"></i> Receipt Preview
          </h2>
          <p class="text-sm opacity-90 mt-1">Transaction completed successfully</p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="
            if ($refs.receiptFrame && $refs.receiptFrame.contentWindow) {
              $refs.receiptFrame.contentWindow.focus();
              $refs.receiptFrame.contentWindow.print();
            }
          "
            class="px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur rounded-lg flex items-center gap-2 text-sm font-medium transition-all">
            <i class="fas fa-print"></i> Print
          </button>
          <button @click="close()"
            class="w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur flex items-center justify-center transition-all">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Receipt Preview -->
      <div class="flex-1 bg-gray-50 p-4">
        <div class="bg-white rounded-lg shadow-sm h-full">
          <iframe x-ref="receiptFrame" :src="url" class="w-full h-full rounded-lg border-0"></iframe>
          x-transition:enter-start="opacity-0 translate-x-6"
          x-transition:enter-end="opacity-100 translate-x-0"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100 translate-x-0"
          x-transition:leave-end="opacity-0 translate-x-6"
          class="relative w-full rounded-xl shadow-xl border overflow-hidden backdrop-blur-sm"
          :class="{
          'bg-green-50 border-green-200': toast.type === 'success',
          'bg-red-50 border-red-200': toast.type === 'error',
          'bg-yellow-50 border-yellow-200': toast.type === 'warning',
          'bg-blue-50 border-blue-200': toast.type === 'info'
          }">

          <!-- Accent Bar -->
          <div class="absolute left-0 top-0 bottom-0 w-1" :class="{
             'bg-green-500': toast.type === 'success',
             'bg-red-500': toast.type === 'error',
             'bg-yellow-500': toast.type === 'warning',
             'bg-blue-500': toast.type === 'info'
           }"></div>

          <!-- Content -->
          <div class="p-4 pl-5 flex items-start gap-3">
            <div class="flex-shrink-0">
              <i :class="{
              'fas fa-check-circle text-green-600': toast.type === 'success',
              'fas fa-exclamation-circle text-red-600': toast.type === 'error',
              'fas fa-exclamation-triangle text-yellow-600': toast.type === 'warning',
              'fas fa-info-circle text-blue-600': toast.type === 'info'
            }" class="text-lg"></i>
            </div>
            <div class="flex-1">
              <p class="font-semibold text-sm" :class="{
               'text-green-800': toast.type === 'success',
               'text-red-800': toast.type === 'error',
               'text-yellow-800': toast.type === 'warning',
               'text-blue-800': toast.type === 'info'
             }" x-text="toast.title"></p>
              <p class="text-sm text-gray-600 mt-0.5" x-text="toast.message"></p>
            </div>
            <button @click="remove(toast)" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        </template>
      </div>


      <!-- Keyboard Shortcuts Help Modal -->
      <div x-data="{ showHelp: false }" x-show="showHelp" @keydown.escape.window="showHelp = false"
        @keydown.shift.slash.window="showHelp = !showHelp" x-transition
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4"
        style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden">
          <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-5 text-white">
            <h2 class="text-lg font-bold flex items-center gap-2">
              <i class="fas fa-keyboard"></i> Keyboard Shortcuts
            </h2>
          </div>
          <div class="p-5 overflow-y-auto">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <h3 class="font-semibold text-sm text-gray-700 mb-3">Navigation</h3>
                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                  <span class="text-sm">Switch Mode</span>
                  <kbd class="px-2 py-1 bg-white rounded border text-xs font-mono">Tab</kbd>
                </div>
                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                  <span class="text-sm">Move Up/Down</span>
                  <kbd class="px-2 py-1 bg-white rounded border text-xs font-mono">↑ ↓</kbd>
                </div>
                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                  <span class="text-sm">Search Products</span>
                  <kbd class="px-2 py-1 bg-white rounded border text-xs font-mono">/</kbd>
                </div>
              </div>
              <div class="space-y-2">
                <h3 class="font-semibold text-sm text-gray-700 mb-3">Actions</h3>
                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                  <span class="text-sm">Add/Remove Item</span>
                  <kbd class="px-2 py-1 bg-white rounded border text-xs font-mono">Enter</kbd>
                </div>
                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                  <span class="text-sm">Clear Cart</span>
                  <kbd class="px-2 py-1 bg-white rounded border text-xs font-mono">C</kbd>
                </div>
                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                  <span class="text-sm">Checkout (Cash/Bank/POS)</span>
                  <kbd class="px-2 py-1 bg-white rounded border text-xs font-mono">1 2 3</kbd>
                </div>
              </div>
            </div>
          </div>
          <div class="p-5 bg-gray-50 flex justify-end">
            <button @click="showHelp = false"
              class="px-5 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-medium text-sm">
              Close
            </button>
          </div>
        </div>
      </div>

      <script>
        document.addEventListener('alpine:init', () => {
          // ----------------- Store Definitions -----------------
          Alpine.store('refs', { grid: null, cart: null });

          Alpine.store('cart', {
            items: {},
            save() {
              this.updateBadge();
            },
            updateBadge() {
              const count = Object.keys(this.items).length;
              document.title = count > 0 ? `(${count}) Modern POS System` : 'Modern POS System';
            },
            add(product, variant = null) {
              const v = variant ?? { unit_type: product.unit, price: product.sale, unit_qty: 1, id: null };
              const key = `${product.id}-${v.unit_type ?? 'default'}`;

              if (this.items[key]) {
                this.items[key].quantity++;
                Alpine.store('toast').show(`Updated quantity for ${product.name}`, "success");
              } else {
                this.items[key] = {
                  ...v,
                  name: product.name,
                  product_id: product.id,
                  variant: v.unit_type ?? null,
                  unit_qty: v.unit_qty,
                  quantity: 1,
                  price: v.price
                };
                Alpine.store('toast').show(`${product.name} added to cart`, "success");
              }
              this.save();

              // Animate cart icon
              const cartIcon = document.querySelector('[data-cart-list]');
              if (cartIcon) {
                cartIcon.classList.add('animate-pulse-soft');
                setTimeout(() => cartIcon.classList.remove('animate-pulse-soft'), 1000);
              }
            },
            remove(key) {
              const item = this.items[key];
              delete this.items[key];
              this.save();
              Alpine.store('toast').show(`${item.name} removed from cart`, "info");
            },
            clear() {
              this.items = {};
              this.save();
            },
            total() {
              return Object.values(this.items).reduce((s, i) => s + Number(i.price) * Number(i.quantity), 0);
            }
          });

          Alpine.store('mode', {
            current: 'products',
            toggle() {
              this.current = this.current === 'products' ? 'cart' : 'products';
              Alpine.store('toast').show(`Switched to ${this.current} mode`, "info");
            }
          });

          // ----------------- Cart Sidebar Component -----------------
          Alpine.data('CartSidebar', () => ({
            activeIndex: 0,
            get cart() { return Alpine.store('cart').items; },
            get keys() { return Object.keys(this.cart); },
            cartTotal() { return Alpine.store('cart').total(); },
            saveCart() { Alpine.store('cart').save(); },
            removeItem(key) { Alpine.store('cart').remove(key); },
            clearCart() {
              if (confirm('Are you sure you want to clear the cart?')) {
                Alpine.store('cart').clear();
                Alpine.store('toast').show("Cart cleared", "info");
              }
            },

            checkout(method) {
              if (Object.keys(this.cart).length === 0) {
                Alpine.store('toast').show("Cart is empty", "error", 4000);
                return;
              }

              const payload = {
                id: 'demo-' + Date.now(),
                cart: Object.values(this.cart),
                paymentMethod: method,
                store_id: 1,
                created_at: new Date().toISOString(),
                status: 'completed',
                total: this.cartTotal()
              };

              // Simulate successful checkout
              Alpine.store('toast').show("Payment successful!", "success");

              // Generate demo receipt URL
              const receiptData = encodeURIComponent(JSON.stringify(payload));
              const url = `data:text/html,${encodeURIComponent(this.generateReceipt(payload))}`;
              Alpine.store('receiptModal').open(url);
              Alpine.store('receiptModal').lastOrderUrl = url;

              Alpine.store('cart').clear();
            },

            generateReceipt(order) {
              return `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Receipt</title>
          <style>
            body { font-family: monospace; margin: 20px; line-height: 1.4; }
            .receipt { max-width: 300px; margin: 0 auto; }
            .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; }
            .item { display: flex; justify-content: space-between; margin: 5px 0; }
            .total { border-top: 1px dashed #000; padding-top: 10px; font-weight: bold; }
          </style>
        </head>
        <body>
          <div class="receipt">
            <div class="header">
              <h2>MODERN POS SYSTEM</h2>
              <p>Demo Store</p>
              <p>Receipt #${order.id}</p>
              <p>${new Date().toLocaleString()}</p>
            </div>
            <div class="items">
              ${order.cart.map(item => `
                <div class="item">
                  <span>${item.name} x${item.quantity}</span>
                  <span>₦${(item.price * item.quantity).toFixed(2)}</span>
                </div>
              `).join('')}
            </div>
            <div class="total">
              <div class="item">
                <span>TOTAL</span>
                <span>₦${order.total.toFixed(2)}</span>
              </div>
              <div class="item">
                <span>PAYMENT METHOD</span>
                <span>${order.paymentMethod.toUpperCase()}</span>
              </div>
            </div>
            <div style="text-align: center; margin-top: 20px;">
              <p>Thank you for your business!</p>
            </div>
          </div>
        </body>
        </html>
      `;
            },

            moveSelection(dir) {
              if (!this.keys.length) return;
              this.activeIndex = (this.activeIndex + dir + this.keys.length) % this.keys.length;
            },
            removeActive() {
              const key = this.keys[this.activeIndex];
              if (key) {
                this.removeItem(key);
                if (this.activeIndex >= this.keys.length) this.activeIndex = this.keys.length - 1;
              }
            },https://chatgpt.com/c/69282b3d-6d58-8328-958f-980d1f474445
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
          Alpine.data('ProductGrid', () => ({
            query: '',
            debouncedQuery: '',
            products: @json($products) || [],  // <-- ensure this is your product list
            activeIndex: 0,
            limit: 20,

            get filtered() {
              const k = this.debouncedQuery.toLowerCase();
              const matches = this.products.filter(p =>
                p.name.toLowerCase().includes(k) ||
                String(p.barcode).toLowerCase().includes(k)
              );
              console.log('Filtered called: k =', k, 'matches count =', matches.length);
              return matches.slice(0, this.limit);
            },

            get totalFiltered() {
              const k = this.debouncedQuery.toLowerCase();
              return this.products.filter(p =>
                p.name.toLowerCase().includes(k) ||
                String(p.barcode).toLowerCase().includes(k)
              ).length;
            },

            add(product) {
              if (product.variants?.length) Alpine.store('variantModal').open(product);
              else Alpine.store('cart').add(product);
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

            handleBarcodeScan(digit) {
              this.scanBuffer = (this.scanBuffer || '') + digit;
              clearTimeout(this._scanTimeout);
              this._scanTimeout = setTimeout(() => {
                if (this.scanBuffer.length >= 6) {
                  this.addByBarcode(this.scanBuffer);
                }
                this.scanBuffer = '';
              }, 250);
            },

            addByBarcode(code) {
              const product = this.products.find(p => String(p.barcode) === String(code));
              if (product) {
                this.add(product);
                Alpine.store('toast').show(`Scanned: ${product.name}`, "success");
              } else {
                Alpine.store('toast').show(`No product for barcode ${code}`, "error");
              }
            },

            init() {
              // sync debounced query
              setInterval(() => {
                this.debouncedQuery = this.query;
              }, 200);
              console.log('ProductGrid init, products length =', this.products.length);
            }
          }));


          // ----------------- Modals -----------------
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
              this.product = null;
            },
            select(i) {
              this.selected = i;
            },
            add() {
              const v = this.product.variants[this.selected];
              Alpine.store('cart').add(this.product, v);
              this.close();
            }
          });
          Alpine.data('VariantModal', () => Alpine.store('variantModal'));

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

          // ----------------- Toast System -----------------
          Alpine.store('toast', {
            toasts: [],
            show(message, type = 'success', timeout = null) {
              const durations = { success: 3000, error: 5000, info: 4000 };
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

          // ----------------- Global Keyboard Shortcuts -----------------
          document.addEventListener('keydown', (e) => {
            // Handle receipt modal shortcuts
            if (Alpine.store('receiptModal').show) {
              if (e.key === 'Escape') {
                e.preventDefault();
                Alpine.store('receiptModal').close();
              } else if (e.key === 'Enter') {
                e.preventDefault();
                const iframe = document.querySelector('[x-ref="receiptFrame"]');
                if (iframe?.contentWindow) {
                  iframe.contentWindow.focus();
                  iframe.contentWindow.print();
                }
              }
              return;
            }

            // Don't handle shortcuts when typing in inputs
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

            const mode = Alpine.store('mode').current;
            const grid = Alpine.store('refs').grid;
            const cart = Alpine.store('refs').cart;

            // Barcode scanning (numeric input in products mode)
            if (mode === 'products' && /^[0-9]$/.test(e.key)) {
              e.preventDefault();
              grid?.handleBarcodeScan(e.key);
              return;
            }

            // Global shortcuts
            const shortcuts = {
              'Tab': () => Alpine.store('mode').toggle(),
              'ArrowDown': () => mode === 'products' ? grid?.moveSelection(1) : cart?.moveSelection(1),
              'ArrowUp': () => mode === 'products' ? grid?.moveSelection(-1) : cart?.moveSelection(-1),
              'Enter': () => mode === 'products' ? grid?.addActive() : cart?.removeActive(),
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
              'P': () => Alpine.store('receiptModal').reprint()
            };

            if (shortcuts[e.key]) {
              e.preventDefault();
              shortcuts[e.key]();
            }
          });

          // Initialize cart badge
          Alpine.store('cart').updateBadge();
        });
      </script>

</body>

</html>