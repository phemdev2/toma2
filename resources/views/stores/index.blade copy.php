@extends('layouts.app')

@section('title', 'Store List')

@section('content')
<div class="container">
    <h1>Store List</h1>

    <!-- Button to Create a New Store -->
    <div class="mb-3">
        <a href="{{ route('stores.create') }}" class="btn btn-success">Create New Store</a>
    </div>

    @if($stores->isEmpty())
        <p>No stores available.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stores as $store)
                    <tr>
                        <td>{{ $store->id }}</td>
                        <td>{{ $store->name }}</td>
                        <td>
                            <!-- Add actions if needed, such as edit or delete -->
                            <a href="#" class="btn btn-primary btn-sm">Edit</a>
                            <form action="#" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
