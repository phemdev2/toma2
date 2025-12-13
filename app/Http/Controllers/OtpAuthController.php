<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter; // For security
use Illuminate\Validation\ValidationException;

class OtpAuthController extends Controller
{
    /**
     * Step 1: Validate email, Generate OTP, Send it.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|email|exists:users,email',
        ], [
            'identifier.exists' => 'We could not find an account with that email.',
        ]);

        $email = $request->identifier;

        // Security: Rate Limit (Max 5 attempts per minute)
        $key = 'otp-send:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'identifier' => "Too many attempts. Please try again in $seconds seconds.",
            ]);
        }
        RateLimiter::hit($key);

        // 1. Generate Random 6-digit OTP
        $otp = rand(100000, 999999);

        // 2. Store in Cache for 5 minutes (300 seconds)
        // Key format: "otp_login_user@example.com"
        Cache::put('otp_login_' . $email, $otp, 300);

        // 3. Send Email
        // For production, use a Mailable class. For simplicity here:
        try {
            Mail::raw("Your Login Verification Code is: $otp. It expires in 5 minutes.", function ($message) use ($email) {
                $message->to($email)
                        ->subject('One-Time Login Code');
            });
        } catch (\Exception $e) {
            // Log error if mail fails
            // \Log::error($e->getMessage());
            return back()->withErrors(['identifier' => 'Unable to send email. Please check configuration.']);
        }

        // 4. Redirect to Verification Page
        // We flash the email to session so the user doesn't have to type it again
        return redirect()->route('login.otp.verify')->with([
            'identifier' => $email,
            'status' => 'Verification code sent to your email!',
        ]);
    }

    /**
     * Step 2: Show the form to enter the code.
     */
    public function showVerifyForm()
    {
        // Ensure we have an identifier in session, otherwise go back to login
        if (!session('identifier') && !old('identifier')) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp');
    }

    /**
     * Step 3: Validate the Code and Login.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|email',
            'otp' => 'required|numeric|digits:6',
        ]);

        $cacheKey = 'otp_login_' . $request->identifier;
        $cachedOtp = Cache::get($cacheKey);

        // Check if OTP exists and matches
        if (!$cachedOtp || $cachedOtp != $request->otp) {
            throw ValidationException::withMessages([
                'otp' => 'The provided code is invalid or has expired.',
            ]);
        }

        // Retrieve User
        $user = User::where('email', $request->identifier)->first();

        if ($user) {
            // 1. Login the user
            Auth::login($user);

            // 2. Clear the OTP from cache (prevent replay attacks)
            Cache::forget($cacheKey);

            // 3. Regenerate session ID (security best practice)
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors(['identifier' => 'User not found.']);
    }
}