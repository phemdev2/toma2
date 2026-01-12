@extends('layouts.app')

@section('title', 'Product Manager - ' . $product->name)

@section('content')
{{-- Ensure this meta tag exists in your layout <head>, or keep it here if not --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Main Container -->
<div class="min-h-screen bg-slate-50 py-8 font-sans text-slate-700">
    <div class="container mx-auto px-4 max-w-7xl">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Product Manager</h1>
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold uppercase tracking-wide">
                        {{ $product->category->name ?? 'Uncategorized' }}
                    </span>
                </div>
                <p class="text-slate-500 mt-1 text-sm">
                    Managing: <span class="font-bold text-slate-800">{{ $product->name }}</span> 
                    <span class="text-slate-400 mx-2">|</span> 
                    Barcode: <span class="font-mono text-slate-600">{{ $product->barcode }}</span>
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Custom Tab Switcher -->
                <div class="bg-white p-1.5 rounded-xl border border-slate-200 shadow-sm flex">
                    <button type="button" onclick="switchTab('settings')" id="btn-settings" class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 bg-slate-900 text-white shadow-md flex items-center gap-2">
                        <i class="fas fa-sliders-h"></i> Configuration
                    </button>
                    <button type="button" onclick="switchTab('audit')" id="btn-audit" class="px-5 py-2.5 rounded-lg text-sm font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-chart-pie"></i> Audit & Profits
                    </button>
                </div>
                
                <a href="{{ route('products.index') }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-colors shadow-sm" title="Back to List">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>

        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed top-24 right-5 z-50 flex flex-col gap-3 pointer-events-none">
            @if(session('success'))
                <div class="bg-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-4 animate-fade-in-up">
                    <i class="fas fa-check text-lg"></i> <span class="font-bold text-sm">{{ session('success') }}</span>
                </div>
            @endif
        </div>

        <!-- ==========================================
             TAB 1: SETTINGS (Calculations & Form)
             ========================================== -->
        <div id="tab-settings" class="tab-content block animate-fade-in-up">
            <form action="{{ route('products.update', $product->id) }}" method="POST" id="productForm">
                @csrf
                @method('PUT')
                <div id="deleted-variants-container"></div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    
                    <!-- LEFT COLUMN: Basic Info & Quick Stock -->
                    <div class="xl:col-span-1 space-y-6">
                        
                        <!-- Card: Identity -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-4">Product Identity</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product Name</label>
                                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full bg-slate-50 border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-800 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barcode / SKU</label>
                                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="w-full bg-slate-50 border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm text-slate-600">
                                </div>
                            </div>
                        </div>

                        <!-- Card: Default Single Unit Pricing -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 z-0"></div>
                            <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-4 relative z-10">Single Unit Pricing</h3>
                            
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Selling Price (Single)</label>
                                <div class="relative group">
                                    <span class="absolute left-3 top-3 text-slate-400 font-bold">₦</span>
                                    <input type="number" step="any" name="sale" id="simple_sale_price"
                                        class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-lg text-lg font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm group-hover:border-indigo-300 transition-colors" 
                                        value="{{ old('sale', $product->sale) }}" placeholder="0.00">
                                </div>
                            </div>

                            <!-- Live Profit Calculation for Single Unit -->
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-100 flex justify-between items-center">
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Base Cost</div>
                                    <div class="font-mono text-sm font-bold text-slate-600">₦<span id="display_base_cost_small">0.00</span></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Est. Profit</div>
                                    <div id="simple_profit_display" class="font-bold text-sm text-emerald-600">₦0.00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Quick Stock Intake -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800 text-sm"><i class="fas fa-truck-loading mr-2 text-slate-400"></i>Receive Stock</h3>
                            </div>
                            <div class="divide-y divide-slate-100 max-h-[300px] overflow-y-auto custom-scrollbar">
                                @foreach($stores as $store)
                                    @php
                                        $currentStock = $product->storeInventories->where('store_id', $store->id)->sum('quantity');
                                    @endphp
                                    <div class="p-4 hover:bg-slate-50 transition-colors">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-bold text-xs text-slate-700 uppercase">{{ $store->name }}</span>
                                            <span class="text-[10px] bg-white border border-slate-200 text-slate-500 px-2 py-0.5 rounded shadow-sm">
                                                Current: {{ number_format($currentStock) }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <!-- Note: Key by store ID for easy controller handling -->
                                            <input type="number" name="inventory[{{ $store->id }}][quantity]" placeholder="+ Qty" class="w-full text-xs border-slate-200 rounded focus:ring-indigo-500 py-1.5">
                                            <input type="text" name="inventory[{{ $store->id }}][batch_number]" placeholder="Batch #" class="w-full text-xs border-slate-200 rounded focus:ring-indigo-500 py-1.5">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- CENTER & RIGHT COLUMN: The Calculator Engine -->
                    <div class="xl:col-span-2 space-y-6">
                        
                        <!-- THE CALCULATOR CONFIGURATION -->
                        <div class="bg-slate-900 rounded-2xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden group">
                            <!-- Background Decoration -->
                            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600 rounded-full mix-blend-overlay filter blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-700"></div>
                            
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 relative z-10 border-b border-slate-700/50 pb-6">
                                <div>
                                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                        <i class="fas fa-calculator text-indigo-400"></i> Costing Engine
                                    </h2>
                                    <p class="text-xs text-slate-400 mt-1 max-w-sm">
                                        Enter supplier details (Pack Cost & Qty). Unit costs and margins for variants are auto-calculated.
                                    </p>
                                </div>
                                <div class="mt-4 md:mt-0 text-left md:text-right bg-slate-800/50 p-3 rounded-xl border border-slate-700 backdrop-blur-sm">
                                    <span class="block text-[10px] uppercase text-slate-400 tracking-wider mb-1">Calculated Unit Cost</span>
                                    <span class="text-3xl font-extrabold text-indigo-400 tracking-tight">
                                        ₦<span id="base_cost_display">0.00</span>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Cost of Bulk Pack</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-3.5 text-slate-500 text-lg">₦</span>
                                        <input type="number" step="any" name="cost" id="master_cost" 
                                            class="w-full bg-slate-800 border border-slate-600 rounded-xl py-3 pl-10 pr-4 text-white font-bold text-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-inner transition-all placeholder-slate-600"
                                            value="{{ old('cost', $product->cost) }}" placeholder="e.g. 5000">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-indigo-300 uppercase mb-2">Quantity Inside Pack</label>
                                    <div class="relative">
                                        <span class="absolute right-4 top-3.5 text-slate-500 text-xs font-bold uppercase">Units</span>
                                        <input type="number" name="quantity_in_pack" id="master_qty" 
                                            class="w-full bg-indigo-900/40 border border-indigo-500/50 rounded-xl py-3 px-4 text-white font-bold text-xl focus:ring-2 focus:ring-indigo-400 focus:border-transparent shadow-inner transition-all"
                                            value="{{ old('quantity_in_pack', $product->quantity_in_pack ?? 1) }}" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- VARIANTS TABLE (Smart Sheet) -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-5 border-b border-slate-200 flex flex-wrap justify-between items-center gap-3 bg-slate-50/50">
                                <div>
                                    <h3 class="font-bold text-slate-800 text-lg">Sales Variants</h3>
                                    <p class="text-xs text-slate-500">Define selling units (e.g., Dozen, Carton).</p>
                                </div>
                                <button type="button" id="add-variant" class="text-xs bg-slate-800 text-white px-4 py-2.5 rounded-lg hover:bg-slate-900 transition shadow-md flex items-center gap-2">
                                    <i class="fas fa-plus-circle"></i> Add Variant
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold tracking-wider border-b border-slate-200">
                                        <tr>
                                            <th class="px-5 py-4 w-1/4">Variant Name</th>
                                            <th class="px-5 py-4 w-1/5">Size (Units)</th>
                                            <th class="px-5 py-4 w-1/4">Selling Price</th>
                                            <th class="px-5 py-4 w-1/5">Calculated Margin</th>
                                            <th class="px-5 py-4 text-right w-[10%]"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="variants-container" class="divide-y divide-slate-100">
                                        @foreach($product->variants as $index => $variant)
                                            <tr class="group hover:bg-indigo-50/30 transition-colors variant-row" data-id="{{ $variant->id }}">
                                                <td class="px-5 py-4 align-top">
                                                    <input type="hidden" name="existing_variants[{{ $variant->id }}][id]" value="{{ $variant->id }}">
                                                    <input type="text" name="existing_variants[{{ $variant->id }}][unit_type]" 
                                                        class="variant-name w-full bg-transparent border-0 border-b-2 border-slate-200 focus:border-indigo-500 focus:ring-0 text-sm font-bold text-slate-800 px-0 pb-1 placeholder-slate-300" 
                                                        value="{{ $variant->unit_type }}" required placeholder="Name">
                                                </td>
                                                <td class="px-5 py-4 align-top">
                                                    <input type="number" name="existing_variants[{{ $variant->id }}][unit_qty]" 
                                                        class="variant-qty w-full bg-white border border-slate-200 rounded-md py-1.5 px-3 text-sm font-bold text-center focus:ring-indigo-500 focus:border-indigo-500" 
                                                        value="{{ $variant->unit_qty }}" required>
                                                    <div class="mt-1 text-center">
                                                        <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Cost: ₦<span class="cost-display">0.00</span></span>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 align-top">
                                                    <div class="relative">
                                                        <span class="absolute left-3 top-2 text-slate-400 text-xs">₦</span>
                                                        <input type="number" name="existing_variants[{{ $variant->id }}][price]" step="any" 
                                                            class="variant-price w-full pl-6 pr-3 py-1.5 border border-slate-200 rounded-md text-slate-900 font-bold text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" 
                                                            value="{{ $variant->price }}" required>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 align-middle">
                                                    <!-- Dynamic Profit Badge -->
                                                    <div class="profit-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                                                        <span class="mr-1">--</span>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 align-middle text-right flex justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                                    {{-- AJAX Save Button --}}
                                                    <button type="button" class="text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white p-2 rounded-lg transition-all save-row shadow-sm" title="Quick Save">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                    <button type="button" class="text-rose-400 bg-rose-50 hover:bg-rose-500 hover:text-white p-2 rounded-lg transition-all delete-variant shadow-sm" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Action Bar -->
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="w-full md:w-auto px-10 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3">
                                <i class="fas fa-check-circle"></i> Save All Changes
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>

       <!-- ==========================================
     TAB 2: STOCK & VALUATION REPORT (Professional ERP Style)
     ========================================== -->
<div id="tab-audit" class="tab-content hidden animate-fade-in-up">
    
    <!-- Top Action Toolbar -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
        <h3 class="text-slate-800 font-bold text-lg flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar text-slate-400"></i> Stock Valuation & Movement
        </h3>
        <div class="flex gap-2">
            <button type="button" class="text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded border border-slate-300 transition flex items-center gap-2">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
            <button type="button" class="text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded border border-slate-300 transition flex items-center gap-2">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Executive Financial Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Metric 1 -->
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm border-l-4 border-l-slate-600">
            <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Total Quantity on Hand</div>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-800 font-mono">{{ number_format(collect($analytics)->sum('current_stock')) }}</span>
                <span class="text-xs text-slate-400">Units</span>
            </div>
        </div>

        <!-- Metric 2 -->
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm border-l-4 border-l-slate-600">
            <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Inventory Valuation (Cost)</div>
            <div class="mt-1">
                <span class="text-2xl font-bold text-slate-800 font-mono">₦{{ number_format(collect($analytics)->sum('stock_value'), 2) }}</span>
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Weighted Avg. Cost</p>
        </div>

        <!-- Metric 3 -->
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm border-l-4 border-l-indigo-600">
            <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Projected Revenue</div>
            <div class="mt-1">
                <span class="text-2xl font-bold text-indigo-700 font-mono">₦{{ number_format((collect($analytics)->sum('stock_value') + collect($analytics)->sum('projected_profit')), 2) }}</span>
            </div>
            <p class="text-[10px] text-slate-400 mt-1">@ 100% Sell-through</p>
        </div>

        <!-- Metric 4 -->
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm border-l-4 border-l-emerald-600">
            <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Gross Margin Est.</div>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-700 font-mono">₦{{ number_format(collect($analytics)->sum('projected_profit'), 2) }}</span>
            </div>
            @php
                $totalRevenue = collect($analytics)->sum('stock_value') + collect($analytics)->sum('projected_profit');
                $marginPercent = $totalRevenue > 0 ? (collect($analytics)->sum('projected_profit') / $totalRevenue) * 100 : 0;
            @endphp
            <p class="text-[10px] text-emerald-600 mt-1 font-bold">+{{ number_format($marginPercent, 1) }}% Margin</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Inventory Location Breakdown (Table) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                <h4 class="text-xs font-bold uppercase text-slate-700">Location Breakdown</h4>
                <span class="text-[10px] text-slate-500">As of {{ now()->format('Y-m-d H:i') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[10px] text-slate-500 uppercase bg-slate-100 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Warehouse / Store</th>
                            <th class="px-5 py-3 text-right font-semibold">Physical Stock</th>
                            <th class="px-5 py-3 text-right font-semibold">Unit Cost</th>
                            <th class="px-5 py-3 text-right font-semibold">Total Valuation</th>
                            <th class="px-5 py-3 text-center font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($analytics ?? [] as $data)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3 font-medium text-slate-700">
                                    {{ $data['store_name'] }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-slate-600">
                                    {{ number_format($data['current_stock']) }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-slate-500 text-xs">
                                    {{-- Calculating Unit Cost based on total value / stock --}}
                                    ₦{{ $data['current_stock'] > 0 ? number_format($data['stock_value'] / $data['current_stock'], 2) : '0.00' }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-700">
                                    ₦{{ number_format($data['stock_value'], 2) }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($data['current_stock'] <= 5)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                            Low Stock
                                        </span>
                                    @elseif($data['current_stock'] > 1000)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Overstock
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">
                                            Healthy
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs">No inventory records found.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200">
                        <tr>
                            <td class="px-5 py-3 font-bold text-slate-800 text-xs uppercase">Totals</td>
                            <td class="px-5 py-3 text-right font-bold font-mono text-slate-800">{{ number_format(collect($analytics)->sum('current_stock')) }}</td>
                            <td class="px-5 py-3"></td>
                            <td class="px-5 py-3 text-right font-bold font-mono text-slate-800">₦{{ number_format(collect($analytics)->sum('stock_value'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Audit Ledger (Terminal Style) -->
        <div class="lg:col-span-1 bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col h-full max-h-[500px]">
            <div class="bg-slate-800 px-4 py-3 border-b border-slate-700 flex justify-between items-center">
                <h4 class="text-xs font-bold uppercase text-slate-200">Audit Trail</h4>
                <span class="text-[10px] text-slate-400">Last 10 Events</span>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50 p-0">
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-slate-200">
                        @forelse($auditHistory ?? [] as $history)
                            <tr class="hover:bg-white transition-colors">
                                <td class="px-4 py-3 align-top">
                                    <div class="font-mono text-[10px] text-slate-400 mb-0.5">
                                        {{ $history->created_at->format('d/m/y H:i') }}
                                    </div>
                                    <div class="font-bold text-slate-700">
                                        {{ $history->type == 'intake' ? 'Stock Intake' : 'Correction/Sale' }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        User: {{ $history->user->name ?? 'System' }} | Ref: <span class="font-mono">{{ $history->batch_number ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right align-middle">
                                    @if($history->quantity > 0)
                                        <div class="text-emerald-700 font-bold font-mono bg-emerald-50 px-2 py-1 rounded inline-block border border-emerald-100">
                                            +{{ $history->quantity }}
                                        </div>
                                    @else
                                        <div class="text-rose-700 font-bold font-mono bg-rose-50 px-2 py-1 rounded inline-block border border-rose-100">
                                            {{ $history->quantity }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="p-8 text-center text-slate-400 italic">
                                    No transaction history available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-white border-t border-slate-200 text-center">
                <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline">View Full Ledger History</a>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<script>
    // --- UI Logic (Tabs & Toasts) ---
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-' + tabName).classList.remove('hidden');
        
        // Button Styles
        const btnClassesInactive = "px-5 py-2.5 rounded-lg text-sm font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all duration-200 flex items-center gap-2";
        const btnClassesActive = "px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 bg-slate-900 text-white shadow-md flex items-center gap-2";
        
        document.getElementById('btn-settings').className = btnClassesInactive;
        document.getElementById('btn-audit').className = btnClassesInactive;
        document.getElementById('btn-' + tabName).className = btnClassesActive;
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const el = document.createElement('div');
        const colorClass = type === 'success' ? 'bg-emerald-600' : (type === 'info' ? 'bg-blue-600' : 'bg-rose-600');
        const icon = type === 'success' ? 'fa-check' : (type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle');
        
        el.className = `${colorClass} text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-4 min-w-[320px] transform transition-all duration-500 translate-x-10 opacity-0`;
        el.innerHTML = `<i class="fas ${icon} text-lg"></i> <span class="font-bold text-sm">${message}</span>`;
        
        container.appendChild(el);
        requestAnimationFrame(() => el.classList.remove('translate-x-10', 'opacity-0'));
        setTimeout(() => {
            el.classList.add('translate-x-10', 'opacity-0');
            setTimeout(() => el.remove(), 500);
        }, 3000);
    }

    // --- The Calculation Engine ---
    document.addEventListener('DOMContentLoaded', () => {
        const dom = {
            masterCost: document.getElementById('master_cost'),
            masterQty: document.getElementById('master_qty'),
            baseCostDisplay: document.getElementById('base_cost_display'),
            baseCostSmall: document.getElementById('display_base_cost_small'),
            simpleSale: document.getElementById('simple_sale_price'),
            simpleProfit: document.getElementById('simple_profit_display'),
            variantsContainer: document.getElementById('variants-container'),
            deletedVariantsContainer: document.getElementById('deleted-variants-container')
        };

        function runCalculations() {
            // 1. Determine Unit Cost
            const packCost = parseFloat(dom.masterCost.value) || 0;
            const packQty = parseFloat(dom.masterQty.value) || 1;
            const unitCost = packQty > 0 ? (packCost / packQty) : 0;

            // 2. Update Global Displays
            const fmtUnitCost = unitCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            dom.baseCostDisplay.textContent = fmtUnitCost;
            if(dom.baseCostSmall) dom.baseCostSmall.textContent = fmtUnitCost;

            // 3. Update Simple Pricing Profit
            if(dom.simpleSale && dom.simpleProfit) {
                const simplePrice = parseFloat(dom.simpleSale.value) || 0;
                const profit = simplePrice - unitCost;
                const margin = simplePrice > 0 ? ((profit / simplePrice) * 100).toFixed(1) : 0;
                
                dom.simpleProfit.textContent = `₦${profit.toFixed(2)} (${margin}%)`;
                
                if(profit < 0) {
                    dom.simpleProfit.className = "font-bold text-sm text-rose-600";
                    dom.simpleSale.classList.add('border-rose-300', 'bg-rose-50');
                } else {
                    dom.simpleProfit.className = "font-bold text-sm text-emerald-600";
                    dom.simpleSale.classList.remove('border-rose-300', 'bg-rose-50');
                }
            }

            // 4. Update Each Variant Row
            document.querySelectorAll('.variant-row').forEach(row => {
                const qtyInput = row.querySelector('.variant-qty');
                const priceInput = row.querySelector('.variant-price');
                const costDisplay = row.querySelector('.cost-display');
                const profitBadge = row.querySelector('.profit-badge');

                if(!qtyInput) return;

                const variantSize = parseFloat(qtyInput.value) || 0;
                const variantCost = unitCost * variantSize;
                
                costDisplay.textContent = variantCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                const sellingPrice = parseFloat(priceInput.value) || 0;
                const profit = sellingPrice - variantCost;
                const marginPercent = sellingPrice > 0 ? ((profit / sellingPrice) * 100).toFixed(0) : 0;

                if(sellingPrice > 0) {
                    if(profit >= 0) {
                        profitBadge.className = "profit-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700";
                        profitBadge.innerHTML = `<i class="fas fa-arrow-up mr-1 text-[10px]"></i> ₦${profit.toFixed(0)} (${marginPercent}%)`;
                    } else {
                        profitBadge.className = "profit-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700";
                        profitBadge.innerHTML = `<i class="fas fa-arrow-down mr-1 text-[10px]"></i> -₦${Math.abs(profit).toFixed(0)} (Loss)`;
                    }
                } else {
                    profitBadge.className = "profit-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-400";
                    profitBadge.innerHTML = "Set Price";
                }
            });
        }

        // Event Listeners
        [dom.masterCost, dom.masterQty, dom.simpleSale].forEach(el => el.addEventListener('input', runCalculations));
        dom.variantsContainer.addEventListener('input', (e) => {
            if(e.target.matches('input')) runCalculations();
        });

        // Add Variant Logic
        document.getElementById('add-variant').addEventListener('click', () => {
            // New variants use "new_variants[]" array naming convention
            const row = `
                <tr class="group hover:bg-indigo-50/30 transition-colors variant-row animate-fade-in-up">
                    <td class="px-5 py-4 align-top">
                        <input type="text" name="new_variants_unit_type[]" class="variant-name w-full bg-transparent border-0 border-b-2 border-slate-200 focus:border-indigo-500 focus:ring-0 text-sm font-bold text-slate-800 px-0 pb-1" placeholder="e.g. Pack of 6" required>
                    </td>
                    <td class="px-5 py-4 align-top">
                        <input type="number" name="new_variants_unit_qty[]" value="1" class="variant-qty w-full bg-white border border-slate-200 rounded-md py-1.5 px-3 text-sm font-bold text-center focus:ring-indigo-500 focus:border-indigo-500" required>
                        <div class="mt-1 text-center">
                             <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Cost: ₦<span class="cost-display">0.00</span></span>
                        </div>
                    </td>
                    <td class="px-5 py-4 align-top">
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-400 text-xs">₦</span>
                            <input type="number" name="new_variants_price[]" step="any" class="variant-price w-full pl-6 pr-3 py-1.5 border border-slate-200 rounded-md text-slate-900 font-bold text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" placeholder="0.00" required>
                        </div>
                    </td>
                    <td class="px-5 py-4 align-middle">
                        <div class="profit-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500"><span class="mr-1">--</span></div>
                    </td>
                    <td class="px-5 py-4 align-middle text-right">
                        <button type="button" class="text-slate-400 hover:text-rose-500 p-2 transition delete-variant"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `;
            dom.variantsContainer.insertAdjacentHTML('beforeend', row);
            runCalculations();
        });

        // Delete & Save Logic
        dom.variantsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if(!btn) return;
            const row = btn.closest('tr');
            const id = row.getAttribute('data-id');

            // DELETE
            if(btn.classList.contains('delete-variant')) {
                if(id) {
                    if(!confirm('Delete this variant?')) return;
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'deleted_variants[]';
                    input.value = id;
                    dom.deletedVariantsContainer.appendChild(input);
                    showToast('Marked for deletion. Click Save.', 'info');
                    row.classList.add('opacity-50', 'bg-rose-50');
                } else {
                    row.remove();
                }
            }

            // AJAX SAVE (Single Row)
            if(btn.classList.contains('save-row') && id) {
                const nameInput = row.querySelector('.variant-name');
                const qtyInput = row.querySelector('.variant-qty');
                const priceInput = row.querySelector('.variant-price');

                const data = {
                    unit_type: nameInput.value,
                    unit_qty: qtyInput.value,
                    price: priceInput.value,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                };
                
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                // Make sure this route exists in web.php
                fetch(`/variants/${id}/update-single`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    body: JSON.stringify(data)
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                         showToast('Variant saved successfully');
                         row.classList.add('bg-emerald-50');
                         setTimeout(() => row.classList.remove('bg-emerald-50'), 1500);
                    } else showToast('Save failed', 'error');
                })
                .catch(err => {
                    console.error(err);
                    showToast('Connection error', 'error');
                })
                .finally(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
            }
        });

        // Initialize calculations on load
        runCalculations();
    });
</script>

<style>
    .animate-fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection