<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialAccess;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class StudentEnrollmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Get Active Enrollments (In Progress, Completed, Failed)
        $activeEnrollments = Enrollment::with(['material.instructor', 'material.tags'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'dropped') // Ensure dropped ones don't show here
            ->latest()
            ->get();

        // 2. Get Dropped Materials (FILTERED: Public modules only)
        $droppedAccesses = MaterialAccess::with(['material.instructor', 'material.tags'])
            ->where('email', $user->email)
            ->where('status', 'dropped')
            ->whereHas('material', function ($query) {
                // This guarantees private modules never show up in the dropped tab
                $query->where('is_public', true); 
            })
            ->latest()
            ->get();

        // Pass BOTH variables to the blade file matching what the tabs expect
        return view('dashboard.partials.student.enrolled', compact('activeEnrollments', 'droppedAccesses'));
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

        // If it's a public module OR invalid code, this triggers the error
        if (!$material) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired access code.']);
        }

        // 2. Check if already enrolled
        $alreadyEnrolled = Enrollment::where('material_id', $material->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('student.materials.show', $material->id)
            ]);
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

        return response()->json([
            'success' => true,
            'redirect_url' => route('student.materials.show', $material->id)
        ]);
    }

    public function show($id)
    {
        $material = Material::findOrFail($id);
        $user = Auth::user();

        // 1. Check if the user has an ACTIVE enrollment (Used to toggle the Enroll/Resume buttons)
        $isEnrolled = Enrollment::where('material_id', $id)
            ->where('user_id', $user->id)
            ->where('status', '!=', 'dropped')
            ->exists();

        // 2. Check if the user has permission to VIEW the overview page
        $hasAccess = false;
        
        if (in_array($user->role, ['teacher', 'admin', 'superadmin'])) {
            $hasAccess = true;
        } elseif ($material->is_public) {
            // Public modules can be viewed by anyone
            $hasAccess = true; 
        } else {
            // Private modules: Check if the student's email is on the access list (even if they dropped)
            $hasAccess = MaterialAccess::where('material_id', $material->id)
                ->where('email', $user->email)
                ->exists();
        }

        // If they aren't on the list at all and it's private, block them
        if (!$hasAccess) {
            abort(403, 'You do not have permission to view this module.');
        }

        $lessons = DB::table('lessons')->where('material_id', $id)->get();
        $exams = DB::table('exams')->where('material_id', $id)->get();

        return view('dashboard.partials.student.materials-show', compact('material', 'lessons', 'exams', 'isEnrolled'));
    }

    public function markAsCompleted($id)
    {
        $enrollment = Enrollment::where('material_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($enrollment->status !== 'completed') {
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        // Generate a cryptographically signed URL
        $signedUrl = URL::signedRoute('student.materials.achieved', ['enrollment_id' => $enrollment->id]);

        return response()->json([
            'success' => true,
            'redirect_url' => $signedUrl
        ]);
    }

    // 2. UPDATE THIS METHOD
    public function downloadCertificate($enrollment_id)
    {
        // Find the specific enrollment directly
        $enrollment = Enrollment::with(['material.instructor', 'user'])
            ->findOrFail($enrollment_id);

        if ($enrollment->status !== 'completed') {
            abort(403, 'This certificate is not valid or incomplete.');
        }

        // Generate the encrypted URL for the QR Code
        $signedUrl = \Illuminate\Support\Facades\URL::signedRoute('student.materials.achieved', ['enrollment_id' => $enrollment->id]);
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate($signedUrl));

        $data = [
            'studentName' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
            'courseName' => $enrollment->material->title,
            'instructorName' => $enrollment->material->instructor->first_name . ' ' . $enrollment->material->instructor->last_name,
            'date' => $enrollment->completed_at ? $enrollment->completed_at->format('F j, Y') : $enrollment->updated_at->format('F j, Y'),
            'certificateId' => 'CERT-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
            'qrCode' => $qrCode
        ];

        $pdf = Pdf::loadView('dashboard.partials.student.certificate-template', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Certificate_of_Completion_' . str_replace(' ', '_', $data['courseName']) . '.pdf');
    }


    public function completionPage($enrollment_id)
    {
        // Find the specific enrollment directly
        $enrollment = Enrollment::with(['material.instructor', 'user'])
            ->findOrFail($enrollment_id);

        // Ensure it is actually completed
        if ($enrollment->status !== 'completed') {
            abort(403, 'This certificate is not valid or incomplete.');
        }

        return view('dashboard.partials.student.certificate-achieved', compact('enrollment'));
    }

    public function myCertificates()
    {
        // Fetch only the completed enrollments for the logged-in student
        $completedEnrollments = Enrollment::with(['material.instructor'])
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->latest('updated_at') // Sorts by most recently completed
            ->get();

        // Pass the data to the certificates blade file
        return view('dashboard.partials.student.certificates', compact('completedEnrollments'));
    }
    public function previewCertificateTemplate($enrollment_id)
    {
        // 1. Fetch the enrollment with necessary relationships
        $enrollment = Enrollment::with(['material.instructor', 'user'])
            ->findOrFail($enrollment_id);

        // 2. Security: Ensure the user owns this certificate (unless admin)
        if (Auth::id() !== $enrollment->user_id && !in_array(Auth::user()->role, ['teacher', 'admin', 'superadmin'])) {
            abort(403, 'Unauthorized access to this certificate preview.');
        }

        // 3. Prepare the data (same logic as downloadCertificate)
        $signedUrl = URL::signedRoute('student.materials.achieved', ['enrollment_id' => $enrollment->id]);
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->generate($signedUrl));

        $data = [
            'studentName' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
            'courseName' => $enrollment->material->title,
            'instructorName' => $enrollment->material->instructor->first_name . ' ' . $enrollment->material->instructor->last_name,
            'date' => $enrollment->completed_at ? $enrollment->completed_at->format('F j, Y') : $enrollment->updated_at->format('F j, Y'),
            'certificateId' => 'CERT-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
            'qrCode' => $qrCode
        ];

        // 4. Return the view directly to the browser
        return view('dashboard.partials.student.certificate-template', $data);
    }

}

