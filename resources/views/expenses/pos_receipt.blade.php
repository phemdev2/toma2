<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 400px; margin: 0 auto; border: 1px solid #eee; padding: 20px; border-radius: 8px; background: #f9f9f9; }
        .header { text-align: center; border-bottom: 2px dashed #ddd; padding-bottom: 10px; margin-bottom: 15px; }
        .store-name { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #000; }
        .meta { font-size: 12px; color: #666; display: flex; justify-content: space-between; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 15px; }
        .table th { text-align: left; border-bottom: 1px solid #ddd; padding: 5px 0; }
        .table td { border-bottom: 1px solid #eee; padding: 5px 0; vertical-align: top; }
        .qty { font-weight: bold; width: 30px; }
        .price { text-align: right; }
        .totals { margin-top: 15px; border-top: 2px dashed #ddd; padding-top: 10px; }
        .row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px; }
        .total-row { font-size: 16px; font-weight: bold; border-top: 1px solid #ddd; margin-top: 5px; padding-top: 5px; }
        .footer { text-align: center; font-size: 11px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="store-name">{{ $store->name ?? 'Store' }}</div>
            <div>{{ $store->address ?? '' }}</div>
            <div>{{ $store->phone ?? '' }}</div>
        </div>

        <div class="meta">
            <span>{{ $order->created_at->format('d/m/Y h:i A') }}</span>
            <span>Ref: {{ substr($order->order_number, -6) }}</span>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th class="qty">Qty</th>
                    <th>Item</th>
                    <th class="price">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td>
                        {{ $item->product_name }}
                        @if($item->variant_name)
                            <br><small style="color:#888">({{ $item->variant_name }})</small>
                        @endif
                    </td>
                    <td class="price">₦{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="row">
                <span>Subtotal:</span>
                <span>₦{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->discount > 0)
            <div class="row" style="color: green;">
                <span>Discount:</span>
                <span>-₦{{ number_format($order->discount, 2) }}</span>
            </div>
            @endif
            <div class="row total-row">
                <span>Total:</span>
                <span>₦{{ number_format($order->total, 2) }}</span>
            </div>
            <div class="row" style="font-size: 11px; color: #666; margin-top: 5px;">
                <span>Payment Method:</span>
                <span style="text-transform: uppercase;">{{ $order->payment_method }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your patronage!</p>
            <p>Served by: {{ $order->user->name ?? 'Staff' }}</p>
        </div>
    </div>
</body>
</html>