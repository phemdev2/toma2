<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->id }}</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Courier New', monospace;
            background: #e5e7eb;
            margin: 0;
            padding: 20px;
            color: #000;
            font-size: 12px;
        }

        .receipt-card {
            background: #fff;
            width: 100%;
            max-width: 302px; /* 80mm */
            margin: 0 auto;
            padding: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        h1 {
            font-size: 16px;
            text-align: center;
            margin: 0 0 5px 0;
            font-weight: 900;
            text-transform: uppercase;
        }

        p { margin: 2px 0; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .info-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .dashed-line {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
        }

        .solid-line {
            border-bottom: 1px solid #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 5px 0;
        }

        td {
            padding: 4px 0;
            vertical-align: top;
        }

        .item-name { display: block; }

        .total-section {
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 12px;
        }

        @media print {
            @page { margin: 0; }
            body { background: #fff; padding: 0; }
            .receipt-card { box-shadow: none; padding: 5px; width: 100%; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>

<body>

<div class="receipt-card">

    <!-- HEADER -->
    <div class="text-center">
        <h1>{{ optional(Auth::user()->store)->company ?? 'Store Name' }}</h1>
        <p>{{ optional(Auth::user()->store)->address ?? 'Address' }}</p>
        <p>Tel: {{ optional(Auth::user()->store)->phone ?? 'N/A' }}</p>
    </div>

    <div class="dashed-line"></div>

    <!-- META -->
    <div class="info-box">
        <span>RCPT: #{{ $order->id }}</span>
        <span>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/y H:i') }}</span>
    </div>

    <div class="info-box">
        <span>Staff: {{ \Illuminate\Support\Str::limit(Auth::user()->name, 12) }}</span>
        <span class="uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</span>
    </div>

    <div class="solid-line"></div>

    <!-- ITEMS -->
    <table>
        <thead>
            <tr>
                <th width="50%">Item</th>
                <th width="15%" class="text-center">Qty</th>
                <th width="35%" class="text-right">Total</th>
            </tr>
        </thead>

        <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td>
                    <span class="item-name text-bold">
                        {{ optional($item->product)->name ?? 'Unknown Item' }}
                    </span>
                    @if(optional($item->variant)->unit_type)
                        <span style="font-size:10px; color:#444;">
                            ({{ $item->variant->unit_type }})
                        </span>
                    @endif
                </td>

                <td class="text-center">{{ $item->quantity }}</td>

                <td class="text-right js-line-total">
                    {{ number_format($item->price * $item->quantity, 2) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="dashed-line"></div>

    <!-- TOTAL -->
    <div class="total-section">
        <span>TOTAL:</span>
        <span id="js-total">&#8358;0.00</span>
    </div>

    <!-- QR -->
    <div class="qr-section">
        <div id="qr-code"></div>
        <p style="font-size:10px; margin-top:4px;">SCAN TO VERIFY</p>
    </div>

    <!-- FOOTER -->
    <div class="text-center" style="margin-top: 15px;">
        <p style="font-size: 11px;">
            {{ optional(Auth::user()->store)->thank_you_message ?? 'Thank you for your patronage!' }}
        </p>
        <p style="font-size: 9px; margin-top: 5px;">Powered by IPOS</p>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    /* ------------------------------
       1. CALCULATE TOTAL USING JS
       ------------------------------ */
    let total = 0;

    document.querySelectorAll(".js-line-total").forEach(cell => {
        const value = parseFloat(cell.textContent.replace(/,/g, ""));
        if (!isNaN(value)) total += value;
    });

    document.getElementById("js-total").innerHTML =
        "&#8358;" + total.toLocaleString(undefined, { minimumFractionDigits: 2 });


    /* ------------------------------
       2. QR CODE GENERATION
       ------------------------------ */
    const verificationUrl = "{{ url('/orders/verify/' . $order->id) }}";

    new QRCode(document.getElementById("qr-code"), {
        text: verificationUrl,
        width: 100,
        height: 100,
        colorDark: "#000",
        colorLight: "#fff",
        correctLevel: QRCode.CorrectLevel.M
    });

    /* ------------------------------
       3. AUTO PRINT
       ------------------------------ */
    setTimeout(() => window.print(), 900);
});
</script>

</body>
</html>
