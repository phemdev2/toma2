@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Stores</h1>
    
    <div class="row">
        <!-- Loop through stores and display them -->
        @foreach ($stores as $store)
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $store->name }}</h5>
                    <p class="card-text">Location: {{ $store->location }}</p>
                    <p class="card-text">Contact: {{ $store->contact }}</p>
                    <a href="{{ route('stores.products', $store->id) }}" class="btn btn-primary">View Products</a>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Card for adding a new store -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Add New Store</h5>
                    <a href="{{ route('stores.create') }}" class="btn btn-success">Create Store</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    {{ $stores->links() }}
</div>
@endsection