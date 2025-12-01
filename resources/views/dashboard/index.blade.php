@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Dashboard</h1>

    <!-- Row for cards -->
    <div class="row">
        <!-- Today's Total -->
        <div class="col-md-2 col-sm-4 mb-4">
            <div class="card h-100 text-white bg-primary">
                <div class="card-header">Today's Total</div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">${{ number_format($todaysTotal, 2) }}</h5>
                    <p class="card-text">Total transactions for today.</p>
                </div>
            </div>
        </div>

        <!-- Total Cash Balance -->
        <div class="col-md-2 col-sm-4 mb-4">
            <div class="card h-100 text-white bg-success">
                <div class="card-header">Total Cash Balance</div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">${{ number_format($cashBalance, 2) }}</h5>
                    <p class="card-text">Total balance from cash transactions.</p>
                </div>
            </div>
        </div>

        <!-- Total POS Balance -->
        <div class="col-md-2 col-sm-4 mb-4">
            <div class="card h-100 text-white bg-warning">
                <div class="card-header">Total POS Balance</div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">${{ number_format($posBalance, 2) }}</h5>
                    <p class="card-text">Total balance from POS transactions.</p>
                </div>
            </div>
        </div>

        <!-- Monthly Cash Balance -->
        <div class="col-md-2 col-sm-4 mb-4">
            <div class="card h-100 text-white bg-info">
                <div class="card-header">Monthly Cash Balance</div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">${{ number_format($monthlyCashBalance, 2) }}</h5>
                    <p class="card-text">Total cash balance for the selected month.</p>
                </div>
            </div>
        </div>

        <!-- Monthly POS Balance -->
        <div class="col-md-2 col-sm-4 mb-4">
            <div class="card h-100 text-white bg-secondary">
                <div class="card-header">Monthly POS Balance</div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">${{ number_format($monthlyPosBalance, 2) }}</h5>
                    <p class="card-text">Total POS balance for the selected month.</p>
                </div>
            </div>
        </div>

        <!-- Placeholder card (for example purposes) -->
        <div class="col-md-2 col-sm-4 mb-4 d-none d-md-block">
            <div class="card h-100 bg-light">
                <div class="card-header">Placeholder</div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p class="card-text">Placeholder for additional information.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
