<?php

namespace App\Http\Controllers;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function store($courseId)
    {
        Enrollment::firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $courseId
        ]);

        return redirect()->route('courses.show', $courseId);
    }
}