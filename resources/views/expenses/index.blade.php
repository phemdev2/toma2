@extends('layouts.app')
@section('content')
<h1>Expenses</h1>

<form method="GET" action="{{ route('expenses.index') }}">
    <div>
        <label>Store:</label>
        <select name="store_id">
            <option value="">All</option>
            @foreach ($stores as $s)
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
    <button type="submit">Filter</button>
</form>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Store</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($expenses as $exp)
        <tr>
            <td>{{ $exp->date }}</td>
            <td>{{ $exp->store->name }}</td>
            <td>{{ $exp->description }}</td>
            <td>{{ number_format($exp->amount, 2) }}</td>
            <td>
                <a href="{{ route('expenses.edit', $exp) }}">Edit</a>
                <form method="POST" action="{{ route('expenses.destroy', $exp) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('Delete?')" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $expenses->withQueryString()->links() }}

@endsection
