@extends('layouts.app')

@section('title', 'Store Inventory Details')

@section('content')
    <div class="container">
        <h1 class="mb-4">{{ $store->name }} - Inventory Details</h1>

        <a href="{{ route('store_inventories.index') }}" class="btn btn-secondary mb-3">Back to Inventory List</a>

        <!-- Inventories Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventories as $inventory)
                        <tr>
                            <td>{{ $inventory->product->name }}</td>
                            <td>{{ $inventory->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
