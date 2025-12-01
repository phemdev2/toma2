@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4 mt-6">
        <h1 class="text-3xl font-bold mb-4">Stores</h1>
        <a href="{{ route('stores.create') }}" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-md transition duration-200">Create New Store</a>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 border border-green-200 rounded-md p-4 mt-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <table class="table-auto w-full mt-4 bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Company</th>
                    <th class="px-4 py-2 text-left">Phone</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Website</th>
                    <th class="px-4 py-2 text-left">Thank You Message</th>
                    <th class="px-4 py-2 text-left">Visit Again Message</th>
                    <!-- <th class="px-4 py-2 text-left">Created At</th>
                    <th class="px-4 py-2 text-left">Updated At</th> -->
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stores as $store)
                    <tr class="border-b border-gray-300 hover:bg-gray-100">
                        <td class="px-4 py-2">{{ $store->id }}</td>
                        <td class="px-4 py-2">{{ $store->name }}</td>
                        <td class="px-4 py-2">{{ $store->company }}</td>
                        <td class="px-4 py-2">{{ $store->phone }}</td>
                        <td class="px-4 py-2">{{ $store->email }}</td>
                        <td class="px-4 py-2">{{ $store->website }}</td>
                        <td class="px-4 py-2">{{ $store->thank_you_message }}</td>
                        <td class="px-4 py-2">{{ $store->visit_again_message }}</td>
                        <!-- <td class="px-4 py-2">
                            {{ optional($store->created_at)->format('Y-m-d H:i:s')?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2">
                            {{ optional($store->updated_at)->format('Y-m-d H:i:s')?? 'N/A' }}
                        </td> -->
                        <td class="px-4 py-2 flex space-x-2">
                            <a href="{{ route('stores.show', $store->id) }}" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-md transition duration-200">View</a>
                            <a href="{{ route('stores.edit', $store->id) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">Edit</a>
                            <form action="{{ route('stores.destroy', $store->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-4 rounded-md transition duration-200" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection