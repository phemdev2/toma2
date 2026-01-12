@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Financial Report</h1>
            <p class="text-slate-500 text-sm">Transaction summaries for {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
        </div>
        
        <div class="flex items-center gap-2 no-print">
            <button onclick="window.print()" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="{{ route('dashboard') }}" class="bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg shadow-purple-200 hover:bg-purple-700 transition-all flex items-center gap-2">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-8 no-print">
        <form method="GET" action="{{ route('user.totals') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 ml-1">Reporting Date</label>
                <div class="relative">
                    <i class="fas fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input 
                        type="date" 
                        name="date" 
                        id="date"
                        value="{{ $date }}" 
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-transparent focus:bg-white focus:ring-2 focus:ring-purple-500 rounded-xl text-sm font-medium transition-all" 
                    />
                </div>
            </div>
            <button type="submit" class="bg-slate-800 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-900 transition-all">
                Update View
            </button>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Total Revenue</span>
            <h3 class="text-2xl font-black text-slate-800">₦{{ number_format($userTotals->sum('totalCash') + $userTotals->sum('totalPOS') + $userTotals->sum('totalBank'), 2) }}</h3>
            <p class="text-xs text-slate-500 mt-1">{{ $userTotals->sum('total_orders') }} orders</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-emerald-500">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Cash</span>
            <h3 class="text-2xl font-black text-emerald-600">₦{{ number_format($userTotals->sum('totalCash'), 2) }}</h3>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-blue-500">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">POS</span>
            <h3 class="text-2xl font-black text-blue-600">₦{{ number_format($userTotals->sum('totalPOS'), 2) }}</h3>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-indigo-500">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Transfer</span>
            <h3 class="text-2xl font-black text-indigo-600">₦{{ number_format($userTotals->sum('totalBank'), 2) }}</h3>
        </div>
    </div>

    {{-- Detailed Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Staff / Store</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Orders</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cash</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">POS</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Bank</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($userTotals as $total)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-700 capitalize">{{ $total->user->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">{{ $total->store->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-bold">{{ $total->total_orders }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-emerald-600">₦{{ number_format($total->totalCash, 2) }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-blue-600">₦{{ number_format($total->totalPOS, 2) }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-indigo-600">₦{{ number_format($total->totalBank, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-black text-slate-800">
                                ₦{{ number_format($total->totalCash + $total->totalPOS + $total->totalBank, 2) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-900 text-white font-bold">
                        <td class="px-6 py-4">GRAND TOTALS</td>
                        <td class="px-6 py-4 text-center">{{ $userTotals->sum('total_orders') }}</td>
                        <td class="px-6 py-4">₦{{ number_format($userTotals->sum('totalCash'), 2) }}</td>
                        <td class="px-6 py-4">₦{{ number_format($userTotals->sum('totalPOS'), 2) }}</td>
                        <td class="px-6 py-4">₦{{ number_format($userTotals->sum('totalBank'), 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            ₦{{ number_format($userTotals->sum('totalCash') + $userTotals->sum('totalPOS') + $userTotals->sum('totalBank'), 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, nav, footer { display: none !important; }
        .bg-white { border: none !important; }
        .bg-slate-900 { background-color: #000 !important; color: #fff !important; }
    }
</style>
@endsection