@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 mt-8 bg-white shadow-md rounded-lg">
        <h1 class="text-3xl font-bold mb-4">Store Details</h1>
        <p class="mb-2"><strong>ID:</strong> {{ $store->id }}</p>
        <p class="mb-4"><strong>Name:</strong> {{ $store->name }}</p>

        <div class="flex space-x-4">
            <a href="{{ route('stores.edit', $store->id) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">Edit</a>
            
            <form action="{{ route('stores.destroy', $store->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-4 rounded-md transition duration-200" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
            
            <a href="{{ route('stores.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">Back to List</a>
        </div>
    </div>
@endsection