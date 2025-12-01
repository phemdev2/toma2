@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h1 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-wide">📊 Daily Store Report</h1>

    {{-- 🔎 Date Filter --}}
    @php
        $selectedDate = !empty($latestDate)
            ? \Carbon\Carbon::parse($latestDate)
            : \Carbon\Carbon::now('Africa/Lagos')->subDay(); // fallback
    @endphp

    <form method="GET" action="{{ route('daily.report') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Select date</label>
            <input
                type="date"
                id="date"
                name="date"
                value="{{ $selectedDate->toDateString() }}"
                class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="px-4 py-2 rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-700">
                Filter
            </button>

            {{-- Quick nav: Prev / Today / Next --}}
            <button type="button" onclick="shiftDate(-1)"
                class="px-3 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300" aria-label="Previous day">
                ◀ Prev
            </button>
            <button type="button" onclick="setToday()"
                class="px-3 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300" aria-label="Set today">
                📅 Today
            </button>
            <button type="button" onclick="shiftDate(1)"
                class="px-3 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300" aria-label="Next day">
                Next ▶
            </button>
        </div>
    </form>

    <p class="text-sm text-gray-600 mb-6">
        Showing report for:
        <span class="font-semibold">{{ $selectedDate->translatedFormat('l, j F Y') }}</span>
    </p>

    {{-- Reports for each store (selected day) --}}
    <div id="report-content" class="space-y-6">
        @forelse($reportData as $storeReport)
            @php
                // robustly get first record (works if records is array or collection)
                $firstRecord = collect($storeReport['records'] ?? [])->first();

                // collect and flatten expenses safely
                $expenseItems = collect($storeReport['records'] ?? [])->flatMap(function($r) {
                    return collect($r->expenses ?? []);
                });

                $expenseSum = $expenseItems->sum('amount');

                // slug id for DOM
                $storeId = \Illuminate\Support\Str::slug($storeReport['store'], '_');
            @endphp

            <div
                class="mb-6 p-6 border rounded-2xl bg-gradient-to-r from-white to-gray-50 shadow-lg"
                id="store-{{ $storeId }}"
                data-store-name="{{ $storeReport['store'] }}"
                data-cash="{{ $storeReport['cash'] ?? 0 }}"
                data-pos="{{ $storeReport['pos'] ?? 0 }}"
                data-expenses="{{ $expenseSum ?? 0 }}"
            >
                {{-- Store name and date --}}
                <h2 class="text-xl font-bold mb-3 text-gray-800">
                    🏬 {{ $storeReport['store'] }}
                    <span class="block text-sm text-gray-500">
                        {{ $firstRecord && $firstRecord->date
                            ? \Carbon\Carbon::parse($firstRecord->date)->format('l, j F Y')
                            : '' }}
                    </span>
                </h2>

                <div class="text-sm space-y-1 text-gray-700">
                    <p class="store-cash">💵 <span class="font-semibold">Cash:</span> {{ number_format($storeReport['cash'] ?? 0, 0) }}</p>
                    <p class="store-pos">💳 <span class="font-semibold">POS:</span> {{ number_format($storeReport['pos'] ?? 0, 0) }}</p>

                    <p class="font-semibold mt-3">🧾 Purchases / Restock:</p>

                    @if($expenseItems->isNotEmpty())
                        <ul class="list-disc list-inside pl-3 text-gray-600 store-expenses-list">
                            @foreach($expenseItems as $exp)
                                <li data-amount="{{ $exp->amount }}">{{ $exp->item }} {{ number_format($exp->amount, 0) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 store-expenses-list">None</p>
                    @endif

                    <p class="font-bold text-lg mt-3 store-total">
                        {{-- Per-store Total = Cash + POS + Purchases --}}
                        🔹 Total: {{ number_format(($storeReport['cash'] ?? 0) + ($storeReport['pos'] ?? 0) + $expenseSum, 0) }}
                    </p>
                </div>

                {{-- 🟢 Per-store share buttons --}}
                <div class="mt-4 flex gap-3">
                    <button onclick="shareWhatsAppStore('store-{{ $storeId }}', '2349159116968')"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl shadow transition"
                        aria-label="Share this store to WhatsApp">
                        📲 Share this store
                    </button>

                    <button onclick="shareEmailStore('store-{{ $storeId }}')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl shadow transition"
                        aria-label="Email this store">
                        📧 Email this store
                    </button>
                </div>
            </div>
        @empty
            <div class="p-6 border rounded-xl text-gray-600">No records for the selected day.</div>
        @endforelse
    </div>

    {{-- Grand Total Box --}}
    <div class="p-6 border rounded-2xl bg-green-50 shadow-xl mb-6">
        <h2 class="text-xl font-bold mb-3 text-green-800">🏆 Grand Totals</h2>
        <div class="text-sm space-y-1 text-green-700">
            <p>💵 Cash: {{ number_format($grand['cash'] ?? 0, 0) }}</p>
            <p>💳 POS: {{ number_format($grand['pos'] ?? 0, 0) }}</p>
            <p>🧾 Purchases / Restock: {{ number_format($grand['expenses'] ?? 0, 0) }}</p>
            <p class="font-bold text-lg">
                {{-- Grand Balance = Cash + POS + Expenses --}}
                💰 Balance: {{ number_format($grand['balance'] ?? 0, 0) }}
            </p>
        </div>
    </div>

    {{-- 🔵 Share/Export All (keeps the selected date) --}}
    <div class="flex flex-wrap gap-4">
        <button onclick="shareWhatsAppAll('2347062774479')"
            class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl shadow-lg transition"
            aria-label="Share all stores to WhatsApp">
            <span>📲 Share ALL Stores</span>
        </button>

        <button onclick="shareEmailAll()"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl shadow-lg transition"
            aria-label="Email all stores">
            <span>📧 Email ALL Stores</span>
        </button>
    </div>
</div>

{{-- 📜 Scripts --}}
<script>
/* ----- Date helpers ----- */
function getDateInput() { return document.getElementById('date'); }
function shiftDate(deltaDays) {
    const input = getDateInput();
    if (!input.value) return;
    const d = new Date(input.value + 'T00:00:00');
    d.setDate(d.getDate() + deltaDays);
    input.value = d.toISOString().slice(0,10);
    input.form.submit();
}
function setToday() {
    const input = getDateInput();
    const today = new Date();
    input.value = today.toISOString().slice(0,10);
    input.form.submit();
}

/* ----- Formatters (use dataset attributes + explicit lists) ----- */
function formatStoreReport(storeId) {
    const store = document.getElementById(storeId);
    if (!store) return '';

    const name = store.dataset.storeName || storeId;
    const cash = Number(store.dataset.cash || 0);
    const pos = Number(store.dataset.pos || 0);
    const expensesTotal = Number(store.dataset.expenses || 0);

    let report = `${name}\n`;
    report += `Cash: ${cash.toLocaleString()}\n`;
    report += `POS: ${pos.toLocaleString()}\n`;

    const expensesList = store.querySelectorAll('.store-expenses-list li');
    if (expensesList && expensesList.length > 0) {
        report += `Purchases:\n`;
        expensesList.forEach(li => {
            report += `- ${li.textContent.trim()}\n`;
        });
       
    } else {
        report += `Purchases / Restock: None\n`;
    }

    const total = cash + pos + expensesTotal;
    report += `Total: ${total.toLocaleString()}\n`;
    return report;
}

function formatFullReport() {
    const storeNodes = document.querySelectorAll("#report-content > div[id^='store-']");
    let report = "📊 DAILY STORE REPORT\n\n";
    storeNodes.forEach(node => report += formatStoreReport(node.id) + "\n");

    const grandBox = document.querySelector(".bg-green-50");
    if (grandBox) {
        // extract the visible lines from the Grand Totals box
        const lines = Array.from(grandBox.querySelectorAll('p')).map(p => p.textContent.trim()).filter(Boolean);
        report += "🏆 GRAND TOTALS\n";
        lines.forEach(line => report += `   ${line}\n`);
    }
    return report;
}

/* ----- Share: per store ----- */
function shareWhatsAppStore(storeId, phone) {
    const report = formatStoreReport(storeId);
    const encoded = encodeURIComponent(report);
    // use noopener for security when opening a new tab
    window.open("https://wa.me/" + phone + "?text=" + encoded, "_blank", "noopener");
}
function shareEmailStore(storeId) {
    const report = formatStoreReport(storeId);
    const subject = encodeURIComponent("Store Report");
    const body = encodeURIComponent(report);
    window.location.href = "mailto:?subject=" + subject + "&body=" + body;
}

/* ----- Share: all stores ----- */
function shareWhatsAppAll(phone) {
    const report = formatFullReport();
    const encoded = encodeURIComponent(report);
    window.open("https://wa.me/" + phone + "?text=" + encoded, "_blank", "noopener");
}
function shareEmailAll() {
    const report = formatFullReport();
    const subject = encodeURIComponent("Daily Store Report");
    const body = encodeURIComponent(report);
    window.location.href = "mailto:?subject=" + subject + "&body=" + body;
}
</script>
@endsection
