<!DOCTYPE html>
<html>
<head>
    <title>Daily Records PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Daily Records Report</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Cash</th>
                <th>POS</th>
                <th>Total Expenses</th>
                <th>Balance</th>
                <th>User</th>
                <th>Store</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($record->date)->format('d M, Y') }}</td>
                    <td>₦{{ number_format($record->cash, 2) }}</td>
                    <td>₦{{ number_format($record->pos, 2) }}</td>
                    <td>₦{{ number_format($record->expenses->sum('amount'), 2) }}</td>
                    <td>₦{{ number_format(($record->cash + $record->pos) - $record->expenses->sum('amount'), 2) }}</td>
                    <td>{{ $record->user->name ?? 'N/A' }}</td>
                    <td>{{ $record->store->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
