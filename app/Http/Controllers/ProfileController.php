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

        // --- BACKEND 30-DAY SECURITY CHECK ---
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
        $user = $request->user();

        // --- BACKEND 30-DAY SECURITY CHECK ---
        $hoursSinceUpdate = $user->updated_at->diffInHours(now());
        $requiredHours = 30 * 24; // 720 hours

        if ($hoursSinceUpdate < $requiredHours) {
            $daysLeft = ceil(($requiredHours - $hoursSinceUpdate) / 24);
            return response()->json([
                'message' => "Update Restricted. You cannot change your password for another {$daysLeft} day(s)."
            ], 422);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'], // Built-in Laravel rule!
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)->mixedCase()->numbers()
            ],
        ]);

        // This automatically updates the updated_at column
        $user->update([
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

        // Prevent updating the `updated_at` timestamp so changing an avatar
        // doesn't accidentally trigger the 30-day profile/password lock!
        $user->timestamps = false;
        
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
            'media' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $totalSize = collect($value)->sum(fn($file) => $file->getSize());
                    if ($totalSize > 2097152) { // 2MB in bytes
                        $fail('The total size of all uploaded images must not exceed 2MB.');
                    }
                },
            ],
            'media.*' => 'image|mimes:jpg,jpeg,png,webp',
        ]);

        $feedback = new \App\Models\Feedback();
        $feedback->user_id = auth()->id();
        $feedback->category = $validated['category'];
        $feedback->subject = $validated['subject'];
        $feedback->message = $validated['message'];
        $feedback->status = 'open';

        if ($request->hasFile('media')) {
            $paths = [];
            foreach ($request->file('media') as $file) {
                $paths[] = $file->store('feedbacks', 'public');
            }
            $feedback->media_url = json_encode($paths);
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

        $feedback->load('messages');
        $newTicket = [
            'id' => $feedback->id,
            'subject' => $feedback->subject,
            'category' => ucwords(str_replace('_', ' ', $feedback->category)),
            'message' => $feedback->message,
            'status' => $feedback->status,
            'messages' => $feedback->messages->map(function($m) {
                return [
                    'id' => $m->id,
                    'message' => $m->message,
                    'sender' => $m->sender,
                    'date' => $m->created_at->format('M d, Y h:i A')
                ];
            })->values(),
            'media_url' => is_array($feedback->media_url) ? $feedback->media_url : ($feedback->media_url ? [$feedback->media_url] : []),
            'date' => $feedback->created_at->format('M d, Y h:i A')
        ];

        return response()->json(['success' => true, 'message' => 'Report submitted successfully!', 'ticket' => $newTicket]);
    }

    public function loadFeedbackPartial()
    {
        // Auto-close resolved tickets older than 3 days dynamically (fallback for cron)
        \App\Models\Feedback::where('status', 'resolved')
            ->where('updated_at', '<=', now()->subDays(3))
            ->update(['status' => 'closed']);

        $feedbacks = \App\Models\Feedback::with(['sender', 'messages.sender'])->latest()->get();
        
        $pendingCount = $feedbacks->whereIn('status', ['open', 'waiting_on_support'])->count();
        $resolvedCount = $feedbacks->where('status', 'resolved')->count();
        $bugCount = $feedbacks->where('category', 'bug_report')->count();

        return view('dashboard.partials.admin.feedback', compact('feedbacks', 'pendingCount', 'resolvedCount', 'bugCount'));
    }

    public function destroyFeedback($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $feedback = \App\Models\Feedback::findOrFail($id);
        
        if ($feedback->media_url) {
            $urls = is_array($feedback->media_url) ? $feedback->media_url : [$feedback->media_url];
            foreach ($urls as $url) {
                $path = str_replace(url('/storage') . '/', '', $url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }
        
        $feedback->delete();
        return response()->json(['success' => true, 'message' => 'Ticket deleted successfully.']);
    }

    public function bulkDeleteFeedback(\Illuminate\Http\Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:feedbacks,id'
        ]);

        $feedbacks = \App\Models\Feedback::whereIn('id', $request->ids)->get();

        foreach ($feedbacks as $feedback) {
            if ($feedback->media_url) {
                $urls = is_array($feedback->media_url) ? $feedback->media_url : [$feedback->media_url];
                foreach ($urls as $url) {
                    $path = str_replace(url('/storage') . '/', '', $url);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
            }
            $feedback->delete();
        }

        return response()->json(['success' => true, 'message' => count($request->ids) . ' ticket(s) deleted successfully.']);
    }

    /**
     * Handle Admin Reply to Feedback
     */
    public function replyToFeedback(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string',
            'status' => 'required|in:in_progress,waiting_on_user,resolved' // Force admin to pick next step
        ]);

        $feedback = \App\Models\Feedback::findOrFail($id);
        
        // Prevent replies to Hard Closed tickets
        if ($feedback->status === 'closed') {
            return response()->json(['message' => 'This ticket is permanently closed.'], 422);
        }

        // 1. Create the message thread entry
        $msg = $feedback->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->admin_reply,
        ]);
        $msg->load('sender');

        // 2. Update the main ticket status
        $feedback->status = $request->status;
        $feedback->save();

        // 3. Notify the sender with dynamic status context
        $sender = User::find($feedback->user_id);
        if ($sender) {
            
            // Map the raw status to user-friendly text, icons, and colors
            $statusText = '';
            $icon = 'fas fa-reply';
            $iconColor = 'text-blue-500';

            switch ($request->status) {
                case 'in_progress':
                    $statusText = 'and is currently working on it (In Progress).';
                    $icon = 'fas fa-spinner';
                    $iconColor = 'text-blue-500';
                    break;
                case 'waiting_on_user':
                    $statusText = 'and is waiting for your response.';
                    $icon = 'fas fa-question-circle';
                    $iconColor = 'text-amber-500';
                    break;
                case 'resolved':
                    $statusText = 'and has marked the ticket as Resolved.';
                    $icon = 'fas fa-check-circle';
                    $iconColor = 'text-green-500';
                    break;
            }

            $sender->notify(new \App\Notifications\LmsAlertNotification(
                'Ticket Update',
                'Support replied to "' . $feedback->subject . '" ' . $statusText,
                route('dashboard.profile') . '?ticket=' . $feedback->id,
                $icon,
                $iconColor
            ));
        }

        return response()->json([
            'success' => true, 
            'message' => 'Response sent successfully.',
            'msg' => $msg,
            'status' => $request->status
        ]);
    }

    /**
     * Handle User Reply to Feedback
     */
    public function userReplyToFeedback(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $feedback = \App\Models\Feedback::where('user_id', auth()->id())->findOrFail($id);

        if ($feedback->status === 'closed') {
            return response()->json(['message' => 'This ticket is permanently closed.'], 422);
        }

        // Add the user's message to the thread
        $feedback->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // PING-PONG: Ball is back in the admin's court! 
        // Even if it was 'resolved', replying reopens it.
        $feedback->status = 'waiting_on_support';
        $feedback->save();

        return response()->json(['success' => true, 'message' => 'Reply sent successfully.']);
    }

    public function broadcastNotification(\Illuminate\Http\Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'required|in:info,warning,success'
        ]);

        \App\Models\Broadcast::create([
            'subject' => $request->subject,
            'message' => $request->message,
            'type'    => $request->type
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Broadcast published successfully.'
        ]);
    }

    /**
     * Fetch standard notifications + global broadcasts
     */
    public function getNotifications()
    {
        $user = auth()->user();

        // 1. Fetch Standard Notifications (Last 30 days)
        $standardNotifs = $user->notifications()
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'is_broadcast' => false,
                    'title' => $notif->data['title'],
                    'message' => $notif->data['message'],
                    'url' => $notif->data['url'],
                    'icon' => $notif->data['icon'],
                    'colorClass' => $notif->data['colorClass'],
                    'time_ago' => $notif->created_at->diffForHumans(),
                    'timestamp' => $notif->created_at->timestamp,
                    'is_read' => $notif->read_at !== null
                ];
            });

        // 2. Fetch Global Broadcasts (Last 30 days)
        $broadcasts = \App\Models\Broadcast::where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->get();
            
        $readBroadcastIds = \App\Models\BroadcastRead::where('user_id', $user->id)
            ->pluck('broadcast_id')
            ->toArray();

        $broadcastNotifs = $broadcasts->map(function ($broadcast) use ($readBroadcastIds) {
            $icon = 'fas fa-bullhorn';
            $colorClass = 'text-blue-500';

            if ($broadcast->type === 'warning') {
                $icon = 'fas fa-exclamation-triangle';
                $colorClass = 'text-amber-500';
            } elseif ($broadcast->type === 'success') {
                $icon = 'fas fa-check-circle';
                $colorClass = 'text-green-500';
            }

            return [
                'id' => $broadcast->id,
                'is_broadcast' => true, // Flag it as a broadcast!
                'title' => $broadcast->subject,
                'message' => $broadcast->message,
                'url' => '#broadcast', 
                'icon' => $icon,
                'colorClass' => $colorClass,
                'time_ago' => $broadcast->created_at->diffForHumans(),
                'timestamp' => $broadcast->created_at->timestamp,
                'is_read' => in_array($broadcast->id, $readBroadcastIds)
            ];
        });

        // 3. Merge, Sort by Newest, and Count Unread
        $allNotifications = collect($standardNotifs)->merge($broadcastNotifs)->sortByDesc('timestamp')->values();
        $unreadCount = $allNotifications->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'notifications' => $allNotifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function markNotificationRead(\Illuminate\Http\Request $request, $id)
    {
        $user = auth()->user();
        
        // Robust check for the broadcast flag (Supports ?is_broadcast=true or JSON body)
        $isBroadcast = $request->boolean('is_broadcast') || $request->query('is_broadcast') === 'true';

        if ($isBroadcast) {
            // FIX: Use DB facade with insertOrIgnore to bypass any Model $fillable issues
            \Illuminate\Support\Facades\DB::table('broadcast_reads')->insertOrIgnore([
                'user_id' => $user->id,
                'broadcast_id' => $id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json(['success' => true]);
            
        } else {
            // Standard notification marking
            $notification = $user->notifications()->find($id);
            
            if ($notification && is_null($notification->read_at)) {
                $notification->markAsRead();
                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => true, 'message' => 'Already read']);
        }
    }
}