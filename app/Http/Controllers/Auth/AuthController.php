<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

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
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        event(new Registered($user));

        session(['verify_email' => $user->email]);

        return redirect()->route('verification.notice');
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

            // Check if email is verified
            if (!Auth::user()->hasVerifiedEmail()) {

                Auth::logout(); // prevent access

                return back()->withErrors([
                    'email' => 'You must verify your email first.'
                ])->onlyInput('email');
            }

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