@extends('layouts.app')
@section('content')
<h1>Expense Report</h1>

<form method="GET" action="{{ route('expenses.report') }}">
    <div>
        <label>Store:</label>
        <select name="store_id">
            <option value="">All</option>
            @foreach (auth()->user()->stores as $s)
                <option value="{{ $s->id }}" {{ request('store_id') == $s->id ? 'selected' : '' }}>
                    {{ $s->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>From:</label>
        <input type="date" name="from_date" value="{{ request('from_date') }}">
    </div>
    <div>
        <label>To:</label>
        <input type="date" name="to_date" value="{{ request('to_date') }}">
    </div>
    <button type="submit">Generate</button>
</form>

@if ($expenses->count())
    <table>
        <thead>
            <tr>
                <th>Date</th><th>Store</th><th>Description</th><th>Amount</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($expenses as $exp)
            <tr>
                <td>{{ $exp->date }}</td>
                <td>{{ $exp->store->name }}</td>
                <td>{{ $exp->description }}</td>
                <td>{{ number_format($exp->amount, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th>{{ number_format($total, 2) }}</th>
            </tr>
        </tfoot>
    </table>
@else
    <p>No expenses for the given filters.</p>
@endif
@endsection
