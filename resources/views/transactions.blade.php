@extends('layouts.app')

@section('content')
<div class="container">
    <h1>User Transactions for {{ $date }}</h1>

    <form method="GET" action="{{ route('transactions') }}">
        <div class="form-group">
            <label for="date">Select Date:</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control" />
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <h2>Transactions</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>User</th>
                <th>Store</th>
                <th>Amount</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
            <tr>
                <td>{{ $transaction->user->name }}</td>
                <td>{{ $transaction->store->name }}</td>
                <td>${{ number_format($transaction->amount, 2) }}</td>
                <td>{{ $transaction->order_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div>
        {{ $transactions->links() }}
    </div>

    <h2>Total Amount Per Store</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Store</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($totalPerStore as $storeTotal)
            <tr>
                <td>{{ $storeTotal->store->name }}</td>
                <td>${{ number_format($storeTotal->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection