@extends('layouts.app')

@section('title', 'Purchase History')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Purchase History</h1>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('purchase-histories.index') }}" class="mb-4">
            <div class="flex items-center space-x-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by store or product" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Search</button>
            </div>
        </form>

        <!-- Purchase History Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Store</th>
                        <th class="px-4 py-2 text-left">Product</th>
                        <th class="px-4 py-2 text-left">User</th>
                        <th class="px-4 py-2 text-left">Quantity</th>
                        <th class="px-4 py-2 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseHistories as $history)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $history->store->name }}</td>
                            <td class="px-4 py-2">{{ $history->product->name }}</td>
                            <td class="px-4 py-2">{{ $history->user->name }}</td>
                            <td class="px-4 py-2">{{ $history->quantity }}</td>
                            <td class="px-4 py-2">{{ $history->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center">No purchase history found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $purchaseHistories->links() }}
        </div>
    </div>
@endsection
