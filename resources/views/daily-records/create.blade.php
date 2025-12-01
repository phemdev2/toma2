<form action="{{ route('daily-records.store') }}" method="POST">
    @csrf

    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
    <input type="hidden" name="store_id" value="{{ $store->id }}">
    <input type="date" name="date" value="{{ date('Y-m-d') }}" required>

    <div>
        <label>Cash Amount:</label>
        <input type="number" step="0.01" name="cash" required>
    </div>

    <div>
        <label>POS Amount:</label>
        <input type="number" step="0.01" name="pos" required>
    </div>

    <div id="expenses">
        <label>Expenses:</label>
        <div class="expense">
            <input type="text" name="expenses[0][description]" placeholder="Description">
            <input type="number" step="0.01" name="expenses[0][amount]" placeholder="Amount">
        </div>
    </div>

    <button type="button" onclick="addExpense()">+ Add Expense</button>

    <button type="submit">Save Daily Record</button>
</form>

<script>
let expenseIndex = 1;
function addExpense() {
    const div = document.createElement('div');
    div.classList.add('expense');
    div.innerHTML = `
        <input type="text" name="expenses[${expenseIndex}][description]" placeholder="Description">
        <input type="number" step="0.01" name="expenses[${expenseIndex}][amount]" placeholder="Amount">
    `;
    document.getElementById('expenses').appendChild(div);
    expenseIndex++;
}
</script>
