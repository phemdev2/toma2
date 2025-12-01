@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <h1 class="mb-4">Dashboard</h1>

        <div class="row">
            <!-- Chart Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Sales Overview</div>
                    <div class="card-body">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Recent Transactions</div>
                    <div class="card-body">
                        <table id="dataTable" class="display">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $transaction->product->name }}</td>
                                        <td>${{ number_format($transaction->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Chart.js
            if (document.getElementById('salesChart')) {
                var ctx = document.getElementById('salesChart').getContext('2d');
                var salesChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['January', 'February', 'March', 'April', 'May', 'June'],
                        datasets: [{
                            label: 'Sales',
                            data: [12, 19, 3, 5, 2, 3], // Replace with dynamic data
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Initialize DataTables
            if (document.getElementById('dataTable')) {
                $('#dataTable').DataTable();
            }
        });
    </script>
@endsection
