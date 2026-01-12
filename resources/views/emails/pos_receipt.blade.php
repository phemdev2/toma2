<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 400px; margin: 0 auto; border: 1px solid #e0e0e0; padding: 20px; border-radius: 8px; background: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px dashed #ddd; padding-bottom: 10px; margin-bottom: 15px; }
        .store-name { font-size: 20px; font-weight: 800; text-transform: uppercase; color: #000; margin-bottom: 2px; }
        .company-name { font-size: 14px; font-weight: 600; color: #555; margin-bottom: 5px; text-transform: uppercase; }
        .store-info { font-size: 12px; color: #666; line-height: 1.4; }
        .meta { font-size: 11px; color: #888; display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        
        .table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 15px; }
        .table th { text-align: left; border-bottom: 1px solid #333; padding: 5px 0; font-size: 11px; text-transform: uppercase; }
        .table td { border-bottom: 1px solid #f0f0f0; padding: 8px 0; vertical-align: top; }
        .qty { font-weight: bold; width: 30px; color: #444; }
        .item-name { color: #000; font-weight: 600; }
        .price { text-align: right; white-space: nowrap; font-family: monospace; }
        
        .totals { margin-top: 15px; border-top: 2px dashed #ddd; padding-top: 10px; }
        .row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
        .total-row { font-size: 18px; font-weight: 800; border-top: 1px solid #333; margin-top: 8px; padding-top: 8px; color: #000; }
        
        .footer { text-align: center; font-size: 11px; color: #999; margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px; }
        .thank-you { font-weight: bold; color: #333; font-size: 13px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="store-name">{{ $store->name ?? config('app.name') }}</div>
            
            @if(!empty($store->company))
                <div class="company-name">{{ $store->company }}</div>
            @endif

            <div class="store-info">
                @if(!empty($store->address)) {{ $store->address }}<br> @endif
                @if(!empty($store->phone)) Tel: {{ $store->phone }}<br> @endif
                @if(!empty($store->email)) {{ $store->email }}<br> @endif
                @if(!empty($store->website)) {{ $store->website }} @endif
            </div>
        </div>

        <!-- Meta Data -->
        <div class="meta">
            <span style="float:left">Date: {{ $order->created_at->format('d/m/Y h:i A') }}</span>
            <span style="float:right">#{{ substr($order->id ?? rand(1000,9999), -6) }}</span>
            <div style="clear:both"></div>
        </div>

        <!-- Items Table -->
        <table class="table">
            <thead>
                <tr>
                    <th class="qty">Qty</th>
                    <th>Item</th>
                    <th class="price">Amt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                @php
                    // Logic to find the name:
                    // 1. Try 'product_name' column in order_items
                    // 2. Try 'name' via product relationship
                    // 3. Try 'custom_name' column
                    // 4. Default to 'Item'
                    $name = $item->product_name ?? ($item->product->name ?? ($item->custom_name ?? 'Item'));

                    // Logic to find variant info
                    $variantInfo = $item->variant_name ?? ($item->variant->variant_name ?? ($item->variant->unit_type ?? null));
                @endphp
                <tr>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td>
                        <div class="item-name">{{ $name }}</div>
                        
                        @if(!empty($variantInfo))
                            <small style="color:#888; font-size:11px;">
                                ({{ $variantInfo }})
                            </small>
                        @endif
                    </td>
                    <td class="price">₦{{ number_format(($item->price * $item->quantity), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals">
            <div class="row">
                <span>Subtotal</span>
                <span>₦{{ number_format(($order->amount ?? 0) + ($order->discount ?? 0), 2) }}</span>
            </div>
            
            @if(($order->discount ?? 0) > 0)
            <div class="row" style="color: green;">
                <span>Discount</span>
                <span>-₦{{ number_format($order->discount, 2) }}</span>
            </div>
            @endif

            <div class="row total-row">
                <span>TOTAL</span>
                <span>₦{{ number_format($order->amount ?? 0, 2) }}</span>
            </div>
            
            <div class="row" style="font-size: 11px; color: #666; margin-top: 8px;">
                <span>Paid via:</span>
                <span style="text-transform: uppercase; font-weight:bold;">{{ $order->payment_method ?? 'Cash' }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">{{ $store->receipt_thank_you ?? 'Thank you for your patronage!' }}</div>
            <div>{{ $store->receipt_visit_again ?? 'Please visit us again.' }}</div>
            
            @if(isset($order->user))
                <div style="margin-top:5px;">Served by: {{ $order->user->name }}</div>
            @endif
        </div>
    </div>
</body>
</html>