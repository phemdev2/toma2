<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->id }}</title>
    
    <style>
        /* OPTIMIZATION: Use system fonts for instant rendering (no downloads) */
        body { 
            font-family: 'Courier New', Courier, monospace; 
            background-color: #f3f4f6; 
            padding: 20px; 
            margin: 0; 
            color: #000; 
        }
        
        /* Container */
        .receipt-card {
            background: white;
            max-width: 480px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Typography */
        h1 { font-size: 20px; font-weight: 900; margin: 0 0 5px 0; text-transform: uppercase; text-align: center; }
        p { margin: 2px 0; font-size: 12px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .dashed-line { border-bottom: 1px dashed #000; margin: 10px 0; display: block; }
        
        /* Info Grid */
        .info-box { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12px; }
        th { text-align: left; border-bottom: 2px solid #000; padding: 5px 0; text-transform: uppercase; }
        td { padding: 5px 0; vertical-align: top; }
        .total-row { font-weight: bold; font-size: 16px; border-top: 2px solid #000; padding-top: 10px; margin-top: 10px; text-align: right; }

        /* QR Section */
        .qr-section { text-align: center; margin-top: 20px; }
        .qr-img { width: 100px; height: 100px; }

        /* Buttons (Hidden in print) */
        .btn-group { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
        .btn { padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; font-size: 14px; text-decoration: none; color: white; font-family: sans-serif; }
        .btn-print { background-color: #1f2937; }
        .btn-new { background-color: #6366f1; }
        .btn:hover { opacity: 0.9; }

        @media print {
            body { background: white; padding: 0; }
            .receipt-card { box-shadow: none; border: none; padding: 0; width: 100%; max-width: 100%; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="receipt-card">

    <!-- Header -->
    <div class="text-center">
        <h1>{{ Auth::user()->store->company ?? 'Store Name' }}</h1>
        <p>{{ Auth::user()->store->address }}</p>
        <p>Tel: {{ Auth::user()->store->phone }}</p>
    </div>

    <div class="dashed-line"></div>

    <!-- Info -->
    <div class="info-box">
        <span>Receipt #: <strong>{{ $order->id }}</strong></span>
        <span>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/y H:i') }}</span>
    </div>
    <div class="info-box">
        <span>Staff: {{ substr(Auth::user()->name, 0, 15) }}</span>
        <span style="text-transform: uppercase;">{{ str_replace('_', ' ', $order->payment_method) }}</span>
    </div>

    <div class="dashed-line"></div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th width="45%">Item</th>
                <th width="15%">Qty</th>
                <th width="20%">Price</th>
                <th width="20%" class="text-right">Amt</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        <span class="font-bold">{{ $item->product->name }}</span>
                        @if($item->variant) <br><span style="font-size:10px;">({{ $item->variant->unit_type }})</span> @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 0) }}</td>
                    <td class="text-right">{{ number_format($item->price * $item->quantity, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Total -->
    <div class="total-row">
        TOTAL: &#8358;{{ number_format($order->total_amount, 2) }}
    </div>

    <!-- QR Code -->
    <div class="qr-section">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ url('/receipt/' . $order->id) }}" 
             class="qr-img" 
             width="100" 
             height="100" 
             alt="QR" 
             loading="eager">
        <p style="font-size: 10px; margin-top: 5px;">SCAN TO VERIFY</p>
    </div>

    <!-- Footer -->
    <div class="text-center" style="margin-top: 15px;">
        <p>{{ Auth::user()->store->thank_you_message ?? 'Thank you for your patronage!' }}</p>
    </div>
   
    
</div>

<script>
    document.getElementById('newOrderBtn').addEventListener('click', function(e) {
        e.preventDefault();
        try {
            if (window.self !== window.top) {
                window.top.location.reload(); // Refresh POS
            } else {
                window.location.href = "{{ route('products.index') }}";
            }
        } catch (e) {
            window.location.href = "/";
        }
    });
</script>

</body>
</html>