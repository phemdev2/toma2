<table>
    <thead>
        <tr>
            <th colspan="6">Daily Store Report from {{ $from }} to {{ $to }}</th>
        </tr>
        <tr>
            <th>Store</th>
            <th>Cash</th>
            <th>POS</th>
            <th>Expenses</th>
            <th>Balance</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData as $store)
            <tr>
                <td>{{ $store['store'] }}</td>
                <td>{{ $store['cash'] }}</td>
                <td>{{ $store['pos'] }}</td>
                <td>{{ $store['expenses'] }}</td>
                <td>{{ $store['balance'] }}</td>
                <td></td>
            </tr>
        @endforeach
        <tr>
            <th>Grand Totals</th>
            <th>{{ $grand['cash'] }}</th>
            <th>{{ $grand['pos'] }}</th>
            <th>{{ $grand['expenses'] }}</th>
            <th>{{ $grand['balance'] }}</th>
            <th></th>
        </tr>
    </tbody>
</table>
