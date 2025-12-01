@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Store</h1>
        <form action="{{ route('stores.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Store Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Create Store</button>
            <a href="{{ route('stores.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
