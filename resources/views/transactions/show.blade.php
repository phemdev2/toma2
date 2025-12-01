@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Transaction #{{ $transaction->id }}</h2>
    <p><strong>Payment Method:</strong> {{ ucfirst($transaction->payment_method) }}</p>
    <p><strong>Total:</strong> ₦{{ number_format($transaction->total, 2) }}</p>
    <p><strong>Created At:</strong> {{ $transaction->created_at->format('Y-m-d H:i') }}</p>

    <h3>Items</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaction->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>₦{{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₦{{ number_format($item->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No items found for this transaction.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Back to Transactions</a>
</div>
@endsection
