<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle post-login logic.
     */
    protected function authenticated(Request $request, $user)
    {
        // Validate store selection
        $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
        ]);

        // Find the selected store
        $store = Store::where('id', $request->store_id)
            ->where(function ($query) use ($user) {
                // Supports both single-store and many-to-many relations
                $query->where('user_id', $user->id)
                      ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id));
            })
            ->first();

        // If the store doesn’t belong to the user
        if (! $store) {
            throw ValidationException::withMessages([
                'store_id' => __('You are not authorized to access this store.'),
            ]);
        }

        // Save selected store to session
        session(['selected_store' => $store->id]);

        // Optionally persist store_id on the user model
        $user->update(['store_id' => $store->id]);

        // Refresh authenticated user context
        Auth::setUser($user);

        // Redirect to the dashboard or home
        return redirect()->route('home')
            ->with('status', 'Welcome! You are now logged into ' . $store->name);
    }

    /**
     * Logout override to clear store session.
     */
    public function logout(Request $request)
    {
        session()->forget('selected_store');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'You have been logged out.');
    }
}
