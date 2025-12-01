<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    // Show a list of all stores
    public function index()
    {
        $stores = Store::all(); // Retrieve all stores
        return view('stores.index', compact('stores'));
    }

    // Show a single store's details
    public function show(Store $store)
    {
        return view('stores.show', compact('store'));
    }

    // Show the form to create a new store
    public function create()
    {
        return view('stores.create');
    }

    // Store a new store in the database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255', // Adjust as necessary
            'phone' => 'nullable|string|max:15', // Adjust as necessary
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'thank_you_message' => 'nullable|string|max:500',
            'visit_again_message' => 'nullable|string|max:500',
        ]);

        Store::create($request->only([
            'name',
            'company',
            'phone',
            'email',
            'website',
            'thank_you_message',
            'visit_again_message',
        ]));

        return redirect()->route('stores.index')->with('success', 'Store created successfully!');
    }

    // Show the form to edit an existing store
    public function edit(Store $store)
    {
        return view('stores.edit', compact('store'));
    }

    // Update an existing store in the database
    public function update(Request $request, Store $store)
{
    \Log::info('Update method called for store ID: ' . $store->id);
    
    $request->validate([
        'name' => 'required|string|max:255',
        // Other validation rules
    ]);

    if ($store->update($request->only([
        'name',
        'company',
        'phone',
        'email',
        'website',
        'thank_you_message',
        'visit_again_message',
    ]))) {
        \Log::info('Store updated successfully.');
    } else {
        \Log::error('Failed to update store.');
    }

    return redirect()->route('stores.index')->with('success', 'Store updated successfully!');
}   

    // Delete a store from the database
    public function destroy(Store $store)
    {
        $store->delete();

        return redirect()->route('stores.index')->with('success', 'Store deleted successfully!');
    }
}