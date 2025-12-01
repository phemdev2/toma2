@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 h-full">

    {{-- Flash Message --}}
    @if(session('message'))
        <div class="mb-4 text-sm text-green-700 bg-green-100 border border-green-300 rounded p-3">
            {{ session('message') }}
        </div>
    @endif

    <h1 class="text-lg font-semibold text-purple-500 mb-6">User Totals for {{ $date }}</h1>

    {{-- Date Filter Form --}}
    <form method="GET" action="{{ route('user.totals') }}" class="mb-6">
        <div class="flex items-center">
            <label for="date" class="mr-2 text-lg">Select Date:</label>
            <input 
                type="date" 
                name="date" 
                id="date"
                value="{{ $date }}" 
                aria-label="Select Date"
                aria-describedby="date-help"
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
        <small id="date-help" class="text-sm text-gray-500">Pick a date to filter transaction data.</small>
    </form>

    {{-- Store Totals Cards --}}
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

    {{-- User Totals Table --}}
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-semibold text-purple-500 mb-4">Totals per User and Store</h2>

        @if($userTotals->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User Name</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Store Name</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Orders</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cash Total</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">POS Total</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bank Total</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Amount</th>
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
                        <td class="px-4 py-2 text-sm text-gray-700">
                            &#8358;{{ number_format($total->totalCash + $total->totalPOS + $total->totalBank, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Grand Totals Row --}}
                <tfoot>
                    <tr class="bg-gray-100 font-semibold text-purple-700">
                        <td colspan="2" class="px-4 py-2 text-right">Grand Total:</td>
                        <td class="px-4 py-2">{{ $userTotals->sum('total_orders') }}</td>
                        <td class="px-4 py-2">&#8358;{{ number_format($userTotals->sum('totalCash'), 2) }}</td>
                        <td class="px-4 py-2">&#8358;{{ number_format($userTotals->sum('totalPOS'), 2) }}</td>
                        <td class="px-4 py-2">&#8358;{{ number_format($userTotals->sum('totalBank'), 2) }}</td>
                        <td class="px-4 py-2">
                            &#8358;{{ number_format($userTotals->sum('totalCash') + $userTotals->sum('totalPOS') + $userTotals->sum('totalBank'), 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
            <p class="text-gray-600">No transactions available for this date.</p>
        @endif
    </div>
</div>
@endsection
