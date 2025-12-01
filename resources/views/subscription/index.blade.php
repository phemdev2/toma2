@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="card shadow-lg p-4 mb-4 bg-light rounded">
        <div class="card-header text-center">
            <h2>Your Subscription Status</h2>
        </div>
        <div class="card-body">
            @if(Auth::check())
                <div class="status-info">
                    <h3 class="text-center mb-4">Subscription Details</h3>
                    
                    <!-- Subscription Information Table -->
                    <table class="min-w-full table-auto text-gray-700 mb-6">
                        <thead>
                            <tr class="bg-gray-100 text-left">
                                <th class="px-4 py-2 font-semibold">Plan Type</th>
                                <th class="px-4 py-2 font-semibold">Start Date</th>
                                <th class="px-4 py-2 font-semibold">Expiry Date</th>
                                <th class="px-4 py-2 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $planType ?? 'Not available' }}</td>
                                <td class="px-4 py-2">{{ $startDate ?? 'No start date available' }}</td>
                                <td class="px-4 py-2">{{ $subscriptionExpiryDate ?? 'No expiry date available' }}</td>
                                <td class="px-4 py-2 text-{{ $isSubscriptionExpired ? 'red-500' : 'green-500' }}">
                                    {{ $isSubscriptionExpired ? 'Expired' : 'Active' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Conditional Status Alerts -->
                    @if($isSubscriptionExpired)
                        <div class="alert alert-danger mt-4 p-3 bg-red-100 border-l-4 border-red-500">
                            <strong>Your subscription has expired!</strong> Please renew your subscription to continue enjoying our services.
                        </div>
                    @else
                        <div class="alert alert-success mt-4 p-3 bg-green-100 border-l-4 border-green-500">
                            <strong>Your subscription is active!</strong> You can continue using your plan without interruption.
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-warning p-3 bg-yellow-100 border-l-4 border-yellow-500">
                    <strong>You are not logged in!</strong> Please log in to view your subscription details.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
