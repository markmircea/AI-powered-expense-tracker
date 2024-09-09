<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            Log::info('User logged in successfully', ['user' => Auth::user()]);

            return Inertia::render('SpreadSheetComponent');
        }

        Log::warning('Failed login attempt', ['email' => $request->email]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}
