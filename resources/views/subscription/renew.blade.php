@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto p-6 bg-white shadow rounded">
    <h2 class="text-xl font-bold mb-4">Renew Your Subscription</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form action="{{ route('subscription.processRenewal') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block font-medium">Select Plan Type</label>
            <select name="plan_type" class="w-full border rounded p-2">
                <option value="basic">Basic</option>
                <option value="premium">Premium</option>
            </select>
        </div>

        <div>
            <label class="block font-medium">Select Duration</label>
            <select name="plani" class="w-full border rounded p-2">
                <option value="Monthly">Monthly</option>
                <option value="Yearly">Yearly</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
            Renew Now
        </button>
    </form>
</div>
@endsection
