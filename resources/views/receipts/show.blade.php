@extends('layouts.app')

@section('content')
<!-- Background wrapper for screen view -->
<div class="min-h-screen bg-gray-100 py-8 px-4 flex flex-col items-center">

    <!-- RECEIPT CONTAINER (Target for printing) -->
    <div id="receipt-container" class="bg-white p-6 shadow-xl rounded-sm w-full max-w-[380px] text-sm text-gray-800 font-mono leading-relaxed border-t-8 border-purple-600">
        
        <!-- 1. Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold uppercase tracking-widest text-gray-900 mb-2">
                {{ $order->store->name ?? 'MY STORE' }}
            </h1>
            <p class="text-xs text-gray-500">
                {{ $order->store->address ?? '123 Market Street, Lagos' }}<br>
                Tel: {{ $order->store->phone ?? '0800-000-0000' }}
            </p>
        </div>

        <!-- 2. Transaction Info -->
        <div class="border-b border-dashed border-gray-300 pb-3 mb-3 text-xs">
            <div class="flex justify-between">
                <span>Receipt #:</span>
                <span class="font-bold">{{ str_pad($order->id, 8, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between mt-1">
                <span>Date:</span>
                <span>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="flex justify-between mt-1">
                <span>Cashier:</span>
                <span>{{ strtoupper(substr($order->user->name ?? 'Staff', 0, 15)) }}</span>
            </div>
            @if($order->payment_method)
            <div class="flex justify-between mt-1">
                <span>Payment:</span>
                <span class="uppercase font-bold">{{ str_replace('_', ' ', $order->payment_method) }}</span>
            </div>
            @endif
        </div>

        <!-- 3. Items List -->
        <table class="w-full mb-4 text-xs">
            <thead>
                <tr class="border-b border-dashed border-gray-300">
                    <th class="py-2 text-left w-8">Qty</th>
                    <th class="py-2 text-left">Item</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-2 align-top">{{ $item->quantity }}</td>
                        <td class="py-2 align-top">
                            <div class="font-bold">{{ $item->product->name ?? 'Unknown Item' }}</div>
                            @if($item->variant)
                                <div class="text-[10px] text-gray-500 italic">({{ $item->variant->unit_type ?? '' }})</div>
                            @endif
                            <!-- Show unit price if qty > 1 -->
                            @if($item->quantity > 1)
                                <div class="text-[10px] text-gray-400">@ {{ number_format($item->price, 2) }}</div>
                            @endif
                        </td>
                        <td class="py-2 align-top text-right font-medium">
                            {{ number_format($item->price * $item->quantity, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 4. Totals -->
        <div class="border-t border-dashed border-gray-300 pt-3">
            <div class="flex justify-between text-base font-bold text-gray-900 mt-2">
                <span>TOTAL</span>
                <span>&#8358;{{ number_format($order->total_amount ?? $order->totalPrice(), 2) }}</span>
            </div>
        </div>

        <!-- 5. Footer -->
        <div class="mt-8 text-center text-xs">
            <div class="mb-2">
                <!-- SVG Barcode Generator (Optional placeholder) -->
                <svg class="w-full h-8 opacity-70" viewBox="0 0 100 20" preserveAspectRatio="none">
                    <rect x="0" y="0" width="100" height="20" fill="url(#pattern-barcode)"/>
                    <defs>
                        <pattern id="pattern-barcode" x="0" y="0" width="4" height="20" patternUnits="userSpaceOnUse">
                            <rect x="0" y="0" width="2" height="20" fill="#000"/>
                        </pattern>
                    </defs>
                </svg>
                <span class="tracking-widest text-[10px]">{{ $order->barcode ?? $order->id }}</span>
            </div>
            <p class="font-bold mb-1">Thank you for your patronage!</p>
            <p class="text-gray-400 text-[10px]">Powered by ModernPOS</p>
        </div>
    </div>

    <!-- ACTION BUTTONS (Hidden during print) -->
    <div class="mt-6 flex gap-3 print:hidden">
        <button onclick="window.print()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
            </svg>
            Print
        </button>
        
        <a href="{{ route('products.index') }}" class="flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all">
            Back to POS
        </a>
    </div>

</div>

<!-- CSS FOR PRINTING -->
<style>
    @media print {
        /* Hide everything */
        body * {
            visibility: hidden;
            background: #fff !important;
        }

        /* Show only the receipt container */
        #receipt-container, #receipt-container * {
            visibility: visible;
        }

        /* Position the receipt at the top-left */
        #receipt-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none;
            border-top: none; /* Remove colorful border for thermal print */
            max-width: 100%;
        }

        /* Hide the wrapper background/padding */
        .min-h-screen {
            padding: 0;
            margin: 0;
        }
        
        /* Ensure font remains dark for thermal printers */
        * {
            color: #000 !important;
        }
    }
</style>
@endsection