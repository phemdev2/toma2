<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store; // Import the Store model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // Display a listing of users
    public function index()
    {
        $users = User::with('store')->get(); // Eager load store
        return view('users.index', compact('users'));
    }

    // Show the form for creating a new user
    public function create()
    {
        $roles = Role::all();
        $stores = Store::all(); // Fetch all stores
        return view('users.create', compact('roles', 'stores')); // Pass stores to the view
    }

    // Store a newly created user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'store_id' => $request->store_id, // Save store ID if provided
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    // Show the form for editing the specified user
    public function edit(User $user)
    {
        $roles = Role::all();
        $stores = Store::all(); // Fetch all stores
        return view('users.edit', compact('user', 'roles', 'stores')); // Pass stores to the view
    }

    // Update the specified user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'store_id' => $request->store_id, // Update store ID if provided
        ]);

        $user->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    // Remove the specified user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    // Assign a user to a specific store
    public function assignUserToStore(Request $request, User $user)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id', // Ensure store_id is valid
        ]);

        $user->store_id = $request->store_id; // Assign store ID
        $user->save(); // Save the changes

        return response()->json(['message' => 'User assigned to store successfully.']);
    }
    public function posLink(User $user)
{
    // Generate the POS link using the user's ID and associated store ID
    $storeId = $user->store_id; // Assuming the user's store_id is set
    $userId = $user->id;

    return route('pos.index', ['user_id' => $userId, 'store_id' => $storeId]);
}




}