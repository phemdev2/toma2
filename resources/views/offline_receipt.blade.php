<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline Checkout Receipt</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Offline Checkout Completed</div>
                    <div class="card-body">
                        <h1 class="text-center">Your checkout data has been saved locally.</h1>
                        <p class="text-center">Your order will be synced when you are back online.</p>
                        <p class="text-center">Thank you for shopping with us!</p>
                        <div class="text-center">
                            <a href="{{ url('/') }}" class="btn btn-primary">Return to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
