<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Check-in & Check-out</title>
    <style>
        /* Include the CSS styles from your HTML */
    </style>
</head>
<body>

<div class="container">
    <h1>POS Check-in & Check-out</h1>
    <div class="balance" id="balance-info">
        Cash Balance: $<span id="cash-balance">100.00</span> | 
        POS Balance: $<span id="pos-balance">50.00</span> | 
        Total Balance: $<span id="total-balance">150.00</span> | 
        Total Charges: $<span id="total-charges">0.00</span>
    </div>

    <form action="{{ route('cashouts.store') }}" method="POST" id="withdraw-form">
        @csrf
        <input type="number" name="amount" id="amount" placeholder="Amount to Withdraw ($)" min="1" required>
        <input type="number" name="charge" id="charge" placeholder="Withdrawal Charge ($)" value="0.50" step="0.01">
        <button type="submit">Withdraw</button>
    </form>

    <table class="transaction-table">
        <thead>
            <tr>
                <th>Amount Withdrawn ($)</th>
                <th>Charge ($)</th>
            </tr>
        </thead>
        <tbody id="transaction-list">
            @foreach ($cashouts as $cashout)
                <tr>
                    <td>{{ number_format($cashout->amount, 2) }}</td>
                    <td>{{ number_format($cashout->charge, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    // Include your JavaScript logic here
</script>

</body>
</html>