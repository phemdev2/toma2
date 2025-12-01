@extends('layouts.app')

@section('title', 'Product List')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 max-w-7xl">
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 sm:p-4 mb-4 sm:mb-6 rounded animate-fade-in text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 sm:p-4 mb-4 sm:mb-6 rounded animate-fade-in text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white bg-opacity-90 hidden items-center justify-center z-50">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-gray-900"></div>
            <p class="mt-4 text-gray-600">Loading products...</p>
        </div>
    </div>

    <!-- Header Section -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-light text-gray-900 mb-1">Products</h1>
                <p class="text-xs sm:text-sm text-gray-500">Manage your product inventory</p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-xl sm:text-2xl font-light text-gray-900">{{ $productsWithVariants->total() }}</p>
                <p class="text-xs sm:text-sm text-gray-500">Total Products</p>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <form method="GET" action="{{ route('products.index') }}" class="mb-4 sm:mb-6" id="searchForm">
            <div class="flex gap-2 sm:gap-3 flex-wrap">
                <div class="flex-1 min-w-[200px] relative group">
                    <input type="text" 
                           name="search" 
                           id="searchBar" 
                           placeholder="Search products..." 
                           class="w-full border-b-2 border-gray-200 focus:border-gray-900 outline-none px-2 py-2 sm:py-3 text-sm sm:text-base transition-all duration-300 group-hover:border-gray-400"
                           value="{{ request('search') }}">
                    <span class="absolute right-2 top-2 sm:top-3 text-gray-400 transition-opacity duration-300 opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                </div>
                <button type="submit" 
                        class="bg-gray-900 text-white px-4 sm:px-6 py-2 sm:py-3 text-sm sm:text-base hover:bg-gray-800 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('products.index') }}" 
                       class="border border-gray-300 text-gray-700 hover:border-gray-900 px-4 sm:px-6 py-2 sm:py-3 text-sm sm:text-base transition-all duration-300 hover:shadow-md">
                        Clear
                    </a>
                @endif
            </div>
        </form>
        
        <!-- Action Buttons - Responsive Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap gap-2 sm:gap-3">
            <a href="{{ route('products.create') }}" 
               class="inline-flex items-center justify-center gap-2 border-2 border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white px-4 sm:px-5 py-2 text-sm sm:text-base transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Product
            </a>
            <a href="{{ route('products.download.csv') }}" 
               class="inline-flex items-center justify-center gap-2 border border-gray-300 text-gray-700 hover:border-gray-900 hover:bg-gray-50 px-4 sm:px-5 py-2 text-sm sm:text-base transition-all duration-300 hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="hidden sm:inline">Export</span> CSV
            </a>
            <a href="{{ route('products.download.pdf') }}" 
               class="inline-flex items-center justify-center gap-2 border border-gray-300 text-gray-700 hover:border-gray-900 hover:bg-gray-50 px-4 sm:px-5 py-2 text-sm sm:text-base transition-all duration-300 hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span class="hidden sm:inline">Export</span> PDF
            </a>
        </div>
    </div>

    @if($productsWithVariants->isEmpty())
        <div class="text-center py-12 sm:py-16 border-2 border-dashed border-gray-200 rounded-lg">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-400 text-base sm:text-lg">No products available.</p>
            <a href="{{ route('products.create') }}" class="inline-block mt-4 text-sm sm:text-base text-gray-600 hover:text-gray-900 underline">
                Add your first product
            </a>
        </div>
    @else
        <!-- Desktop Table View (Hidden on Mobile) -->
        <div class="hidden lg:block bg-white border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="overflow-x-auto">
                <table id="productTable" class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productBody" class="divide-y divide-gray-100">
                        @foreach($productsWithVariants as $product)
                            <tr class="hover:bg-gray-50 transition-all duration-200 hover:shadow-sm group">
                                <td class="py-3 px-4 text-sm text-gray-900 group-hover:text-black transition-colors duration-200">
                                    <span class="font-medium">{{ $product->name }}</span>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600 font-mono group-hover:text-gray-800 transition-colors duration-200">
                                    {{ $product->barcode }}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900">
                                    <span class="group-hover:font-medium transition-all duration-200">&#8358;{{ number_format($product->cost, 2) }}</span>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900">
                                    <span class="group-hover:font-medium transition-all duration-200">&#8358;{{ number_format($product->sale, 2) }}</span>
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    @php
                                        $profit = $product->sale - $product->cost;
                                        $profitPercent = $product->cost > 0 ? (($profit / $product->cost) * 100) : 0;
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium 
                                        {{ $profit > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}
                                        group-hover:shadow-sm transition-all duration-200">
                                        {{ $profit > 0 ? '+' : '' }}&#8358;{{ number_format($profit, 2) }}
                                        <span class="text-[10px]">({{ number_format($profitPercent, 1) }}%)</span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    @if($product->expiry_date)
                                        @php
                                            $expiryDate = \Carbon\Carbon::parse($product->expiry_date);
                                            $daysUntilExpiry = now()->diffInDays($expiryDate, false);
                                        @endphp
                                        <span class="inline-flex items-center gap-1 text-xs
                                            {{ $daysUntilExpiry < 0 ? 'text-red-600' : ($daysUntilExpiry < 30 ? 'text-orange-600' : 'text-gray-600') }}
                                            group-hover:font-medium transition-all duration-200">
                                            {{ $expiryDate->format('d/m/Y') }}
                                            @if($daysUntilExpiry < 0)
                                                <span class="text-[10px] bg-red-100 text-red-700 px-1 rounded">Expired</span>
                                            @elseif($daysUntilExpiry < 30)
                                                <span class="text-[10px] bg-orange-100 text-orange-700 px-1 rounded">{{ $daysUntilExpiry }}d</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2 opacity-70 group-hover:opacity-100 transition-opacity duration-200">
                                        @can('update-product')
                                            <a href="{{ route('products.show', $product->id) }}" 
                                               class="text-gray-600 hover:text-blue-600 text-sm underline transition-colors duration-200 hover:no-underline" 
                                               title="View Product Details">
                                                View
                                            </a>
                                        @endcan
                                        @can('update-product')
                                            <a href="{{ route('products.edit', $product->id) }}" 
                                               class="text-gray-600 hover:text-green-600 text-sm underline transition-colors duration-200 hover:no-underline" 
                                               title="Edit Product">
                                                Edit
                                            </a>
                                        @endcan
                                        @can('delete-product')
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-gray-600 hover:text-red-600 text-sm underline transition-colors duration-200 hover:no-underline" 
                                                        onclick="return confirm('Are you sure you want to delete this product?');" 
                                                        title="Delete Product">
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View (Visible on Small Screens) -->
        <div class="lg:hidden space-y-3">
            @foreach($productsWithVariants as $product)
                @php
                    $profit = $product->sale - $product->cost;
                    $profitPercent = $product->cost > 0 ? (($profit / $product->cost) * 100) : 0;
                    $expiryDate = $product->expiry_date ? \Carbon\Carbon::parse($product->expiry_date) : null;
                    $daysUntilExpiry = $expiryDate ? now()->diffInDays($expiryDate, false) : null;
                @endphp
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900 text-base mb-1">{{ $product->name }}</h3>
                            <p class="text-xs text-gray-500 font-mono">{{ $product->barcode }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium ml-2
                            {{ $profit > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $profit > 0 ? '+' : '' }}{{ number_format($profitPercent, 1) }}%
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Cost</p>
                            <p class="text-gray-900 font-medium">&#8358;{{ number_format($product->cost, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Sale</p>
                            <p class="text-gray-900 font-medium">&#8358;{{ number_format($product->sale, 2) }}</p>
                        </div>
                    </div>

                    @if($expiryDate)
                        <div class="mb-3 text-xs">
                            <span class="inline-flex items-center gap-1
                                {{ $daysUntilExpiry < 0 ? 'text-red-600' : ($daysUntilExpiry < 30 ? 'text-orange-600' : 'text-gray-600') }}">
                                Expires: {{ $expiryDate->format('d/m/Y') }}
                                @if($daysUntilExpiry < 0)
                                    <span class="bg-red-100 text-red-700 px-1 rounded">Expired</span>
                                @elseif($daysUntilExpiry < 30)
                                    <span class="bg-orange-100 text-orange-700 px-1 rounded">{{ $daysUntilExpiry }} days</span>
                                @endif
                            </span>
                        </div>
                    @endif
                    
                    <div class="flex gap-2 pt-3 border-t border-gray-100">
                        @can('update-product')
                            <a href="{{ route('products.show', $product->id) }}" 
                               class="flex-1 text-center text-gray-600 hover:text-blue-600 text-sm py-2 border border-gray-300 hover:border-blue-600 rounded transition-colors duration-200">
                                View
                            </a>
                        @endcan
                        @can('update-product')
                            <a href="{{ route('products.edit', $product->id) }}" 
                               class="flex-1 text-center text-gray-600 hover:text-green-600 text-sm py-2 border border-gray-300 hover:border-green-600 rounded transition-colors duration-200">
                                Edit
                            </a>
                        @endcan
                        @can('delete-product')
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full text-gray-600 hover:text-red-600 text-sm py-2 border border-gray-300 hover:border-red-600 rounded transition-colors duration-200" 
                                        onclick="return confirm('Are you sure you want to delete this product?');">
                                    Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6 sm:mt-8">
            {{ $productsWithVariants->links('pagination::tailwind') }}
        </div>
    @endif
</div>

<!-- Enhanced Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50 px-4 transition-opacity duration-300">
    <div class="bg-white max-w-lg w-full shadow-2xl transform transition-all duration-300 scale-95 modal-content max-h-[90vh] overflow-y-auto">
        <div class="p-6 sm:p-8">
            <div class="flex items-center justify-between mb-4 sm:mb-6 pb-4 border-b border-gray-200">
                <h2 class="text-xl sm:text-2xl font-light text-gray-900">Product Details</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="detailsContent" class="space-y-3"></div>
            <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-200">
                <button onclick="closeModal()" 
                        class="w-full border border-gray-300 text-gray-700 hover:border-gray-900 hover:bg-gray-50 px-6 py-2 sm:py-3 text-sm sm:text-base transition-all duration-300 hover:shadow-md">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

#detailsModal.show .modal-content {
    transform: scale(1);
}

#detailsModal:not(.show) .modal-content {
    transform: scale(0.95);
}

