@extends('layouts.app')

@section('title', 'Inventory List')

@section('content')
<div class="container mx-auto px-4">
    <!-- Card Background -->
    <div class="bg-purple-200 p-6 rounded-lg mb-4">
        <h1 class="text-2xl font-bold mb-4">Inventory List</h1>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('inventory.index') }}" class="mb-4">
            <input type="text" name="search" id="searchBar" placeholder="Search by product name..." 
                   class="border rounded-md px-2 py-1 w-full md:w-1/3" value="{{ request('search') }}">
            <button type="submit" class="bg-blue-600 text-white rounded-md px-4 py-1 mt-2">Search</button>
        </form>
        
        <div class="mb-3">
            <a href="{{ route('inventory.top-up.form') }}" class="bg-green-600 text-white hover:bg-green-700 rounded-md px-4 py-2">Top Up Inventory</a>
        </div>
    </div>

    <!-- Product Table -->
    @if($products->isEmpty())
        <p>No products available.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-2 md:px-4 border-b text-left">Product Name</th>
                        <th class="py-2 px-2 md:px-4 border-b text-left">Allow Overselling</th>
                        <th class="py-2 px-2 md:px-4 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-2 md:px-4 border-b">{{ $product->name }}</td>
                            <td class="py-2 px-2 md:px-4 border-b">
                                <form action="{{ route('inventory.update', $product->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="checkbox" name="allow_overselling" 
                                           {{ $product->allow_overselling ? 'checked' : '' }} 
                                           onchange="this.form.submit()" />
                                </form>
                            </td>
                            <td class="py-2 px-2 md:px-4 border-b flex items-center space-x-2">
                                <a href="{{ route('inventory.top-up.form') }}" class="bg-yellow-500 text-white hover:bg-yellow-600 rounded-md px-2 py-1">Top Up</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection