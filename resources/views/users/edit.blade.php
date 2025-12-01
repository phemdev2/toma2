@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Edit User</h1>

    <!-- Success / Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 border border-green-300 rounded p-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 border border-red-300 rounded p-3 mb-4">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user) }}" method="POST"
        class="bg-white dark:bg-gray-900 shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        @csrf
        @method('PUT')

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Name</label>
            <input type="text" name="name" id="name"
                value="{{ old('name', $user->name) }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-800 focus:outline-none focus:shadow-outline"
                required>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Email</label>
            <input type="email" name="email" id="email"
                value="{{ old('email', $user->email) }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-800 focus:outline-none focus:shadow-outline"
                required>
        </div>

        <!-- Role -->
        <div class="mb-4">
            <label for="role" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Role</label>
            <select name="role" id="role"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-800 focus:outline-none focus:shadow-outline"
                required>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Store -->
        <div class="mb-6">
            <label for="store_id" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Store</label>
            <select name="store_id" id="store_id"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-800 focus:outline-none focus:shadow-outline">
                <option value="">Select a store (optional)</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ $user->store_id == $store->id ? 'selected' : '' }}>
                        {{ $store->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Submit -->
        <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-indigo-400">
            Update User
        </button>
    </form>
</div>
@endsection
