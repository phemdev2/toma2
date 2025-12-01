<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Records Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #f9f9f9;
            color: #333;
            font-size: 13px;
            line-height: 1.5;
        }
        h1, h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        h1 {
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 30px;
            font-size: 22px;
        }
        .summary-box {
            display: inline-block;
            padding: 10px 15px;
            margin: 5px 10px 20px 0;
            border-radius: 6px;
            color: #fff;
            font-weight: bold;
        }
        .cash { background: #27ae60; }
        .pos { background: #2980b9; }
        .expenses { background: #c0392b; }
        .balance { background: #8e44ad; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        th {
            background: #3498db;
            color: #fff;
            text-transform: uppercase;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Daily Records Report</h1>

    @foreach($reportData as $store)
        <h2>{{ $store['store'] }} Report</h2>
        
        <div>
            <span class="summary-box cash">Cash: ₦{{ number_format($store['cash']) }}</span>
            <span class="summary-box pos">POS: ₦{{ number_format($store['pos']) }}</span>
            <span class="summary-box expenses">Expenses: ₦{{ number_format($store['expenses']) }}</span>
            <span class="summary-box balance">Balance: ₦{{ number_format($store['balance']) }}</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Cash</th>
                    <th>POS</th>
                    <th>Expenses</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($store['records'] as $r)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}</td>
                        <td>₦{{ number_format($r->cash) }}</td>
                        <td>₦{{ number_format($r->pos) }}</td>
                        <td>
                            @if($r->expenses->count())
                                @foreach($r->expenses as $e)
                                    {{ $e->item }} (₦{{ number_format($e->amount) }})<br>
                                @endforeach
                            @else
                                <em>No expenses</em>
                            @endif
                        </td>
                        <td>{{ $r->user->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <h2>Grand Total (All Stores)</h2>
    <div>
        <span class="summary-box cash">Cash: ₦{{ number_format($grand['cash']) }}</span>
        <span class="summary-box pos">POS: ₦{{ number_format($grand['pos']) }}</span>
        <span class="summary-box expenses">Expenses: ₦{{ number_format($grand['expenses']) }}</span>
        <span class="summary-box balance">Balance: ₦{{ number_format($grand['balance']) }}</span>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M Y h:i A') }} | Powered by Your Company
    </div>
</body>
</html>
