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
        $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
        ]);

        // Find the selected store, ensure user is authorized for it
        $store = Store::where('id', $request->store_id)
            ->where(function ($query) use ($user) {
                // supports single and many-to-many store relationships
                $query->where('user_id', $user->id)
                      ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id));
            })
            ->first();

        if (! $store) {
            throw ValidationException::withMessages([
                'store_id' => __('You are not authorized to access this store.'),
            ]);
        }

        // ✅ Update user’s active store in DB
        $user->update(['store_id' => $store->id]);

        // ✅ Store selected store in session
        session(['selected_store' => $store->id]);

        // ✅ Refresh the authenticated user instance
        Auth::setUser($user->fresh());

        // Optional: redirect to a store-specific dashboard
        return redirect()->route('home')
            ->with('status', 'You are now logged into ' . $store->name);
    }

    /**
     * Custom logout to clear store session.
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
