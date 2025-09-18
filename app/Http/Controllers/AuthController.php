<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view("login");
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Role Check
            $user = Auth::user();
            if (!in_array($user->role, ['pharmacist', 'doctor'])) {
                Auth::logout();
                return response()->json([
                    'message' => 'Access forbidden.',
                ], 403); // 403
            }

            return response()->json([
                'message'   => 'Login successful. Redirecting...',
                'redirect'  => '/dashboard',
            ]);
        }

        return response()->json([
            'message' => 'Login failed. Please try again.'
        ], 422);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
