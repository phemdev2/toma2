@extends('layouts.app')

@section('title', 'Edit Product & Inventory')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 font-sans text-slate-800">
    <div class="container mx-auto px-4 max-w-7xl">

        <!-- Top Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Product Management</h1>
                <p class="text-slate-500 text-sm mt-1">Update pricing, variants, and manage store inventory.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ url()->previous() }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" form="productForm" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Save Changes
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-lg flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('products.update', $product->id) }}" method="POST" id="productForm">
            @csrf
            @method('PUT')
            <div id="deleted-variants-container"></div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                
                <!-- LEFT COLUMN: Details & Stock (5 Columns) -->
                <div class="xl:col-span-5 space-y-6">
                    
                    <!-- 1. General Info -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-5">Product Details</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name</label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full bg-slate-50 border-transparent rounded-lg p-2.5 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500 transition" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Barcode</label>
                                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="w-full bg-slate-50 border-transparent rounded-lg p-2.5 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500 transition" required>
                            </div>
                        </div>
                    </div>

                    <!-- 2. STORE INVENTORY (Updated with Batch/Expiry) -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                Inventory by Store
                            </h3>
                        </div>
                        
                        <div class="divide-y divide-slate-100">
                            @foreach($stores as $store)
                                @php
                                    // Sum all batches for this store to get total visible quantity
                                    // Assumes $product->store_inventories is available via relationship
                                    $currentStock = $product->store_inventories->where('store_id', $store->id)->sum('quantity');
                                    
                                    // Visual Status
                                    $stockColor = $currentStock > 10 ? 'bg-emerald-100 text-emerald-700' : ($currentStock > 0 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                                @endphp
                                
                                <div class="p-5 group">
                                    <!-- Header: Store Name & Total Count -->
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 mr-3">
                                                <i class="fas fa-store text-xs"></i>
                                            </div>
                                            <div>
                                                <span class="block font-bold text-sm text-slate-800">{{ $store->name }}</span>
                                                <span class="text-[10px] text-slate-400 uppercase tracking-wide">Current Total</span>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $stockColor }}">
                                            {{ number_format($currentStock) }} Units
                                        </span>
                                    </div>

                                    <!-- Add Stock Form Area -->
                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                                        <h4 class="text-[10px] font-bold text-slate-400 uppercase mb-2">Receive New Stock</h4>
                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <!-- Qty Input -->
                                            <div class="col-span-2">
                                                <div class="relative">
                                                    <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">QTY</span>
                                                    <input type="number" name="inventory[{{ $store->id }}][quantity]" placeholder="0" class="pl-12 w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-300">
                                                </div>
                                            </div>
                                            <!-- Batch Input -->
                                            <div>
                                                <input type="text" name="inventory[{{ $store->id }}][batch_number]" placeholder="Batch #" class="w-full text-xs border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400">
                                            </div>
                                            <!-- Expiry Input -->
                                            <div>
                                                <input type="date" name="inventory[{{ $store->id }}][expiry_date]" class="w-full text-xs border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-slate-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Pricing Engine (7 Columns) -->
                <div class="xl:col-span-7 space-y-6">
                    
                    <!-- 1. BATCH CALCULATOR -->
                    <div class="bg-slate-900 rounded-2xl p-6 shadow-xl text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-600 rounded-full mix-blend-overlay filter blur-3xl opacity-20 -mr-10 -mt-10"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                            <div class="col-span-2 flex justify-between items-center border-b border-slate-700 pb-4 mb-2">
                                <h2 class="text-lg font-bold flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    Buying Cost Calculator
                                </h2>
                                <div class="text-right">
                                    <span class="block text-[10px] uppercase text-slate-400 tracking-wide">Base Cost (1 Unit)</span>
                                    <span class="text-2xl font-bold text-indigo-400 tracking-tight">₦<span id="base_cost_display">0.00</span></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">New Batch Cost</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-slate-500">₦</span>
                                    <input type="number" step="0.01" name="cost" id="master_cost" 
                                           class="w-full bg-slate-800 border border-slate-700 rounded-xl py-3 pl-8 pr-4 text-white font-bold text-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-600"
                                           value="{{ old('cost', $product->cost) }}" placeholder="e.g. 3050">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Qty inside Batch</label>
                                <input type="number" id="master_qty" 
                                       class="w-full bg-slate-800 border border-slate-700 rounded-xl py-3 px-4 text-white font-bold text-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-600"
                                       value="1" placeholder="e.g. 12">
                            </div>
                        </div>
                    </div>

                    <!-- 2. VARIANTS -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-slate-800">Sales Variants</h3>
                                <p class="text-xs text-slate-400 mt-1">Define selling prices (Singles, Cartons, etc)</p>
                            </div>
                            <button type="button" id="add-variant" class="text-xs bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-black transition shadow">
                                + Add Variant
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-bold">
                                    <tr>
                                        <th class="px-6 py-4">Name</th>
                                        <th class="px-6 py-4">Size & Cost</th>
                                        <th class="px-6 py-4">Sell Price</th>
                                        <th class="px-6 py-4 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="variants-container" class="divide-y divide-slate-100">
                                    @foreach($product->variants as $index => $variant)
                                        <tr class="group hover:bg-slate-50 transition variant-row">
                                            <td class="px-6 py-4 align-top">
                                                <input type="hidden" name="variant_id[]" value="{{ $variant->id }}">
                                                <input type="text" name="unit_type[]" 
                                                    class="w-full bg-transparent border-0 border-b border-slate-200 focus:ring-0 focus:border-indigo-500 p-0 text-sm font-semibold text-slate-800 placeholder-slate-300" 
                                                    value="{{ old('unit_type.'.$index, $variant->unit_type) }}" placeholder="e.g. Single" required>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-16">
                                                        <label class="text-[10px] uppercase text-slate-400 font-bold mb-1 block">Size</label>
                                                        <input type="number" name="unit_qty[]" 
                                                            class="variant-qty w-full text-center bg-white border border-slate-200 rounded-lg py-2 text-sm font-bold text-slate-700 focus:ring-indigo-500 focus:border-indigo-500" 
                                                            value="{{ old('unit_qty.'.$index, $variant->unit_qty) }}" required>
                                                    </div>
                                                    <div class="flex-1">
                                                        <label class="text-[10px] uppercase text-slate-400 font-bold mb-1 block">Cost</label>
                                                        <div class="bg-indigo-50/50 border border-indigo-100 rounded-lg py-2 px-2 text-indigo-700 font-mono text-sm font-bold flex items-center gap-1">
                                                            <span class="text-indigo-300 text-xs">₦</span>
                                                            <span class="cost-display">0.00</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <label class="text-[10px] uppercase text-slate-400 font-bold mb-1 block">Price</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-2.5 text-slate-400 text-sm">₦</span>
                                                    <input type="number" name="price[]" step="0.01" 
                                                        class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-slate-900 font-bold focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" 
                                                        value="{{ old('price.'.$index, $variant->price) }}" required>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-middle text-right">
                                                <button type="button" class="text-slate-300 hover:text-red-500 transition delete-variant" data-id="{{ $variant->id }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const masterCostInput = document.getElementById('master_cost');
        const masterQtyInput = document.getElementById('master_qty');
        const baseCostDisplay = document.getElementById('base_cost_display');
        
        function updateCosts() {
            const totalCost = parseFloat(masterCostInput.value) || 0;
            const totalQty = parseFloat(masterQtyInput.value) || 1;
            const baseUnitCost = totalCost / totalQty;
            
            baseCostDisplay.textContent = baseUnitCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            document.querySelectorAll('.variant-row').forEach(row => {
                const qtyInput = row.querySelector('.variant-qty');
                const costDisplay = row.querySelector('.cost-display');
                const variantSize = parseFloat(qtyInput.value) || 0;
                const specificCost = baseUnitCost * variantSize;
                costDisplay.textContent = specificCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });
        }

        masterCostInput.addEventListener('input', updateCosts);
        masterQtyInput.addEventListener('input', updateCosts);
        document.getElementById('variants-container').addEventListener('input', function(e) {
            if (e.target.classList.contains('variant-qty')) updateCosts();
        });

        document.getElementById('add-variant').addEventListener('click', () => {
            const container = document.getElementById('variants-container');
            const newRow = `
                <tr class="group hover:bg-slate-50 transition variant-row animate-fade-in">
                    <td class="px-6 py-4 align-top">
                        <input type="hidden" name="variant_id[]" value="">
                        <input type="text" name="unit_type[]" class="w-full bg-transparent border-0 border-b border-slate-200 focus:ring-0 focus:border-indigo-500 p-0 text-sm font-semibold text-slate-800" placeholder="Name" required>
                    </td>
                    <td class="px-6 py-4 align-top">
                        <div class="flex items-start gap-3">
                            <div class="w-16">
                                <label class="text-[10px] uppercase text-slate-400 font-bold mb-1 block">Size</label>
                                <input type="number" name="unit_qty[]" value="1" class="variant-qty w-full text-center bg-white border border-slate-200 rounded-lg py-2 text-sm font-bold text-slate-700 focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            <div class="flex-1">
                                <label class="text-[10px] uppercase text-slate-400 font-bold mb-1 block">Cost</label>
                                <div class="bg-indigo-50/50 border border-indigo-100 rounded-lg py-2 px-2 text-indigo-700 font-mono text-sm font-bold flex items-center gap-1">
                                    <span class="text-indigo-300 text-xs">₦</span>
                                    <span class="cost-display">0.00</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 align-top">
                        <label class="text-[10px] uppercase text-slate-400 font-bold mb-1 block">Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 text-sm">₦</span>
                            <input type="number" name="price[]" step="0.01" class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-slate-900 font-bold focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" required>
                        </div>
                    </td>
                    <td class="px-6 py-4 align-middle text-right">
                        <button type="button" class="text-slate-300 hover:text-red-500 transition delete-variant">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                </tr>
            `;
            container.insertAdjacentHTML('beforeend', newRow);
            updateCosts();
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-variant')) {
                const button = e.target.closest('.delete-variant');
                const row = button.closest('tr');
                const variantId = button.getAttribute('data-id');
                if (variantId) {
                    const deletedInput = document.createElement('input');
                    deletedInput.type = 'hidden';
                    deletedInput.name = 'deleted_variants[]';
                    deletedInput.value = variantId;
                    document.getElementById('deleted-variants-container').appendChild(deletedInput);
                }
                row.remove();
            }
        });

        updateCosts();
    });
</script>

<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection