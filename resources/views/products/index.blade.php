@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
<div class="container mx-auto px-2 sm:px-6 py-6 max-w-7xl" x-data="productManager()">
    
    <!-- Notifications -->
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r shadow-sm flex justify-between items-center animate-fade-in">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
        </div>
    @endif
    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded-r shadow-sm flex justify-between items-center animate-fade-in">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white/90 backdrop-blur-sm hidden items-center justify-center z-50 transition-opacity">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-gray-100 border-t-purple-600"></div>
            <p class="mt-3 text-gray-500 font-medium text-sm tracking-wide">Processing...</p>
        </div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Inventory</h1>
            <p class="text-gray-500 text-sm mt-1">Manage {{ $productsWithVariants->total() }} total products</p>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Product
            </a>
            
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false" class="inline-flex items-center gap-2 border border-gray-300 bg-white text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export
                </button>
                <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-20 animate-fade-in">
                    <a href="{{ route('products.download.csv') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700">Download CSV</a>
                    <a href="{{ route('products.download.pdf') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700">Download PDF</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Bulk Actions -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form method="GET" action="{{ route('products.index') }}" id="searchForm" class="flex flex-col md:flex-row gap-4 items-center justify-between">
            
            <!-- Left: Bulk Actions (Hidden unless items selected) -->
            <div x-show="selected.length > 0" x-cloak class="flex items-center gap-3 w-full md:w-auto bg-purple-50 px-3 py-2 rounded-lg border border-purple-100 animate-fade-in">
                <span class="text-sm font-bold text-purple-700"><span x-text="selected.length"></span> Selected</span>
                <div class="h-4 w-px bg-purple-200"></div>
                <button type="button" @click="bulkDelete" class="text-xs font-bold text-red-600 hover:text-red-800 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </div>

            <!-- Right: Search & Filters -->
            <div x-show="selected.length === 0" class="flex flex-col sm:flex-row gap-3 w-full">
                <!-- Status Filter -->
                <div class="relative min-w-[150px]">
                    <select name="status" onchange="this.form.submit()" class="w-full appearance-none bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block p-2.5">
                        <option value="">All Status</option>
                        <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative flex-1 group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-purple-500 focus:border-purple-500 transition-shadow" placeholder="Search by name, barcode...">
                    @if(request('search') || request('status'))
                        <a href="{{ route('products.index') }}" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    @endif
                </div>
                <button type="submit" class="hidden sm:block bg-purple-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">Search</button>
            </div>
        </form>
    </div>

    @if($productsWithVariants->isEmpty())
        <div class="bg-white rounded-xl border-2 border-dashed border-gray-300 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900">No products found</h3>
            <p class="text-gray-500 mt-1 mb-6">Adjust your filters or add a new item to your inventory.</p>
            <a href="{{ route('products.create') }}" class="text-purple-600 hover:text-purple-800 font-medium hover:underline">Create Product &rarr;</a>
        </div>
    @else
        <!-- Desktop Table -->
        <div class="hidden lg:block bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                            <th class="p-4 w-4">
                                <input type="checkbox" @click="toggleAll" x-ref="checkboxAll" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer">
                            </th>
                            <th class="p-4">Product Info</th>
                            <th class="p-4">Stock Level</th>
                            <th class="p-4">Pricing</th>
                            <th class="p-4">Profit</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($productsWithVariants as $product)
                            @php
                                $totalStock = $product->storeInventories->sum('quantity');
                                $stockStatus = $totalStock == 0 ? 'Out' : ($totalStock < 10 ? 'Low' : 'Good');
                                $profit = $product->sale - $product->cost;
                                $profitPercent = $product->cost > 0 ? (($profit / $product->cost) * 100) : 100;
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="p-4">
                                    <input type="checkbox" value="{{ $product->id }}" x-model="selected" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900 text-sm">{{ $product->name }}</span>
                                        <span class="text-xs text-gray-500 font-mono mt-0.5 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                            {{ $product->barcode ?? 'No Barcode' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        @if($stockStatus == 'Out')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Out of Stock
                                            </span>
                                        @elseif($stockStatus == 'Low')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                Low ({{ $totalStock }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                In Stock ({{ $totalStock }})
                                            </span>
                                        @endif
                                    </div>
                                    @if($product->expiry_date)
                                        <div class="text-[10px] text-gray-400 mt-1">Exp: {{ \Carbon\Carbon::parse($product->expiry_date)->format('M d, Y') }}</div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="text-sm">
                                        <span class="text-gray-500 text-xs">Buy:</span> <span class="font-medium">&#8358;{{ number_format($product->cost, 2) }}</span><br>
                                        <span class="text-gray-500 text-xs">Sell:</span> <span class="font-bold text-gray-900">&#8358;{{ number_format($product->sale, 2) }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-xs font-bold {{ $profit > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $profit > 0 ? '+' : '' }}&#8358;{{ number_format($profit, 2) }}
                                    </span>
                                    <div class="w-16 bg-gray-200 rounded-full h-1 mt-1">
                                        <div class="bg-emerald-500 h-1 rounded-full" style="width: {{ min($profitPercent, 100) }}%"></div>
                                    </div>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <button onclick="openModal({{ $product->id }})" class="p-1.5 hover:bg-gray-100 rounded text-gray-600 hover:text-blue-600" title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <a href="{{ route('products.edit', $product->id) }}" class="p-1.5 hover:bg-gray-100 rounded text-gray-600 hover:text-amber-600" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <a href="#" class="p-1.5 hover:bg-gray-100 rounded text-gray-600 hover:text-purple-600" title="Print Barcode">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete this product?')" class="p-1.5 hover:bg-red-50 rounded text-gray-600 hover:text-red-600" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden space-y-4">
            @foreach($productsWithVariants as $product)
                @php
                    $totalStock = $product->storeInventories->sum('quantity');
                @endphp
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 relative">
                    <div class="absolute top-4 right-4">
                        <input type="checkbox" value="{{ $product->id }}" x-model="selected" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </div>
                    
                    <div class="pr-8" onclick="openModal({{ $product->id }})">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-500 font-mono">{{ $product->barcode }}</p>
                            </div>
                        </div>

                        <div class="flex gap-4 mb-3">
                            <div class="flex-1 bg-gray-50 p-2 rounded">
                                <span class="block text-[10px] uppercase text-gray-500 font-bold">Price</span>
                                <span class="block font-bold text-gray-900">&#8358;{{ number_format($product->sale, 2) }}</span>
                            </div>
                            <div class="flex-1 bg-gray-50 p-2 rounded">
                                <span class="block text-[10px] uppercase text-gray-500 font-bold">Stock</span>
                                <span class="block font-bold {{ $totalStock < 5 ? 'text-red-600' : 'text-gray-900' }}">{{ $totalStock }} Units</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('products.edit', $product->id) }}" class="text-center py-2 text-xs font-bold text-gray-700 bg-gray-50 rounded hover:bg-gray-100">Edit</a>
                        <button class="text-center py-2 text-xs font-bold text-purple-700 bg-purple-50 rounded hover:bg-purple-100">Barcode</button>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete?')" class="w-full text-center py-2 text-xs font-bold text-red-700 bg-red-50 rounded hover:bg-red-100">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Hidden Bulk Delete Form -->
        <form id="bulkDeleteForm" action="#" method="POST" class="hidden">
            @csrf @method('DELETE')
            <input type="hidden" name="ids" id="bulkDeleteIds">
        </form>

        <div class="mt-6">
            {{ $productsWithVariants->links('pagination::tailwind') }}
        </div>
    @endif
