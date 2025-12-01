@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white shadow-lg rounded-lg">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Daily Record</h1>

    {{-- Update form --}}
    <form action="{{ route('daily.update', $record->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Date --}}
        <div class="mb-4">
            <label for="date" class="block text-gray-700 font-semibold mb-2">Date</label>
            <input type="date" name="date" id="date" value="{{ $record->date }}"
                   class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-indigo-200">
        </div>

        {{-- Cash --}}
        <div class="mb-4">
            <label for="cash" class="block text-gray-700 font-semibold mb-2">Cash</label>
            <input type="number" step="0.01" name="cash" id="cash" value="{{ $record->cash }}"
                   class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-indigo-200">
        </div>

        {{-- POS --}}
        <div class="mb-4">
            <label for="pos" class="block text-gray-700 font-semibold mb-2">POS</label>
            <input type="number" step="0.01" name="pos" id="pos" value="{{ $record->pos }}"
                   class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-indigo-200">
        </div>

        {{-- Expenses --}}
        <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">Expenses</label>
            <div id="expenses-container">
                @foreach($record->expenses as $i => $expense)
                    <div class="flex gap-2 mb-2">
                        <input type="hidden" name="expenses[{{ $i }}][id]" value="{{ $expense->id }}">
                        <input type="text" name="expenses[{{ $i }}][item]" value="{{ $expense->item }}"
                               placeholder="Item"
                               class="flex-1 border border-gray-300 rounded-lg p-2 focus:ring focus:ring-indigo-200">
                        <input type="number" step="0.01" name="expenses[{{ $i }}][amount]" value="{{ $expense->amount }}"
                               placeholder="Amount"
                               class="w-32 border border-gray-300 rounded-lg p-2 focus:ring focus:ring-indigo-200">
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-expense"
                    class="mt-2 px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">
                + Add Expense
            </button>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                Update Record
            </button>
        </div>
    </form>
</div>

{{-- JS to dynamically add expense rows --}}
<script>
document.getElementById('add-expense').addEventListener('click', function () {
    const container = document.getElementById('expenses-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.classList.add('flex', 'gap-2', 'mb-2');

    div.innerHTML = `
        <input type="text" name="expenses[${index}][item]" placeholder="Item"
               class="flex-1 border border-gray-300 rounded-lg p-2 focus:ring focus:ring-indigo-200">
        <input type="number" step="0.01" name="expenses[${index}][amount]" placeholder="Amount"
               class="w-32 border border-gray-300 rounded-lg p-2 focus:ring focus:ring-indigo-200">
    `;

    container.appendChild(div);
});
</script>
@endsection
