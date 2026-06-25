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
use Vinkla\Hashids\Facades\Hashids; // <-- Import Hashids


class StudentEnrollmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Get Active Enrollments (In Progress, Completed, Failed) -> ONLY published modules
        $activeEnrollments = Enrollment::with(['material.instructor', 'material.tags'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'dropped')
            ->whereHas('material', function($q) {
                $q->where('status', 'published');
            })
            ->latest()
            ->get();

        // 2. Get Dropped Materials (ONLY published modules)
        $droppedAccesses = MaterialAccess::with(['material.instructor', 'material.tags'])
            ->where('email', $user->email)
            ->where('status', 'dropped')
            ->whereHas('material', function($q) {
                $q->where('status', 'published');
            })
            ->latest()
            ->get();

        return view('dashboard.partials.student.enrolled', compact('activeEnrollments', 'droppedAccesses'));
    }
   
    public function acceptInvitation(Request $request, $hashid, $email)
{
    // 1. Decode the hashid
    $decoded = \Vinkla\Hashids\Facades\Hashids::decode($hashid);
    if (empty($decoded)) {
        abort(404, 'Invalid or corrupted invitation link.');
    }
    
    // Find the material
    $material = \App\Models\Material::findOrFail($decoded[0]);

    // 🚨 NEW CHECK: Prevent accepting invites for draft/pending modules
    if ($material->status !== 'published') {
        abort(403, 'This module is invalid. Please Try Again Later');
    }

    // 2. Security check: Ensure the logged-in user is the one invited
    if (Auth::user()->email !== $email) {
        abort(403, 'This invitation was sent to a different email address.');
    }

    // 3. Process Access Record
    $access = \App\Models\MaterialAccess::where('material_id', $material->id)
        ->where('email', $email)
        ->firstOrFail();

    $enrollment = \App\Models\Enrollment::where('material_id', $material->id)->where('user_id', Auth::id())->first();

    if ($access->status === 'dropped' || ($enrollment && $enrollment->status === 'dropped')) {
        $droppedByStudent = ($access->dropped_by_type === 'student') || ($enrollment && $enrollment->dropped_by_type === 'student');

        if ($droppedByStudent) {
            \App\Models\Enrollment::reactivateAndResetForStudent(Auth::user(), $material);
            return redirect()->route('student.materials.show', $hashid)
                ->with('success', 'Successfully rejoined the module!');
        } else {
            abort(403, 'You were removed from this module. Please contact your instructor to be re-enrolled.');
        }
    }

    // 4. Create Enrollment record (tracking progress)
    \App\Models\Enrollment::firstOrCreate([
        'material_id' => $material->id,
        'user_id' => auth()->id(),
    ], [
        'status' => 'in_progress'
    ]);

    // 5. Update the material access status and assign the student's ID
    $access->update([
        'status' => 'enrolled',
        'student_id' => auth()->id()
    ]);

    // 6. Redirect back to the show page using the hashid!
    return redirect()->route('student.materials.show', $hashid)
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
            ->where('status', 'published') // 🚨 NEW CHECK: Block draft/pending modules
            ->first();

        // If it's a public module, invalid code, or NOT published, this triggers the error
        if (!$material) {
            return response()->json(['success' => false, 'message' => 'Invalid access code.']);
        }

        // 2. CHECK EXPIRED CODE (From our previous fix)
        if ($material->access_code_expires_at && now()->greaterThan($material->access_code_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'This access code has expired. Please ask your teacher for a new one.'
            ]);
        }
        
        // 3. Check if already enrolled (Rest of your existing code remains exactly the same below here)
        $enrollment = Enrollment::where('material_id', $material->id)
            ->where('user_id', Auth::id())
            ->first();

        $access = MaterialAccess::where('material_id', $material->id)
            ->where('email', Auth::user()->email)
            ->first();

        if ($enrollment || ($access && $access->status === 'dropped')) {
            $isDropped = ($enrollment && $enrollment->status === 'dropped') || ($access && $access->status === 'dropped');
            $droppedByStudent = ($enrollment && $enrollment->dropped_by_type === 'student') || ($access && $access->dropped_by_type === 'student');

            if ($isDropped) {
                if ($droppedByStudent) {
                    \App\Models\Enrollment::reactivateAndResetForStudent(Auth::user(), $material);
                    return response()->json([
                        'success' => true,
                        'redirect_url' => route('student.materials.show', $material->hashid)
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'You were removed from this module. Please contact your instructor to be re-enrolled.'
                    ]);
                }
            }
            return response()->json([
                'success' => true,
                'redirect_url' => route('student.materials.show', $material->hashid)
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
            'redirect_url' => route('student.materials.show', $material->hashid)
        ]);
    }

    public function show($hashid)
    {
        $decoded = Hashids::decode($hashid);
        // If the hash is invalid or tampered with, throw a 404
        if (empty($decoded)) {
            abort(404, 'Invalid material link.');
        }
        $id = $decoded[0];

        $material = Material::findOrFail($id);
        $user = Auth::user();

        // 🚨 NEW CHECK: Prevent accessing draft/pending modules for students
        if ($user->role === 'student' && $material->status !== 'published') {
            abort(403, 'This module is temporarily unavailable or in draft mode.');
        }

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

        $hashid = Hashids::encode($enrollment->id);
        $url = route('student.materials.achieved', ['hashid' => $hashid]);

        return response()->json([
            'success' => true,
            'redirect_url' => $url
        ]);
    }

    // 2. UPDATE THIS METHOD
    public function downloadCertificate($hashid)
    {

        $decoded = Hashids::decode($hashid);
        if (empty($decoded))
            abort(404, 'Invalid certificate link.');
        $enrollment_id = $decoded[0];
        // Find the specific enrollment directly
        $enrollment = Enrollment::with(['material.instructor', 'user'])
            ->findOrFail($enrollment_id);

        // 🚨 NEW CHECK: Prevent students from accessing draft/pending modules
        if (Auth::user()->role === 'student' && $enrollment->material->status !== 'published') {
            abort(403, 'This module is temporarily unavailable or in draft mode.');
        }

        if ($enrollment->status !== 'completed') {
            abort(403, 'This certificate is not valid or incomplete.');
        }

        // Generate the encrypted URL for the QR Code
        // Generate the Hashid URL for the QR Code
        $url = route('student.materials.achieved', ['hashid' => $hashid]);
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->generate($url));

        $duration = '';
        if ($enrollment->calculated_time > 0) {
            $totalSeconds = $enrollment->calculated_time;
            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);
            $seconds = $totalSeconds % 60;

            $durationParts = [];
            if ($hours > 0) {
                $durationParts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
            }
            if ($minutes > 0) {
                $durationParts[] = $minutes . ' min' . ($minutes > 1 ? 's' : '');
            }
            if ($seconds > 0 || empty($durationParts)) {
                $durationParts[] = $seconds . ' sec' . ($seconds > 1 ? 's' : '');
            }

            $duration = implode(' ', $durationParts);
        }

        $data = [
            'studentName' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
            'courseName' => $enrollment->material->title,
            'instructorName' => $enrollment->material->instructor->first_name . ' ' . $enrollment->material->instructor->last_name,
            'date' => $enrollment->completed_at ? $enrollment->completed_at->format('F j, Y') : $enrollment->updated_at->format('F j, Y'),
            'certificateId' => 'CERT-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
            'qrCode' => $qrCode,
            'duration' => $duration,
            'activeTemplate' => \App\Models\CertificateTemplate::getForMaterial($enrollment->material)
        ];

        $pdf = Pdf::loadView('dashboard.partials.student.certificate-template', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Certificate_of_Completion_' . str_replace(' ', '_', $data['courseName']) . '.pdf');
    }



    public function completionPage($hashid)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($hashid);
        if (empty($decoded)) {
            abort(404, 'Invalid certificate link.');
        }
        $enrollment_id = $decoded[0];

        $enrollment = Enrollment::with(['material.instructor', 'user'])
            ->findOrFail($enrollment_id);

        // 🚨 NEW CHECK: Prevent students from accessing draft/pending modules
        if (Auth::user()->role === 'student' && $enrollment->material->status !== 'published') {
            abort(403, 'This module is temporarily unavailable or in draft mode.');
        }

        if ($enrollment->status !== 'completed') {
            abort(403, 'This certificate is not valid or incomplete.');
        }

        $activeTemplate = \App\Models\CertificateTemplate::getForMaterial($enrollment->material);
        return view('dashboard.partials.student.certificate-achieved', compact('enrollment', 'hashid', 'activeTemplate'));
    }


    public function myCertificates()
    {
        // Fetch only the completed enrollments for the logged-in student
        $completedEnrollments = Enrollment::with(['material.instructor'])
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->latest('updated_at') // Sorts by most recently completed
            ->get()
            ->map(function ($enrollment) {
                // Dynamically add the hashid to each enrollment object
                $enrollment->hashid = Hashids::encode($enrollment->id);
                return $enrollment;
            });

        // Pass the data to the certificates blade file
        return view('dashboard.partials.student.certificates', compact('completedEnrollments'));
    }
    public function previewCertificateTemplate($hashid)
    {
        $decoded = Hashids::decode($hashid);
        if (empty($decoded))
            abort(404, 'Invalid certificate link.');
        $enrollment_id = $decoded[0];
        // 1. Fetch the enrollment with necessary relationships
        $enrollment = Enrollment::with(['material.instructor', 'user'])
            ->findOrFail($enrollment_id);

        // 🚨 NEW CHECK: Prevent students from accessing draft/pending modules
        if (Auth::user()->role === 'student' && $enrollment->material->status !== 'published') {
            abort(403, 'This module is temporarily unavailable or in draft mode.');
        }

        // 2. Security: Ensure the user owns this certificate (unless admin)
        if (Auth::id() !== $enrollment->user_id && !in_array(Auth::user()->role, ['teacher', 'admin', 'superadmin'])) {
            abort(403, 'Unauthorized access to this certificate preview.');
        }

        // 3. Prepare the data (same logic as downloadCertificate)
        $url = route('student.materials.achieved', ['hashid' => $hashid]);
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->generate($url));

        $duration = '';
        if ($enrollment->calculated_time > 0) {
            $totalSeconds = $enrollment->calculated_time;
            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);
            $seconds = $totalSeconds % 60;

            $durationParts = [];
            if ($hours > 0) {
                $durationParts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
            }
            if ($minutes > 0) {
                $durationParts[] = $minutes . ' min' . ($minutes > 1 ? 's' : '');
            }
            if ($seconds > 0 || empty($durationParts)) {
                $durationParts[] = $seconds . ' sec' . ($seconds > 1 ? 's' : '');
            }

            $duration = implode(' ', $durationParts);
        }

        $data = [
            'studentName' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
            'courseName' => $enrollment->material->title,
            'instructorName' => $enrollment->material->instructor->first_name . ' ' . $enrollment->material->instructor->last_name,
            'date' => $enrollment->completed_at ? $enrollment->completed_at->format('F j, Y') : $enrollment->updated_at->format('F j, Y'),
            'certificateId' => 'CERT-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
            'qrCode' => $qrCode,
            'duration' => $duration,
            'activeTemplate' => \App\Models\CertificateTemplate::getForMaterial($enrollment->material)
        ];

        // 4. Return the view directly to the browser
        return view('dashboard.partials.student.certificate-template', $data);
    }

}

