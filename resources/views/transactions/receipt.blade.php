@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Receipt</h2>

    <div id="receipt" class="border p-4">
        <h3>Transaction Receipt</h3>
        <p><strong>ID:</strong> {{ $transaction->id }}</p>
        <p><strong>Date:</strong> {{ $transaction->created_at->format('Y-m-d H:i') }}</p>
        <p><strong>Payment Method:</strong> {{ ucfirst($transaction->payment_method) }}</p>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->name }}</td>
                        <td>₦{{ number_format($detail->price, 2) }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>₦{{ number_format($detail->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                    <td>${{ number_format($transaction->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <button id="print-button" class="btn btn-primary mt-3">Print Receipt</button>
</div>

@section('scripts')
<script>
    document.getElementById('print-button').addEventListener('click', function() {
        const receipt = document.getElementById('receipt').innerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Receipt</title>');
        printWindow.document.write('<style>body { font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ddd; padding: 8px; }</style>');
        printWindow.document.write('</head><body >');
        printWindow.document.write(receipt);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });
</script>
@endsection
@endsection
