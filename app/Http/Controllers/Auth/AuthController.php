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
    // 🔹 Handle Registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:255',
            
            // 👈 UPDATED: Strict Username Validation
            'username' => [
                'required',
                'string',
                'max:30', // Max 30 characters
                'regex:/^[a-zA-Z0-9._]+$/', // Only letters, numbers, periods, and underscores
                'unique:users,username'
            ],
            
            'email' => 'required|email|unique:users',
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
        ], [
            // 👈 NEW: Friendly error message if they use invalid characters
            'username.regex' => 'Your username may only contain letters, numbers, periods, and underscores.'
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'username' => $validated['username'], 
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'school_id' => $validated['school_id'],
            'grade_level' => $validated['grade_level'] ?? null,
            'role' => $validated['role'],
            'status' => 'pending', 
        ]);

        event(new Registered($user));

        // Redirect back with the flags needed to open the modal
        return back()->with([
            'show_verification_modal' => true,
            'verify_email' => $user->email 
        ]);
    }

    // 🔹 Handle Login
    // 🔹 Handle Login
    public function login(Request $request)
    {
        // 1. Validate the generic login field
        $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // 2. Auto-detect if they typed an email or a username
        $loginType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $credentials = [
            $loginType => $request->login_id,
            'password' => $request->password,
        ];

        $remember = $request->boolean('remember');

        // 3. Attempt Login
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // ⛔ KICK OUT SUSPENDED USERS
            if ($user->status === 'suspended') {
                Auth::logout(); // Prevent access
                return back()->withErrors([
                    'login_id' => 'Your account has been suspended. Please contact the administrator.'
                ])->onlyInput('login_id');
            }

            // Check if the user has no email in the database
            if (empty($user->email)) {
                $request->session()->regenerate();
                return redirect('/register-email'); 
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                $unverifiedEmail = $user->email; 
                Auth::logout(); 
                return back()->withErrors([
                    'login_id' => 'You must verify your email first.'
                ])->with('unverified_email', $unverifiedEmail)->onlyInput('login_id');
            }

            // ✅ ALLOW PENDING & ACTIVE USERS TO PROCEED
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'login_id' => 'Invalid credentials.',
        ])->onlyInput('login_id');
    }

    // 🔹 Handle Logout
    public function logout(Request $request)
    {
        Auth::logout(); // Remove user from session

        $request->session()->invalidate(); // destroy session
        $request->session()->regenerateToken(); // prevent csrf reuse

        return redirect('/login');
    }

    public function showRegisterEmail()
    {
        // Prevent users who already have an email from seeing this
        if (!empty(auth()->user()->email)) {
            return redirect()->intended('/dashboard');
        }
        return view('auth.register-email');
    }

    /**
     * Store the provided email and trigger verification.
     */
    public function storeRegisterEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $user = auth()->user();
        $user->update([
            'email' => $validated['email']
        ]);

        // Trigger the verification email
        event(new \Illuminate\Auth\Events\Registered($user));

        // Log them out so they must verify their email to log back in
        Auth::logout();

        return redirect()->route('login')->with([
            'show_verification_modal' => true,
            'verify_email' => $validated['email']
        ]);
    }
}