<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
        $schools = School::orderBy('name')->get();
        return view('auth.register', compact('schools'));
    }
    // 🔹 Handle Registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users',
            'user_id' => 'required|string|max:50|unique:users,user_id',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()
            ],

            'school_id' => 'required|exists:schools,id',

            'role' => ['required', Rule::in(['student', 'teacher'])],

            'grade_level' => [
                Rule::requiredIf($request->role === 'student'),
                'nullable',
                'string',
                'max:50'
            ],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'user_id' => $validated['user_id'],
            'school_id' => $validated['school_id'],
            'grade_level' => $validated['grade_level'],
            'role' => $validated['role'],
            'status' => 'pending', // 👈 NEW: Set default status here
        ]);

        event(new Registered($user));

        // Redirect back with the flags needed to open the modal
        return back()->with([
            'show_verification_modal' => true,
            'verify_email' => $user->email // matching your route's session variable
        ]);
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
            $user = Auth::user();

            // Check if the account is suspended
            if ($user->status === 'suspended') {
                Auth::logout(); // Prevent access
                
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact the administrator.'
                ])->onlyInput('email');
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                $unverifiedEmail = $user->email; // Capture the email
                Auth::logout(); // Prevent access

                return back()->withErrors([
                    'email' => 'You must verify your email first.'
                ])->with('unverified_email', $unverifiedEmail)->onlyInput('email');
            }

            // If neither suspended nor unverified, regenerate session and log them in
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
        Auth::logout(); // Remove user from session

        $request->session()->invalidate(); // destroy session
        $request->session()->regenerateToken(); // prevent csrf reuse

        return redirect('/login');
    }
}