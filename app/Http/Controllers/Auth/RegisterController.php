<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        Log::info('Registration attempt', $request->except('password', 'password_confirmation'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

           Auth::login($user);

            Log::info('User registered successfully', ['user_id' => $user->id]);

            return redirect('/')->with('success', 'Registration successful!');
        } catch (\Exception $e) {
            Log::error('Registration failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }
}
