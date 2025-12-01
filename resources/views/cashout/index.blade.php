@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-8 text-center">Today's Cash Out Transactions</h2>

    @if (session('success'))
        <div class="bg-green-200 border border-green-500 text-green-700 px-4 py-3 rounded relative mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
            <strong class="font-bold">Whoops!</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Cash Out Form -->
    <div class="bg-white shadow-lg rounded-lg p-6 mt-6">
        <h3 class="text-xl font-semibold mb-4">Cash Out</h3>
        <form action="{{ route('cashout.store') }}" method="POST" id="cashout-form">
            @csrf
            
            <!-- Hidden input for store_id -->
            <input type="hidden" name="store_id" value="{{ auth()->user()->store_id ?? '' }}">

            <div class="mb-6">
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount to Withdraw</label>
                <input type="number" name="amount" id="amount" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring focus:ring-blue-300"
                       placeholder="Enter amount" min="1" max="{{ auth()->user()->getAvailableCash() }}" />
            </div>

            <div class="mb-6">
                <label for="charges" class="block text-sm font-medium text-gray-700 mb-2">Charges</label>
                <input type="number" name="charges" id="charges"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring focus:ring-blue-300"
                       placeholder="Enter charges" min="0" />
            </div>

            <button type="submit" class="w-full py-3 px-4 border border-transparent rounded-md shadow-md text-white bg-blue-600 hover:bg-blue-700">
                Cash Out
            </button>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="overflow-hidden border-b border-gray-200 sm:rounded-lg mt-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Charges</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->store->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">&#8358;{{ number_format($transaction->amount, 2) }}</td>
                        <!-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">&#8358;{{ number_format($transaction->charges, 2) }}</td> -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->payment_method }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
