@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <div class="container">
        <h1 class="mb-4">Transactions</h1>

        <!-- Filter and View Options -->
        <div class="mb-4">
            <form action="{{ route('transactions.index') }}" method="GET" class="mb-3">
                <div class="form-row align-items-center">
                    <div class="col-auto">
                        <input type="text" name="month" value="{{ request('month') }}" class="form-control mb-2" placeholder="YYYY-MM">
                    </div>
                    <div class="col-auto">
                        <select name="filter" class="form-control mb-2">
                            <option value="daily" {{ request('filter') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="monthly" {{ request('filter') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary mb-2">Apply Filter</button>
                    </div>
                </div>
            </form>

            <div class="mb-4">
                <a href="{{ route('transactions.index', ['filter' => 'daily']) }}" class="btn btn-secondary {{ request('filter') === 'daily' ? 'active' : '' }}">Daily View</a>
                <a href="{{ route('transactions.index', ['filter' => 'monthly']) }}" class="btn btn-secondary {{ request('filter') === 'monthly' ? 'active' : '' }}">Monthly View</a>
            </div>
        </div>

        <!-- Balances Display with Cards -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Cash Balance</div>
                    <div class="card-body">
                        <h5 class="card-title">${{ number_format($cashBalance, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">POS Balance</div>
                    <div class="card-body">
                        <h5 class="card-title">${{ number_format($posBalance, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Total Balance</div>
                    <div class="card-body">
                        <h5 class="card-title">${{ number_format($totalBalance, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Today's Total</div>
                    <div class="card-body">
                        <h5 class="card-title">${{ number_format($todaysTotal, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>{{ ucfirst($transaction->type) }}</td>
                            <td>${{ number_format($transaction->total, 2) }}</td>
                            <td>
                                <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-info btn-sm">View</a>
                                <!-- Add delete button -->
                                <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
