@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 tracking-wide">📊 Daily Store Report</h1>

    {{-- Reports for each store --}}
    <div id="report-content">
        @foreach($reportData as $storeReport)
            <div class="mb-6 p-6 border rounded-2xl bg-gradient-to-r from-white to-gray-50 shadow-lg">
                {{-- Store name and date --}}
                <h2 class="text-xl font-bold mb-3 text-gray-800">
                    🏬 {{ $storeReport['store'] }}
                    <span class="block text-sm text-gray-500">
                        {{ optional($storeReport['records']->first())->date 
                            ? \Carbon\Carbon::parse($storeReport['records']->first()->date)->format('l, j F Y') 
                            : '' }}
                    </span>
                </h2>

                <div class="text-sm space-y-1 text-gray-700">
                    <p>💵 <span class="font-semibold">Cash:</span> {{ number_format($storeReport['cash'], 0) }}</p>
                    <p>💳 <span class="font-semibold">POS:</span> {{ number_format($storeReport['pos'], 0) }}</p>

                    <p class="font-semibold mt-3">🧾 Expenses:</p>
                    @if($storeReport['records']->flatMap->expenses->isNotEmpty())
                        <ul class="list-disc list-inside pl-3 text-gray-600">
                            @foreach($storeReport['records']->flatMap->expenses as $exp)
                                <li>{{ $exp->item }} — {{ number_format($exp->amount, 0) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500">None</p>
                    @endif

                    <p class="font-bold text-lg mt-3">
                        🔹 Total: {{ number_format($storeReport['cash'] + $storeReport['pos'], 0) }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Grand Total Box --}}
    <div class="p-6 border rounded-2xl bg-green-50 shadow-xl mb-6">
        <h2 class="text-xl font-bold mb-3 text-green-800">🏆 Grand Totals</h2>
        <div class="text-sm space-y-1 text-green-700">
            <p>💵 Cash: {{ number_format($grand['cash'], 0) }}</p>
            <p>💳 POS: {{ number_format($grand['pos'], 0) }}</p>
            <p>🧾 Expenses: {{ number_format($grand['expenses'], 0) }}</p>
            <p class="font-bold text-lg">💰 Balance: {{ number_format($grand['balance'], 0) }}</p>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-4">
        {{-- Share on WhatsApp --}}
        <button onclick="shareWhatsApp('2347062774479')" 
            class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg transition">
            <span>📲 Share on WhatsApp</span>
        </button>

        {{-- Share on Email --}}
        <button onclick="shareEmail()" 
            class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg transition">
            <span>📧 Share via Email</span>
        </button>
    </div>

</div>

{{-- Sharing Scripts --}}
<script>
function shareWhatsApp(phoneNumber) {
    let report = document.getElementById("report-content").innerText;
    let encoded = encodeURIComponent("📊 Daily Store Report\n\n" + report);
    window.open("https://wa.me/" + phoneNumber + "?text=" + encoded, "_blank");
}

function shareEmail() {
    let report = document.getElementById("report-content").innerText;
    let subject = encodeURIComponent("Daily Store Report");
    let body = encodeURIComponent("📊 Daily Store Report\n\n" + report);
    window.location.href = "mailto:?subject=" + subject + "&body=" + body;
}
</script>
@endsection
