<!-- resources/views/admin-settings.blade.php -->
@extends('layouts.app')

@section('title', 'Admin Settings')

@section('content')
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Admin Settings</h2>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-600 text-white p-4 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-600 text-white p-4 rounded-lg mb-6">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Settings Form -->
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="allow_overselling" class="block text-sm font-medium text-gray-700 mb-2">Allow Overselling</label>
                    <select id="allow_overselling" name="allow_overselling" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 py-2 px-3">
                        <option value="1" {{ $allowOverselling === 'true' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ $allowOverselling === 'false' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <!-- Add more settings here as needed -->
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Save Settings</button>
        </form>
    </div>
@endsection