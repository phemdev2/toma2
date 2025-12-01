<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 max-w-7xl">

    {{-- Search & Sort --}}
    <div class="mb-4 flex flex-wrap gap-3 items-center">
        <div class="flex-1 min-w-[200px]">
            <input type="text"
                   wire:model.debounce.300ms="search"
                   placeholder="Search products..."
                   class="w-full border-b-2 border-gray-200 focus:border-gray-900 outline-none px-2 py-2 sm:py-3 text-sm sm:text-base transition-all duration-300">
        </div>
        <div class="flex gap-2">
            <select wire:model="sortField" class="border px-2 py-1">
                <option value="name">Name</option>
                <option value="cost">Cost</option>
                <option value="sale">Sale</option>
                <option value="expiry_date">Expiry</option>
            </select>
            <select wire:model="sortDirection" class="border px-2 py-1">
                <option value="asc">Asc</option>
                <option value="desc">Desc</option>
            </select>
        </div>
    </div>

    {{-- If no products at all --}}
    @if (empty($this->products))
        <div class="text-center py-12 sm:py-16 border-2 border-dashed border-gray-200 rounded-lg">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00‑2 2v7m16 0v5a2 2 0 01‑2 2H6a2 2 0 01‑2‑2v‑5m16 0h‑2.586a1 1 0 00‑.707.293l‑2.414 2.414a1 1 0 01‑.707.293h‑3.172a1 1 0 01‑.707‑.293l‑2.414‑2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-400 text-base sm:text-lg">No products found.</p>
        </div>
    @else
        {{-- Desktop Table View --}}
        <div class="hidden lg:block bg-white border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="overflow-x-auto">
                <table class="w-full">
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
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($this->products as $prod)
                            <tr class="hover:bg-gray-50 transition-all duration-200 group">
                                <td class="py-3 px-4 text-sm text-gray-900 group-hover:text-black font-medium">{{ $prod['name'] }}</td>
                                <td class="py-3 px-4 text-sm text-gray-600 font-mono">{{ $prod['barcode'] }}</td>
                                <td class="py-3 px-4 text-sm text-gray-900">₦{{ number_format($prod['cost'], 2) }}</td>
                                <td class="py-3 px-4 text-sm text-gray-900">₦{{ number_format($prod['sale'], 2) }}</td>
                                <td class="py-3 px-4 text-sm">
                                    @php
                                        $pf = $prod['profit'];
                                        $pp = $prod['profit_percent'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium
                                        {{ $pf > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}
                                        group-hover:shadow-sm transition-all duration-200">
                                        {{ $pf > 0 ? '+' : '' }}₦{{ number_format($pf, 2) }}
                                        <span class="text-[10px]">({{ number_format($pp, 1) }}%)</span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    @if ($prod['expiry_date'])
                                        @php
                                            $days = $prod['days_until_expiry'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 text-xs
                                            {{ $days < 0 ? 'text-red-600' : ($days < 30 ? 'text-orange-600' : 'text-gray-600') }}
                                            group-hover:font-medium transition-all duration-200">
                                            {{ \Carbon\Carbon::parse($prod['expiry_date'])->format('d/m/Y') }}
                                            @if ($days < 0)
                                                <span class="text-[10px] bg-red-100 text-red-700 px-1 rounded">Expired</span>
                                            @elseif ($days < 30)
                                                <span class="text-[10px] bg-orange-100 text-orange-700 px-1 rounded">{{ $days }}d</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <div class="flex items-center gap-2 opacity-70 group-hover:opacity-100 transition-opacity duration-200">
                                        <a href="{{ route('products.show', $prod['id']) }}" class="text-gray-600 hover:text-blue-600 text-sm underline">View</a>
                                        <a href="{{ route('products.edit', $prod['id']) }}" class="text-gray-600 hover:text-green-600 text-sm underline">Edit</a>
                                        <form action="{{ route('products.destroy', $prod['id']) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="text-gray-600 hover:text-red-600 text-sm underline"
                                                    onclick="return confirm('Are you sure?');">
                                                Delete
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

        {{-- Mobile / Card View --}}
        <div class="lg:hidden space-y-3">
            @foreach ($this->products as $prod)
                @php
                    $pf = $prod['profit'];
                    $pp = $prod['profit_percent'];
                    $days = $prod['days_until_expiry'];
                @endphp
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900 text-base mb-1">{{ $prod['name'] }}</h3>
                            <p class="text-xs text-gray-500 font-mono">{{ $prod['barcode'] }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium
                            {{ $pf > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ number_format($pp, 1) }}%
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Cost</p>
                            <p class="text-gray-900 font-medium">₦{{ number_format($prod['cost'], 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Sale</p>
                            <p class="text-gray-900 font-medium">₦{{ number_format($prod['sale'], 2) }}</p>
                        </div>
                    </div>

                    @if ($prod['expiry_date'])
                        <div class="mb-3 text-xs">
                            <span class="inline-flex items-center gap-1
                                {{ $days < 0 ? 'text-red-600' : ($days < 30 ? 'text-orange-600' : 'text-gray-600') }}">
                                Expires: {{ \Carbon\Carbon::parse($prod['expiry_date'])->format('d/m/Y') }}
                                @if ($days < 0)
                                    <span class="bg-red-100 text-red-700 px-1 rounded">Expired</span>
                                @elseif ($days < 30)
                                    <span class="bg-orange-100 text-orange-700 px-1 rounded">{{ $days }} days</span>
                                @endif
                            </span>
                        </div>
                    @endif

                    <div class="flex gap-2 pt-3 border-t border-gray-100">
                        <a href="{{ route('products.show', $prod['id']) }}" class="flex-1 text-center text-gray-600 hover:text-blue-600 text-sm py-2 border border-gray-300 hover:border-blue-600 rounded">View</a>
                        <a href="{{ route('products.edit', $prod['id']) }}" class="flex-1 text-center text-gray-600 hover:text-green-600 text-sm py-2 border border-gray-300 hover:border-green-600 rounded">Edit</a>
                        <form action="{{ route('products.destroy', $prod['id']) }}" method="POST" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-full text-gray-600 hover:text-red-600 text-sm py-2 border border-gray-300 hover:border-red-600 rounded"
                                    onclick="return confirm('Are you sure?');">
                                Delete
                            </button>
                        </form>
                    </div>

                    {{-- Variants section (optional in card) --}}
                    @if (!empty($prod['variants']))
                        <div class="mt-4 text-xs">
                            <h4 class="font-medium text-gray-500 uppercase tracking-wide mb-1">Variants</h4>
                            <div class="space-y-1">
                                @foreach ($prod['variants'] as $v)
                                    <div class="flex justify-between bg-gray-50 px-3 py-1 rounded">
                                        <span>{{ $v['unit_type'] }} ({{ $v['unit_qty'] }} units)</span>
                                        <span>₦{{ number_format($v['price'], 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Infinite scroll trigger --}}
        @if ($hasMore)
            <div
                x-data
                x-intersect="$wire.loadMore()"
                class="py-4 text-center text-gray-500"
            >
                <div wire:loading>
                    Loading more products...
                </div>
            </div>
        @endif

    @endif

</div>
