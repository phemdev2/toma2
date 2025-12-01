@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-semibold">Cash Out</h2>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Whoops!',
                html: '<ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Available Cash Balance for Today</label>
        <p class="mt-1 text-lg font-semibold text-gray-900">&#8358;{{ number_format(auth()->user()->getCashBalanceForToday(), 2) }}</p>
    </div>

    <form action="{{ route('cashout.store') }}" method="POST" class="mt-4" onsubmit="return validateAmount();">
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

        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Withdraw Cash
        </button>
    </form>

    <script>
        function validateAmount() {
            const amountInput = document.getElementById('amount');
            const chargesInput = document.getElementById('charges');
            const submitButton = document.querySelector('button[type="submit"]');
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
            } else {
                submitButton.textContent = 'Processing...';
                submitButton.disabled = true;
            }
            return true;
        }
    </script>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
