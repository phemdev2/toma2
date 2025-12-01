@extends('layouts.app')
@section('content')
<h1>Edit Expense</h1>

<form method="POST" action="{{ route('expenses.update', $expense) }}">
    @csrf
    @method('PUT')

    <div>
        <label>Date:</label>
        <input type="date" name="date" value="{{ $expense->date }}" required>
    </div>
    <div>
        <label>Description:</label>
        <input type="text" name="description" value="{{ $expense->description }}" required>
    </div>
    <div>
        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" value="{{ $expense->amount }}" required>
    </div>

    <button type="submit">Update</button>
</form>
@endsection
