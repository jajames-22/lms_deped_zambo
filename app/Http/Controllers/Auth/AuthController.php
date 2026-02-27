<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    // 🔹 Show Login Page
    public function showLogin()
    {
        return view('auth.login');
    }

    // 🔹 Show Register Page
    public function showRegister()
    {
        return view('auth.register');
    }

    // 🔹 Handle Registration
    public function register(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Create user
        $user = User::create($validated);

        // For Autologin (to be discussed)
        // Auth::login($user);

        // Redirect
        return redirect('/login');
    }

    // 🔹 Handle Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    // 🔹 Handle Logout
    public function logout(Request $request)
    {
        Auth::logout(); //Remove user from session

        $request->session()->invalidate(); // destroy session
        $request->session()->regenerateToken(); // prevent csrf reuse

        return redirect('/login');
    }
}