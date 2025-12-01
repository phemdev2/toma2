@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto mt-20 p-6 bg-white shadow rounded">
    <h2 class="text-xl font-semibold text-red-600 mb-4">Subscription Expired</h2>
    <p class="mb-4">Your subscription expired on <strong>{{ $trialEndDate }}</strong>.</p>
    <p class="mb-6">Please renew to regain access to the system.</p>
    <a href="{{ route('subscription.create') }}" class="bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-800">Renew Subscription</a>
</div>
@endsection
