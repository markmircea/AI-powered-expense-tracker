<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LogoutController extends Controller
{
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Log::info('Logout initiated', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
        ]);

        // Logout the user
        Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        Log::info('Logout completed successfully');

        // Check if the request expects a JSON response
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        }

        // For web requests, redirect to the login page using Inertia
        return Inertia::location(route('login'));
    }
}
