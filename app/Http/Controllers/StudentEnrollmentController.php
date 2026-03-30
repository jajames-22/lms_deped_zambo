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
            'user_id'     => auth()->id(),
        ], [
            'status' => 'in_progress'
        ]);

        // 4. Update the material access status and assign the student's ID
        $access->update([
            'status' => 'enrolled',
            'student_id' => auth()->id() // FIXED: Grabs the currently logged-in student's ID
        ]);

        return redirect('/dashboard')
            ->with('autoLoad', route('student.materials.show', $material->id))
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

        $lessons = DB::table('lessons')->where('materials_id', $id)->get();
        $exams = DB::table('exams')->where('material_id', $id)->get();

        // This returns the NAKED partial (the skeleton)
        return view('dashboard.partials.student.materials-show', compact('material', 'lessons', 'exams', 'isEnrolled'));
    }
}