<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction List</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Transactions</h1>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->type }}</td>
                            <td>${{ number_format($transaction->total, 2) }}</td>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#transactionDetailsModal" data-id="{{ $transaction->id }}" class="btn btn-info btn-sm">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for transaction details -->
    <div class="modal fade" id="transactionDetailsModal" tabindex="-1" role="dialog" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionDetailsModalLabel">Transaction Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="transaction-details-body">
                    <!-- Transaction details will be loaded here by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#transactionDetailsModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var transactionId = button.data('id'); // Extract info from data-* attributes
                
                // Show loading indicator
                document.getElementById('transaction-details-body').innerHTML = '<p>Loading...</p>';

                // Fetch transaction details via AJAX
                fetch(`/api/transactions/${transactionId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        
                        // Populate modal body with transaction details
                        var details = `
                            <h5>Transaction ID: ${data.id}</h5>
                            <p>Date: ${data.created_at}</p>
                            <p>Type: ${data.type}</p>
                            <p>Total: $${data.total.toFixed(2)}</p>
                            <h6>Items:</h6>
                            <ul>
                                ${data.items.map(item => `<li>${item.name} - ${item.quantity} x $${item.price.toFixed(2)}</li>`).join('')}
                            </ul>
                        `;
                        document.getElementById('transaction-details-body').innerHTML = details;
                    })
                    .catch(error => {
                        document.getElementById('transaction-details-body').innerHTML = `
                            <p class="text-danger">Failed to load transaction details. Please try again later.</p>
                        `;
                        console.error('Error fetching transaction details:', error);
                    });
            });
        });
    </script>
</body>
</html>
