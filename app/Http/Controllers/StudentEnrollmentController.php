<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialAccess;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentEnrollmentController extends Controller
{
    public function index()
    {
        // Fetch all enrollments for the logged-in student
        // We eager-load 'material', 'material.instructor', and 'material.tags' for better performance
        $enrollments = Enrollment::with(['material.instructor', 'material.tags'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        // Pass the $enrollments variable to your blade file
        return view('dashboard.partials.student.enrolled', compact('enrollments'));
    }
    
    public function acceptInvitation(Request $request, Material $material, $email)
    {
        // 1. Security check: Ensure the logged-in user is the one invited
        if (Auth::user()->email !== $email) {
            abort(403, 'This invitation was sent to a different email address.');
        }

        // 2. Process Access Record
        $access = MaterialAccess::where('material_id', $material->id)
            ->where('email', $email)
            ->firstOrFail();

        // 3. Create Enrollment record (tracking progress)
        Enrollment::firstOrCreate([
            'material_id' => $material->id,
            'user_id' => auth()->id(),
        ], [
            'status' => 'in_progress'
        ]);

        // 4. Update the material access status and assign the student's ID
        $access->update([
            'status' => 'enrolled',
            'student_id' => auth()->id()
        ]);

        // THE FIX: Do a hard redirect straight to the full-page module URL
        return redirect()->route('student.materials.show', $material->id)
            ->with('success', 'Successfully enrolled!');
    }
    

    public function enrollWithCode(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string|max:10'
        ]);

        // 1. Find the private material matching the code
        $material = Material::where('access_code', strtoupper($request->access_code))
            ->where('is_public', false) // Ensures codes only work for private materials
            ->first();

        if (!$material) {
            // Since this was previously AJAX, we'll redirect back with an error message
            return redirect()->back()->with('error', 'Invalid or expired access code.');
        }

        // 2. Check if already enrolled
        $alreadyEnrolled = Enrollment::where('material_id', $material->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyEnrolled) {
            return redirect()->route('student.materials.show', $material->id)
                             ->with('info', 'You are already enrolled in this module.');
        }

        // 3. Create Enrollment record
        Enrollment::create([
            'material_id' => $material->id,
            'user_id' => Auth::id(),
            'status' => 'in_progress'
        ]);

        // 4. Keep the MaterialAccess table synced
        MaterialAccess::updateOrCreate([
            'material_id' => $material->id,
            'email' => Auth::user()->email,
        ], [
            'student_id' => Auth::id(),
            'status' => 'enrolled'
        ]);

        // THE FIX: Use a hard redirect just like acceptInvitation
        return redirect()->route('student.materials.show', $material->id)
                         ->with('success', 'Successfully enrolled!');
    }
    
    public function show($id)
    {
        $material = Material::findOrFail($id);

        // Security check for students
        $isEnrolled = Enrollment::where('material_id', $id)
            ->where('user_id', Auth::id())
            ->exists();

        $hasAccess = $isEnrolled || in_array(Auth::user()->role, ['teacher', 'admin', 'superadmin']);

        if (!$hasAccess) {
            abort(403, 'You are not enrolled in this module.');
        }

        $lessons = DB::table('lessons')->where('material_id', $id)->get();
        $exams = DB::table('exams')->where('material_id', $id)->get();

        // FIX: Return the view instead of redirecting!
        return view('dashboard.partials.student.materials-show', compact('material', 'lessons', 'exams', 'isEnrolled'));
    }
}