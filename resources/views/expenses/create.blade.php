<form method="POST" action="{{ route('expenses.store') }}">
    @csrf

    <label for="store_id">Store:</label>
    <select name="store_id" required>
        @foreach ($stores as $store)
            <option value="{{ $store->id }}">{{ $store->name }}</option>
        @endforeach
    </select>

    <label>Date:</label>
    <input type="date" name="date" value="{{ date('Y-m-d') }}" required>

    <label>Description:</label>
    <input type="text" name="description" required>

    <label>Amount:</label>
    <input type="number" name="amount" step="0.01" required>

    <button type="submit">Save Expense</button>
</form>