</div>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4 transition-all opacity-0">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 transition-all duration-300 max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Product Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="detailsContent" class="p-6 overflow-y-auto custom-scrollbar"></div>
        <div class="p-6 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <button onclick="closeModal()" class="w-full bg-white border border-gray-300 text-gray-700 font-medium py-2.5 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">Close</button>
        </div>
    </div>
</div>

<script>
    // AlpineJS Logic for Checkboxes
    function productManager() {
        return {
            selected: [],
            toggleAll() {
                const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selected"]');
                const allIds = Array.from(checkboxes).map(c => c.value);
                
                if (this.selected.length === allIds.length) {
                    this.selected = [];
                } else {
                    this.selected = allIds;
                }
            },
            bulkDelete() {
                if(confirm('Are you sure you want to delete ' + this.selected.length + ' products? This cannot be undone.')) {
                    // Logic to submit bulk delete (Implementation depends on your route)
                    // document.getElementById('bulkDeleteIds').value = JSON.stringify(this.selected);
                    // document.getElementById('bulkDeleteForm').submit();
                    alert('Bulk delete functionality ready to be linked to backend route.');
                }
            }
        }
    }

    // Modal Logic
    function openModal(id) {
        const modal = document.getElementById('detailsModal');
        const overlay = document.getElementById('loadingOverlay');
        const content = document.getElementById('detailsContent');
        
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        fetch(`/products/${id}`)
            .then(res => res.json())
            .then(data => {
                const profit = data.sale - data.cost;
                const margin = data.cost > 0 ? ((profit/data.cost)*100).toFixed(1) : 0;
                
                let html = `
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Product Name</p>
                                <p class="text-lg font-bold text-gray-900">${data.name}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Barcode</p>
                                <p class="font-mono text-gray-700 bg-gray-100 px-2 py-1 rounded text-sm">${data.barcode}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500">Cost Price</p>
                                <p class="font-medium text-gray-900">&#8358;${data.cost.toFixed(2)}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Sale Price</p>
                                <p class="font-bold text-gray-900 text-lg">&#8358;${data.sale.toFixed(2)}</p>
                            </div>
                            <div class="col-span-2 border-t border-gray-200 pt-3 flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Profit Margin</span>
                                <span class="px-2 py-1 rounded text-xs font-bold ${profit > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}">
                                    &#8358;${profit.toFixed(2)} (${margin}%)
                                </span>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Inventory Variants</p>
                            ${data.variants && data.variants.length ? 
                                `<div class="space-y-2">
                                    ${data.variants.map(v => `
                                        <div class="flex justify-between items-center p-3 border border-gray-200 rounded-lg hover:border-purple-300 transition-colors">
                                            <span class="text-sm font-medium text-gray-700">${v.unit_type}</span>
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Qty: ${v.unit_qty}</span>
                                                <span class="text-sm font-bold text-gray-900">&#8358;${v.price.toFixed(2)}</span>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>` 
                                : `<p class="text-sm text-gray-400 italic text-center py-4 bg-gray-50 rounded-lg">No variants defined</p>`}
                        </div>
                    </div>
                `;
                content.innerHTML = html;
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.querySelector('div').classList.remove('scale-95');
                    modal.querySelector('div').classList.add('scale-100');
                }, 10);
            })
            .catch(err => {
                console.error(err);
                overlay.classList.add('hidden');
            });
    }

    function closeModal() {
        const modal = document.getElementById('detailsModal');
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>
@endsection