/* Loading spinner animation */
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>

<script>
// Show loading overlay on page navigation
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    // Show loading on form submit
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            loadingOverlay.classList.remove('hidden');
            loadingOverlay.classList.add('flex');
        });
    }
    
    // Show loading on pagination clicks
    document.querySelectorAll('a[href*="page="]').forEach(link => {
        link.addEventListener('click', function() {
            loadingOverlay.classList.remove('hidden');
            loadingOverlay.classList.add('flex');
        });
    });
});

function openModal(productId) {
    const modal = document.getElementById('detailsModal');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    loadingOverlay.classList.remove('hidden');
    loadingOverlay.classList.add('flex');
    
    fetch(`/products/${productId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const profit = data.sale - data.cost;
            const profitPercent = data.cost > 0 ? ((profit / data.cost) * 100) : 0;
            const profitColor = profit > 0 ? 'text-green-600' : 'text-red-600';
            
            let content = `
                <div class="space-y-2 sm:space-y-3">
                    <div class="flex justify-between py-2 sm:py-3 border-b border-gray-100 hover:bg-gray-50 px-2 -mx-2 transition-colors duration-200">
                        <span class="text-gray-500 text-xs sm:text-sm">Name</span>
                        <span class="text-gray-900 font-medium text-sm sm:text-base">${data.name}</span>
                    </div>
                    <div class="flex justify-between py-2 sm:py-3 border-b border-gray-100 hover:bg-gray-50 px-2 -mx-2 transition-colors duration-200">
                        <span class="text-gray-500 text-xs sm:text-sm">Barcode</span>
                        <span class="text-gray-900 font-mono text-xs sm:text-sm">${data.barcode}</span>
                    </div>
                    <div class="flex justify-between py-2 sm:py-3 border-b border-gray-100 hover:bg-gray-50 px-2 -mx-2 transition-colors duration-200">
                        <span class="text-gray-500 text-xs sm:text-sm">Cost Price</span>
                        <span class="text-gray-900 text-sm sm:text-base">&#8358;${data.cost.toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between py-2 sm:py-3 border-b border-gray-100 hover:bg-gray-50 px-2 -mx-2 transition-colors duration-200">
                        <span class="text-gray-500 text-xs sm:text-sm">Sale Price</span>
                        <span class="text-gray-900 text-sm sm:text-base">&#8358;${data.sale.toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between py-2 sm:py-3 border-b border-gray-100 hover:bg-gray-50 px-2 -mx-2 transition-colors duration-200">
                        <span class="text-gray-500 text-xs sm:text-sm">Profit Margin</span>
                        <span class="${profitColor} font-medium text-sm sm:text-base">
                            ${profit > 0 ? '+' : ''}&#8358;${profit.toFixed(2)} 
                            <span class="text-xs">(${profitPercent.toFixed(1)}%)</span>
                        </span>
                    </div>
                </div>
                <div class="mt-4 sm:mt-6">
                    <h3 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Variants
                    </h3>
                    <div class="space-y-2">`;
            
            if (data.variants && data.variants.length > 0) {
                data.variants.forEach(variant => {
                    content += `
                        <div class="flex justify-between py-2 px-3 bg-gray-50 rounded hover:bg-gray-100 transition-colors duration-200 text-xs sm:text-sm">
                            <span class="text-gray-700">${variant.unit_type} <span class="text-gray-500 text-xs">(${variant.unit_qty} units)</span></span>
                            <span class="text-gray-900 font-medium">&#8358;${variant.price.toFixed(2)}</span>
                        </div>`;
                });
            } else {
                content += '<p class="text-gray-400 text-xs sm:text-sm">No variants available</p>';
            }
            
            content += '</div></div>';
            document.getElementById('detailsContent').innerHTML = content;
            
            loadingOverlay.classList.add('hidden');
            loadingOverlay.classList.remove('flex');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => modal.classList.add('show'), 10);
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
            document.getElementById('detailsContent').innerHTML = '<p class="text-red-500 text-sm">Error loading product details</p>';
            
            loadingOverlay.classList.add('hidden');
            loadingOverlay.classList.remove('flex');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
}

function closeModal() {
    const modal = document.getElementById('detailsModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

// Close modal on outside click
document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection