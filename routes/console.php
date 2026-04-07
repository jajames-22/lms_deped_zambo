<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use App\Models\Feedback;

// Default Laravel inspire command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// YOUR NEW LMS CLEANUP TASK:
// This will automatically delete any notification older than 30 days from the database
Schedule::call(function () {
    DB::table('notifications')
        ->where('created_at', '<', now()->subDays(30))
        ->delete();
})->daily();

// Run this check once a day
Schedule::call(function () {
    $oldFeedbacks = \App\Models\Feedback::where('created_at', '<', now()->subDays(30))->get();

    foreach ($oldFeedbacks as $feedback) {
        // If there's an image, delete it from the public disk
        if ($feedback->media_url) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($feedback->media_url);
        }
        // Delete the database row
        $feedback->delete();
    }
})->daily();