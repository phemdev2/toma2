@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-purple-600 mb-4">Totals per User and Store for {{ $date }}</h1>

    <form method="GET" action="{{ route('user.totals') }}" class="mb-6">
        <div class="flex items-center">
            <label for="date" class="mr-2 text-lg">Select Date:</label>
            <input 
                type="date" 
                name="date" 
                value="{{ $date }}" 
                aria-label="Select Date" 
                class="border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-purple-500" 
            />
            <button 
                type="submit" 
                class="ml-3 bg-purple-600 text-white rounded-lg px-5 py-2 transition duration-300 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-purple-500" 
                aria-label="Filter"
            >
                Filter
            </button>
        </div>
    </form>

    <!-- Store Totals Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($storeTotals as $storeTotal)
            <div class="bg-purple-100 shadow-lg rounded-lg p-4 transition-transform transform hover:scale-105 flex flex-col">
                <div class="flex items-center mb-2">
                    <i class="fas fa-store text-purple-800 mr-2"></i>
                    <h2 class="text-lg font-semibold text-purple-800">{{ $storeTotal['name'] }}</h2>
                </div>
                <hr class="border-purple-600 mb-2">
                <p class="text-sm text-gray-700">Total Orders: <span class="font-bold">{{ $storeTotal['total_orders'] }}</span></p>
                <p class="text-sm text-gray-700">Total Amount: <span class="font-bold">&#8358;{{ number_format($storeTotal['total_amount'], 2) }}</span></p>
            </div>
        @empty
            <div class="bg-yellow-100 shadow-lg rounded-lg p-4 flex flex-col">
                <p class="text-gray-700">No store data available for this date.</p>
            </div>
        @endforelse
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-purple-500 mb-4">User Totals</h2>
            @if($userTotals->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">User</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Store</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Orders</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Cash</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">POS</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Bank</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($userTotals as $total)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $total->user->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $total->store->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $total->total_orders }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">&#8358;{{ number_format($total->totalCash, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">&#8358;{{ number_format($total->totalPOS, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">&#8358;{{ number_format($total->totalBank, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">&#8358;{{ number_format($total->totalCash + $total->totalPOS + $total->totalBank, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">No transactions available for this date.</p>
            @endif
        </div>
    </div>
</div>
@endsection
