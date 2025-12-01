@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Transaction #{{ $transaction->id }}</h2>
    <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" class="form-control" required>
                <option value="cash" {{ $transaction->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="pos" {{ $transaction->payment_method == 'pos' ? 'selected' : '' }}>POS</option>
            </select>
        </div>
        <div class="form-group">
            <label for="total">Total</label>
            <input type="text" id="total" name="total" class="form-control" value="{{ number_format($transaction->total, 2) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Transaction</button>
    </form>
</div>
@endsection
