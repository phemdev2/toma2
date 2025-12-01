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
    public function show($storeId)
    {
        $store = Store::with('storeInventories.product')->findOrFail($storeId);
        return view('store.show', compact('store'));
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
        ]);

        Store::create($request->only('name'));

        return redirect()->route('stores.index')->with('success', 'Store created successfully!');
    }
}
