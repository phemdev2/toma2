@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 mt-8 bg-white shadow-md rounded-lg">
        <h1 class="text-3xl font-bold mb-4">Edit Store</h1>

        <form action="{{ route('stores.update', $store->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2">Store Name</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    class="border border-gray-300 rounded-md w-full p-2 focus:outline-none focus:ring focus:ring-blue-300" 
                    value="{{ $store->name }}" 
                    required
                >
            </div>
            <div class="mb-4">
                <label for="company" class="block text-gray-700 font-semibold mb-2">Store Name</label>
                <input 
                    type="text" 
                    name="company" 
                    id="company" 
                    class="border border-gray-300 rounded-md w-full p-2 focus:outline-none focus:ring focus:ring-blue-300" 
                    value="{{ $store->company }}" 
                    required
                >
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-gray-700 font-semibold mb-2">Store Phone</label>
                <input 
                    type="text" 
                    name="phone" 
                    id="phone" 
                    class="border border-gray-300 rounded-md w-full p-2 focus:outline-none focus:ring focus:ring-blue-300" 
                    value="{{ $store->phone }}" 
                    required
                >
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold mb-2">Store Email</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    class="border border-gray-300 rounded-md w-full p-2 focus:outline-none focus:ring focus:ring-blue-300" 
                    value="{{ $store->email }}" 
                    required
                >
            </div>

            <div class="mb-4">
                <label for="website" class="block text-gray-700 font-semibold mb-2">Store Website</label>
                <input 
                    type="text" 
                    name="website" 
                    id="website" 
                    class="border border-gray-300 rounded-md w-full p-2 focus:outline-none focus:ring focus:ring-blue-300" 
                    value="{{ $store->website }}" 
                >
            </div>

            <div class="mb-4">
                <label for="thank_you_message" class="block text-gray-700 font-semibold mb-2">Thank You Message</label>
                <textarea 
                    name="thank_you_message" 
                    id="thank_you_message" 
                    class="border border-gray-300 rounded-md w-full p-2 focus:outline-none focus:ring focus:ring-blue-300" 
                    required
                >{{ $store->thank_you_message }}</textarea>
            </div>

            <div class="mb-4">
                <label for="visit_again_message" class="block text-gray-700 font-semibold mb-2">Visit Again Message</label>
                <textarea 
                    name="visit_again_message" 
                    id="visit_again_message" 
                    class="border border-gray-300 rounded-md w-full p-2 focus:outline-none focus:ring focus:ring-blue-300" 
                    required
                >{{ $store->visit_again_message }}</textarea>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-md transition duration-200">Update Store</button>
                <a href="{{ route('stores.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">Cancel</a>
            </div>
        </form>
    </div>
@endsection