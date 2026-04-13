<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\Notification;


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
        // 1. Fetch a totally fresh instance from the database
        $user = \App\Models\User::find(auth()->id());

        // --- BACKEND 30-DAY SECURITY CHECK (Simplified Logic) ---
        $hoursSinceUpdate = $user->updated_at->diffInHours(now());
        $requiredHours = 30 * 24; // 720 hours

        if ($hoursSinceUpdate < $requiredHours) {
            $daysLeft = ceil(($requiredHours - $hoursSinceUpdate) / 24);
            return response()->json([
                'message' => "Update Restricted. You cannot change your personal details for another {$daysLeft} day(s)."
            ], 422);
        }

        // --- BASE VALIDATION RULES ---
        $rules = [
            'username' => [
                'required',
                'string',
                'max:30',
                'regex:/^(?=.*[a-zA-Z].*[a-zA-Z].*[a-zA-Z])[a-zA-Z0-9._]+$/',
                \Illuminate\Validation\Rule::unique('users', 'username')->ignore($user->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
        ];

        // --- CONDITIONAL VALIDATION (STUDENTS ONLY) ---
        if ($user->role === 'student') {
            $rules['grade_level'] = ['required', 'string', 'max:50'];
            $rules['school_id'] = ['required', 'exists:schools,id'];
        }

        $validated = $request->validate($rules, [
            'username.regex' => 'Username must contain at least 3 letters and can only include letters, numbers, periods, and underscores.'
        ]);

        // --- SAVE DATA DIRECTLY TO DATABASE ---
        $user->username = $validated['username'];
        $user->first_name = $validated['first_name'];
        $user->middle_name = $validated['middle_name'];
        $user->last_name = $validated['last_name'];
        $user->suffix = $validated['suffix'];

        if ($user->role === 'student') {
            $user->grade_level = $validated['grade_level'];
            $user->school_id = $validated['school_id'];
        }

        // Force updated_at to the exact current time
        $user->updated_at = now();
        $user->save();

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

    public function storeFeedback(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'media' => 'nullable|image|max:2048', // 2MB max for screenshots
        ]);

        $feedback = new \App\Models\Feedback();
        $feedback->user_id = auth()->id();
        $feedback->category = $validated['category'];
        $feedback->subject = $validated['subject'];
        $feedback->message = $validated['message'];
        $feedback->status = 'pending';

        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('feedbacks', 'public');
            $feedback->media_url = $path;
        }

        $feedback->save();

        // --- TRIGGER EXISTING LARAVEL NOTIFICATION FOR ADMINS ---
        $admins = \App\Models\User::where('role', 'admin')->get();
        $categoryName = ucfirst(str_replace('_', ' ', $validated['category']));
        $senderName = auth()->user()->first_name . ' ' . auth()->user()->last_name;
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\LmsAlertNotification(
                'New Feedback Received',
                $senderName . ' submitted a new ' . $categoryName . ' report.',
                route('dashboard.feedback') . '?ticket=' . $feedback->id, // Add Ticket ID here
                'fas fa-inbox',
                'text-amber-500'
            ));
        }

        return response()->json(['success' => true, 'message' => 'Report submitted successfully!']);
    }

    public function loadFeedbackPartial()
    {
        // Fetch all feedbacks with their sender information
        $feedbacks = \App\Models\Feedback::with('sender')->latest()->get();
        
        $pendingCount = $feedbacks->where('status', 'pending')->count();
        $resolvedCount = $feedbacks->where('status', 'resolved')->count();
        $bugCount = $feedbacks->where('category', 'bug_report')->count();

        return view('dashboard.partials.admin.feedback', compact('feedbacks', 'pendingCount', 'resolvedCount', 'bugCount'));
    }

    /**
     * Handle Admin Reply to Feedback
     */
    public function replyToFeedback(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string'
        ]);

        $feedback = \App\Models\Feedback::findOrFail($id);
        $feedback->admin_reply = $request->admin_reply;
        $feedback->status = 'resolved';
        $feedback->replied_by_admin_id = auth()->id();
        $feedback->replied_at = now();
        $feedback->save();

        // --- TRIGGER EXISTING LARAVEL NOTIFICATION FOR THE SENDER ---
        $sender = \App\Models\User::find($feedback->user_id);
        if ($sender) {
            $sender->notify(new \App\Notifications\LmsAlertNotification(
                'Feedback Replied',
                'An admin has replied to your report: "' . $feedback->subject . '".',
                route('dashboard.profile') . '?ticket=' . $feedback->id,
                'fas fa-reply',
                'text-green-500'
            ));
        }

        return response()->json(['success' => true, 'message' => 'Reply sent and ticket resolved.']);
    }
}