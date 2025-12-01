@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    {{-- Success & Error --}}
    <div id="success-message" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6"></div>
    <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"></div>

    {{-- Create Form --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i class="fas fa-plus-circle text-green-600"></i> Add Daily Record
        </h2>
        <form id="daily-form" action="{{ route('daily.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600">User</label>
                    <input type="text" value="{{ auth()->user()->name }}" readonly
                           class="w-full border bg-gray-100 rounded-lg p-2 text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Store</label>
                    <input type="text" value="{{ auth()->user()->store->name ?? 'N/A' }}" readonly
                           class="w-full border bg-gray-100 rounded-lg p-2 text-gray-700">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-600">Date</label>
                    <input type="date" name="date" id="date" class="w-full border rounded-lg p-2" required value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label for="cash" class="block text-sm font-medium text-gray-600">Cash</label>
                    <input type="number" name="cash" id="cash" min="0" value="0" class="w-full border rounded-lg p-2">
                </div>
                <div>
                    <label for="pos" class="block text-sm font-medium text-gray-600">POS</label>
                    <input type="number" name="pos" id="pos" min="0" value="0" class="w-full border rounded-lg p-2">
                </div>
            </div>

            <h3 class="text-md font-semibold text-gray-700 mt-4">Purchases / Restock</h3>
            <div id="expense-container" class="space-y-2">
                <div class="flex gap-2">
                    <input type="text" name="expenses[0][item]" placeholder="Item" class="flex-1 border rounded-lg p-2">
                    <input type="number" name="expenses[0][amount]" placeholder="Amount" min="0" class="w-40 border rounded-lg p-2">
                </div>
            </div>
            <button type="button" onclick="addExpenseRow()" class="mt-2 px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                + Add Purchase
            </button>

            <div class="pt-4">
                <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow">
                    Save Record
                </button>
            </div>
        </form>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200 mb-6">
        <form method="GET" action="{{ route('daily.index') }}" class="grid md:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-600">From</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">To</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Store</label>
                <select name="store_id" class="w-full border rounded-lg p-2">
                    <option value="">All</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" @selected($storeId == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">User</label>
                <select name="user_id" class="w-full border rounded-lg p-2">
                    <option value="">All</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected($userId == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Keyword (expense item)</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="e.g., cake, diesel" class="w-full border rounded-lg p-2">
            </div>
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 mt-6">
                    <input type="checkbox" name="has_purchases" value="1" @checked($hasPurchases) class="rounded">
                    <span class="text-sm text-gray-700">Has purchases</span>
                </label>
            </div>
            <div class="md:col-span-6 flex gap-3">
                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Apply Filters</button>
                <a href="{{ route('daily.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg">Reset</a>
            </div>
        </form>
    </div>

    {{-- Records Table --}}
    <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Daily Records</h2>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Cash</th>
                        <th class="px-4 py-2">POS</th>
                        <th class="px-4 py-2">Purchases / Restock</th>
                        <th class="px-4 py-2">Total</th> {{-- Cash + POS + Purchases --}}
                <th class="px-4 py-2">User</th>
                        <th class="px-4 py-2">Store</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="records-body">
@foreach($records as $record)
@php
    $purchases = $record->expenses->sum('amount');
    $total = ($record->cash + $record->pos + $purchases);
@endphp
<tr>
    <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($record->date)->format('d M, Y') }}</td>
    <td class="border px-4 py-2 text-green-700 font-medium">₦{{ number_format($record->cash, 2) }}</td>
    <td class="border px-4 py-2 text-blue-700 font-medium">₦{{ number_format($record->pos, 2) }}</td>
    <td class="border px-4 py-2 align-top">
        @if($record->expenses->isNotEmpty())
            <details class="text-sm text-gray-600">
                <summary class="cursor-pointer">
                    {{ $record->expenses->count() }} items (₦{{ number_format($purchases, 2) }})
                </summary>
                <div class="mt-2 space-y-1">
                    @foreach($record->expenses as $exp)
                        <div class="flex justify-between">
                            <span>{{ $exp->item }}</span>
                            <span>₦{{ number_format($exp->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </details>
        @else
            <em class="text-gray-400">None</em>
        @endif
    </td>
    <td class="border px-4 py-2 text-purple-700 font-bold">₦{{ number_format($total, 2) }}</td>
    <td class="border px-4 py-2">{{ $record->user->name ?? 'N/A' }}</td>
    <td class="border px-4 py-2">{{ $record->store->name ?? 'N/A' }}</td>
    <td class="border px-4 py-2 text-center space-x-2">
        <a href="{{ route('daily.edit', $record->id) }}" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">Edit</a>
        <form action="{{ route('daily.destroy', $record->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button onclick="return confirm('Delete this record?')" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm">Delete</button>
        </form>
    </td>
</tr>
@endforeach
</tbody>
            </table>
        </div>
    </div>

    {{-- Filtered Summary (matches current filters) --}}
    <div id="summary" class="bg-gray-50 shadow-md rounded-xl p-6 mt-6 border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Filtered Summary</h2>
        <div class="grid md:grid-cols-4 gap-4 text-center">
            <div class="p-4 bg-white rounded-lg shadow">
                <p class="text-gray-500 text-sm">Total Cash</p>
                <p id="summary-cash" class="text-lg font-bold text-green-700">₦{{ number_format($summary['totalCash'], 2) }}</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow">
                <p class="text-gray-500 text-sm">Total POS</p>
                <p id="summary-pos" class="text-lg font-bold text-blue-700">₦{{ number_format($summary['totalPos'], 2) }}</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow">
                <p class="text-gray-500 text-sm">Total Purchases</p>
                <p id="summary-expenses" class="text-lg font-bold text-amber-600">₦{{ number_format($summary['totalExpenses'], 2) }}</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow">
                <p class="text-gray-500 text-sm">Total (Cash + POS + Purchases)</p>
                <p id="summary-balance" class="text-lg font-bold text-purple-700">₦{{ number_format($summary['balance'], 2) }}</p>
            </div>
        </div>
    </div>
</div>

{{-- JS Section --}}
<script>
    let expenseIndex = 1;

    function addExpenseRow() {
        const container = document.getElementById('expense-container');
        const row = document.createElement('div');
        row.classList.add('flex', 'gap-2', 'mt-2');
        row.innerHTML = `
            <input type="text" name="expenses[${expenseIndex}][item]" placeholder="Item" class="flex-1 border rounded-lg p-2">
            <input type="number" name="expenses[${expenseIndex}][amount]" placeholder="Amount" min="0" class="w-40 border rounded-lg p-2">
            <button type="button" onclick="this.parentElement.remove()" class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm">×</button>
        `;
        container.appendChild(row);
        expenseIndex++;
    }

    document.getElementById('daily-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });

            const raw = await response.text();
            let data; try { data = JSON.parse(raw); } catch { data = { ok:false, raw }; }

            if (!response.ok || data.error) {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : data.raw) || 'Validation error';
                const errorBox = document.getElementById('error-message');
                errorBox.textContent = msg;
                errorBox.classList.remove('hidden');
                return;
            }

            // Success UI
            const successBox = document.getElementById('success-message');
            successBox.textContent = 'Record saved successfully!';
            successBox.classList.remove('hidden');
            setTimeout(() => successBox.classList.add('hidden'), 3000);
            document.getElementById('error-message').classList.add('hidden');

            // Insert new row
            const tbody = document.getElementById('records-body');
            const record = data.record || {};
            const expenses = Array.isArray(record.expenses) ? record.expenses : [];
            const expensesHtml = expenses.length
                ? `
                    <details class="text-sm text-gray-600">
                        <summary class="cursor-pointer">
                            ${expenses.length} items (₦${expenses.reduce((t, e) => t + parseFloat(e.amount || 0), 0).toFixed(2)})
                        </summary>
                        <div class="mt-2 space-y-1">
                            ${expenses.map(e => `
                                <div class="flex justify-between">
                                    <span>${e.item ?? ''}</span>
                                    <span>₦${parseFloat(e.amount || 0).toFixed(2)}</span>
                                </div>`).join('')}
                        </div>
                    </details>`
                : '<em class="text-gray-400">None</em>';

            const dateStr = record.date ?? new Date().toISOString().slice(0,10);
            const formattedDate = new Date(dateStr).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
            const purchasesTotal = expenses.reduce((t, e) => t + parseFloat(e.amount || 0), 0);
            const rowTotal = (parseFloat(record.cash || 0) + parseFloat(record.pos || 0) + purchasesTotal);

            const username = record.user?.name ?? 'N/A';
            const storename = record.store?.name ?? 'N/A';

            const tr = document.createElement('tr');
            tr.classList.add('bg-yellow-50');
            tr.innerHTML = `
                <td class="border px-4 py-2">${formattedDate}</td>
                <td class="border px-4 py-2 text-green-700 font-medium">₦${parseFloat(record.cash || 0).toFixed(2)}</td>
                <td class="border px-4 py-2 text-blue-700 font-medium">₦${parseFloat(record.pos || 0).toFixed(2)}</td>
                <td class="border px-4 py-2 align-top">${expensesHtml}</td>
                <td class="border px-4 py-2 text-purple-700 font-bold">₦${rowTotal.toFixed(2)}</td>
                <td class="border px-4 py-2">${username}</td>
                <td class="border px-4 py-2">${storename}</td>
                <td class="border px-4 py-2 text-center space-x-2">
                    <a href="/daily/${record.id}/edit" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">Edit</a>
                    <form action="/daily/${record.id}" method="POST" class="inline">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button onclick="return confirm('Delete this record?')" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm">Delete</button>
                    </form>
                </td>
            `;
            tbody.prepend(tr);
            setTimeout(() => tr.classList.remove('bg-yellow-50'), 3000);

            // Update top summary with values from server (global aggregates)
            const s = data.summary || {};
            document.getElementById('summary-cash').textContent     = `₦${parseFloat(s.totalCash || 0).toFixed(2)}`;
            document.getElementById('summary-pos').textContent      = `₦${parseFloat(s.totalPos || 0).toFixed(2)}`;
            document.getElementById('summary-expenses').textContent = `₦${parseFloat(s.totalExpenses || 0).toFixed(2)}`;
            document.getElementById('summary-balance').textContent  = `₦${parseFloat(s.balance || 0).toFixed(2)}`;

            // Reset form
            form.reset();
            document.getElementById('expense-container').innerHTML = `
                <div class="flex gap-2">
                    <input type="text" name="expenses[0][item]" placeholder="Item" class="flex-1 border rounded-lg p-2">
                    <input type="number" name="expenses[0][amount]" placeholder="Amount" min="0" class="w-40 border rounded-lg p-2">
                </div>
            `;
            expenseIndex = 1;

        } catch (err) {
            console.error(err);
            const errorBox = document.getElementById('error-message');
            errorBox.textContent = 'An unexpected error occurred.';
            errorBox.classList.remove('hidden');
        }
    });
</script>

@endsection
