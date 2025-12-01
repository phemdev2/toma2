@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Create New Transaction</h2>
    <form action="{{ route('transactions.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" class="form-control" required>
                <option value="cash">Cash</option>
                <option value="pos">POS</option>
            </select>
        </div>
        <div class="form-group">
            <label for="total">Total</label>
            <input type="text" id="total" name="total" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Transaction</button>
    </form>
</div>
@endsection
