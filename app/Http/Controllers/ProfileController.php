<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Return the profile blade view.
     * (Assuming your loadPartial JS just injects this HTML).
     */
    public function show()
    {
        return view('dashboard.partials.shared.profile');
    }

    /**
     * Update the user's personal information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required', 
                'email', 
                'max:255', 
                Rule::unique('users')->ignore($user->id) // Ensures email is unique, ignoring their own
            ],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.'
        ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'], // Built-in Laravel rule!
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)->mixedCase()->numbers()
            ],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.'
        ]);
    }

    /**
     * Update the user's avatar image.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Max 2MB
        ]);

        $user = $request->user();

        // Delete the old avatar from storage if they already have one
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store the new image in the 'avatars' folder inside storage/app/public
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update([
            'avatar' => $path,
        ]);

        return response()->json([
            'success' => true,
            'avatar_url' => asset('storage/' . $path),
            'message' => 'Avatar updated successfully.'
        ]);
    }
}