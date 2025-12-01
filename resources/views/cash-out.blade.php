@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Cash Out</h1>

    <form action="{{ route('cashout.store') }}" method="POST">
    @csrf
    <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
    <div>
        <label for="amount">Amount:</label>
        <input type="number" name="amount" required>
        @error('amount')
            <div>{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label for="charges">Charges:</label>
        <input type="number" name="charges" value="0">
        @error('charges')
            <div>{{ $message }}</div>
        @enderror
    </div>
    <button type="submit">Withdraw</button>
</form>


    <div id="response-message" class="mt-3"></div>
</div>

@section('scripts')
<script>
    document.getElementById('cash-out-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        fetch('{{ route('cashout.process') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById('response-message');
            messageDiv.innerHTML = `<div class="alert alert-${data.message.includes('Error') ? 'danger' : 'success'}">${data.message}</div>`;
        })
        .catch(error => {
            console.error('Error:', error);
            const messageDiv = document.getElementById('response-message');
            messageDiv.innerHTML = `<div class="alert alert-danger">An unexpected error occurred.</div>`;
        });
    });
</script>
@endsection
8