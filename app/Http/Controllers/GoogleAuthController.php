<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Callback
     */
    public function callback()
    {
        try {
            // Get user from Google
            $googleUser = Socialite::driver('google')->user();

            // 1. Check if user exists by Google ID
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // User exists, login
                Auth::login($user);
                return redirect()->intended('dashboard');
            }

            // 2. Check if user exists by Email (Account linking)
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Link Google ID to existing account
                $user->update([
                    'google_id' => $googleUser->id,
                ]);
                
                Auth::login($user);
                return redirect()->intended('dashboard');
            }

            // 3. Create new user
            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                // Generate a random secure password so the DB doesn't complain
                'password' => bcrypt(Str::random(24)), 
                'email_verified_at' => now(), // Auto-verify email
            ]);

            Auth::login($newUser);
            return redirect()->intended('dashboard');

        } catch (Exception $e) {
            // Log the error for debugging: Log::error($e->getMessage());
            return redirect()->route('login')->with('status', 'Something went wrong with Google Login. Please try again.');
        }
    }
}