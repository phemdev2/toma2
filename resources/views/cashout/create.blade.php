@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-semibold mb-4">Request Cash Out</h2>

    @if (session('success'))
        <div class="mb-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 text-red-600">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Available Cash Balance for Today</label>
        <p class="mt-1 text-lg font-semibold text-gray-900">&#8358;{{ number_format(auth()->user()->getCashBalanceForToday(), 2) }}</p>
    </div>

    <form action="{{ route('cashout.store') }}" method="POST" onsubmit="return validateAmount();">
        @csrf
        <div class="mb-4">
            <label for="amount" class="block text-sm font-medium text-gray-700">Amount to Withdraw</label>
            <input type="number" name="amount" id="amount" required min="1" max="{{ auth()->user()->getCashBalanceForToday() }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Enter amount" />
            <span class="text-sm text-gray-500">Max: &#8358;{{ number_format(auth()->user()->getCashBalanceForToday(), 2) }}</span>
        </div>

        <div class="mb-4">
            <label for="charges" class="block text-sm font-medium text-gray-700">Charges</label>
            <input type="number" name="charges" id="charges" required min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Enter charges" />
            <span class="text-sm text-gray-500">Enter any applicable charges.</span>
        </div>

        <input type="hidden" name="payment_method" value="withdraw">

        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
            Withdraw Cash
        </button>
    </form>

    <script>
        function validateAmount() {
            const amountInput = document.getElementById('amount');
            const chargesInput = document.getElementById('charges');
            const maxAmount = parseFloat(amountInput.max);

            const totalWithdrawal = parseFloat(amountInput.value) + parseFloat(chargesInput.value);

            if (totalWithdrawal > maxAmount) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Amount',
                    text: 'The total amount (withdrawal + charges) exceeds your available balance for today.',
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return false;
            }
            return true;
        }
    </script>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
