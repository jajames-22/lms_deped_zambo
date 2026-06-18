<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\Material;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Imports\LessonImport;
use App\Models\Tag;
use App\Imports\EmailMaterialsAccessImport;
use App\Exports\MaterialTemplateExport;
use Illuminate\Support\Facades\Mail;
use App\Mail\MaterialInvitationMail;
use App\Models\MaterialAccess;
use App\Models\LessonContent;
use App\Models\ExamAnswer;
use App\Models\QuizAnswer;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use Exception;


class MaterialsController extends Controller
{
    // ==========================================
    // --- ADMIN FUNCTIONS ---
    // ==========================================

    /**
     * Admin Index: Fetches ALL materials across the entire platform.
     */
    public function adminIndex()
    {
        $user = Auth::user();

        $materials = Material::with('instructor')
            ->withCount([
                'lessons' => function ($query) {
                    $query->where('section_type', 'lesson');
                }
            ])
            ->where(function ($query) use ($user) {
                // 1. Admins can see ALL pending and published materials
                $query->whereIn('status', ['pending', 'published'])

                    // 2. OR they can see drafts ONLY if they are the instructor
                    ->orWhere(function ($subQuery) use ($user) {
                    $subQuery->where('status', 'draft')
                        ->where('instructor_id', $user->id);
                });
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dashboard.partials.admin.materials', compact('materials'));
    }


    public function evaluateMaterial(Request $request, $hashid)
    {
        // 1. Security Check: Allow Admins and CID to access the evaluation mode
        if (!in_array(auth()->user()->role, ['admin', 'cid'])) {
            abort(403, 'Unauthorized access. Only Admins and CID can evaluate materials.');
        }
        $decoded = Hashids::decode($hashid);
        if (empty($decoded))
            abort(404, 'Invalid link.');

        $material = Material::findOrFail($decoded[0]);
        $user = auth()->user();

        // 🛑 NEW SECURITY CHECK: Prevent evaluating drafts
        if ($material->status === 'draft' && $material->instructor_id !== $user->id) {
            return response('
            <div class="flex flex-col items-center justify-center h-[60vh] text-center px-4">
                <div class="w-20 h-20 bg-red-50 text-[#a52a2a] rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-file-signature text-3xl"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">Evaluation Locked</h2>
                <p class="text-gray-500 max-w-md text-sm">This module is in <b>Draft</b> mode. You cannot evaluate it until the instructor submits it for review.</p>
            </div>
        ');
        }

        // 2. Eager load all the necessary relationships and sort them
        $material->load([
            'instructor',
            'tags',
            'lessons' => function ($query) {
                $query->orderBy('sort_order', 'asc'); // Sorts the lessons
            },
            'lessons.contents' => function ($query) {
                $query->orderBy('sort_order', 'asc'); // Sorts the contents inside each lesson
            },
            'lessons.contents.options',
            'exams.options'
        ]);

        $criteriaId = $request->query('criteria_id');
        $evaluationCriteria = null;

        if ($criteriaId && \Illuminate\Support\Facades\Storage::exists('criterias.json')) {
            $criteriasArray = json_decode(\Illuminate\Support\Facades\Storage::get('criterias.json'), true);
            $index = array_search($criteriaId, array_column($criteriasArray, 'id'));
            if ($index !== false) {
                $evaluationCriteria = (object) $criteriasArray[$index];
            }
        }

        // 3. Return the specific evaluate view
        return view('dashboard.partials.admin.evaluate-material', compact('material', 'evaluationCriteria'));
    }


    // ==========================================
    // --- TEACHER FUNCTIONS ---
    // ==========================================

    /**
     * Teacher Index: Fetches ONLY the materials belonging to the logged-in teacher.
     */
    public function teacherIndex()
    {
        $materials = Material::with('instructor')
            ->where('instructor_id', Auth::id())
            ->withCount([
                'lessons' => function ($query) {
                    $query->where('section_type', 'lesson');
                }
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dashboard.partials.teacher.materials', compact('materials'));
    }

    /**
     * Legacy/Default Index (Kept for backward compatibility if needed)
     */
    public function index()
    {
        $user = Auth::user();

        // added CID here
        if (in_array($user->role, ['admin', 'cid'])) {
            return $this->adminIndex();
        }

        // If the user is a teacher, load the Teacher Index
        if ($user->role === 'teacher') {
            return $this->teacherIndex();
        }

        // Failsafe catch
        abort(403, 'Unauthorized access.');
    }

    // ==========================================
    // --- GENERAL FUNCTIONS (Shared by Admin & Teacher) ---
    // ==========================================

    public function create()
    {
        $materialId = DB::table('materials')->insertGetId([
            'title' => 'Untitled Material',
            'description' => '',
            'instructor_id' => Auth::id(),
            'status' => 'draft',
            'access_code' => strtoupper(Str::random(6)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $material = DB::table('materials')->where('id', $materialId)->first();
        $lessons = [];
        $isNew = true;

        // UPDATED: Pointing to shared folder
        return view('dashboard.partials.shared.materials-create', compact('material', 'lessons', 'isNew'));
    }

    public function edit($id)
    {
        $material = DB::table('materials')->where('id', $id)->first();

        if (!$material)
            abort(404, 'Material not found');

        // SECURITY LOCK: Cannot load editor if not draft
        if ($material->status !== 'draft') {
            abort(403, 'Content can only be edited when the module is in Draft status. Please revert to draft first.');
        }

        if (!$material)
            abort(404, 'Material not found');

        // 1. Fetch Lessons (Ordered by sort_order)
        $lessons = DB::table('lessons')
            ->where('material_id', $id)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($lesson) {
                // FIX: Changed 'quizzes' to 'lesson_contents'
                $lesson->questions = DB::table('lesson_contents')
                    ->where('lesson_id', $lesson->id)
                    ->orderBy('sort_order', 'asc')
                    ->get()
                    ->map(function ($quiz) {
                    $quiz->options = DB::table('quiz_options')
                        ->where('quiz_id', $quiz->id)
                        ->get();
                    return $quiz;
                });
                return $lesson;
            });

        // 2. Fetch Exams (Ordered by sort_order)
        $examQuestions = DB::table('exams')
            ->where('material_id', $id)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($exam) {
                $exam->options = DB::table('exam_options')
                    ->where('exam_id', $exam->id)
                    ->get();
                return $exam;
            });

        // 3. Package the exam questions into a section block for the frontend builder
        if ($examQuestions->isNotEmpty()) {
            $examSection = (object) [
                'id' => 'exam-section-' . $id,
                'title' => 'Final Exam',
                'section_type' => 'exam',
                'questions' => $examQuestions
            ];

            // Append the exam section to the end of the lessons collection
            $lessons->push($examSection);
        }

        $isNew = false;

        // UPDATED: Pointing to shared folder
        return view('dashboard.partials.shared.materials-create', compact('material', 'lessons', 'isNew'));
    }


    public function manage($id)
    {
        $material = Material::findOrFail($id);

        $user = auth()->user();

        if (in_array($user->role, ['admin', 'cid']) && $material->status === 'draft' && $material->instructor_id !== $user->id) {
            return response('
            <div class="flex flex-col items-center justify-center h-[60vh] text-center px-4">
                <div class="w-20 h-20 bg-red-50 text-[#a52a2a] rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-lock text-3xl"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">Module Unavailable</h2>
                <p class="text-gray-500 max-w-md text-sm">This module is currently in <b>Draft</b> mode. It may have been reverted for revisions and cannot be accessed until the instructor submits it again.</p>
            </div>
        ');
        }

        $material->lessons_count = DB::table('lessons')
            ->where('material_id', $id)
            ->where('section_type', 'lesson')
            ->count();

        $examIds = DB::table('exams')->where('material_id', $id)->pluck('id');
        $material->items_count = DB::table('exams')->whereIn('id', $examIds)->count();

        // 1. Get whitelisted students
        $whitelistedStudents = \App\Models\MaterialAccess::with('student')
            ->where('material_id', $material->id)
            ->latest()
            ->get();

        // 2. Fetch all enrollments for this material efficiently
        $enrollments = \App\Models\Enrollment::where('material_id', $material->id)
            ->get()
            ->keyBy('user_id');

        // ============================================================
        // NEW: Calculate dynamic scores and Pass Rate based on passing grade
        // ============================================================
        $lessonIds = DB::table('lessons')->where('material_id', $material->id)->pluck('id');
        $quizIds = DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->where('type', '!=', 'content')->pluck('id');

        $hasQuizzes = $quizIds->isNotEmpty();
        $hasExams = $examIds->isNotEmpty();

        $examWeight = $material->exam_weight ?? 60;
        $passingScore = $material->passing_percentage ?? 80;

        if ($hasExams && !$hasQuizzes) {
            $examWeight = 100;
        } elseif (!$hasExams && $hasQuizzes) {
            $examWeight = 0;
        } elseif (!$hasExams && !$hasQuizzes) {
            $examWeight = 0;
        }
        $quizWeight = 100 - $examWeight;

        // Optimized Answer Fetching (Prevents N+1 Query Problem)
        $userIds = $enrollments->keys();

        $quizAnswers = DB::table('quiz_answers')
            ->whereIn('lesson_content_id', $quizIds)
            ->whereIn('user_id', $userIds)
            ->where('is_correct', 1)
            ->select('user_id', DB::raw('count(*) as correct_count'))
            ->groupBy('user_id')
            ->pluck('correct_count', 'user_id');

        $examAnswers = DB::table('exam_answers')
            ->whereIn('exam_id', $examIds)
            ->whereIn('user_id', $userIds)
            ->where('is_correct', 1)
            ->select('user_id', DB::raw('count(*) as correct_count'))
            ->groupBy('user_id')
            ->pluck('correct_count', 'user_id');

        $quizTotal = $quizIds->count();
        $examTotal = $examIds->count();

        $passCount = 0;
        $evaluatedCount = 0;
        
        // Build unified timeline to calculate accurate progress
        $timeline = collect();
        $lessons = DB::table('lessons')->where('material_id', $material->id)->get();
        foreach ($lessons as $lesson) {
            $count = DB::table('lesson_contents')->where('lesson_id', $lesson->id)->count();
            $timeline->push((object)[
                'items_count' => $count,
                'timestamp' => $lesson->created_at ? \Carbon\Carbon::parse($lesson->created_at)->timestamp : 0
            ]);
        }
        if ($hasExams) {
            $examCount = DB::table('exams')->where('material_id', $material->id)->count();
            $firstExam = DB::table('exams')->where('material_id', $material->id)->first();
            $timeline->push((object)[
                'items_count' => $examCount,
                'timestamp' => $firstExam && $firstExam->created_at ? \Carbon\Carbon::parse($firstExam->created_at)->timestamp : 0
            ]);
        }
        $timeline = $timeline->sortBy('timestamp')->values();
        $totalContents = $timeline->sum('items_count');

        foreach ($enrollments as $enrollment) {
            $quizCorrect = $quizAnswers->get($enrollment->user_id, 0);
            $qScore = $quizTotal > 0 ? ($quizCorrect / $quizTotal) * 100 : 100;

            $examCorrect = $examAnswers->get($enrollment->user_id, 0);
            $eScore = $examTotal > 0 ? ($examCorrect / $examTotal) * 100 : 100;

            if (!$hasExams && !$hasQuizzes) {
                $totalScore = 100;
            } else {
                $totalScore = ($qScore * ($quizWeight / 100)) + ($eScore * ($examWeight / 100));
            }

            // Assign score so the Blade UI table displays their actual current score!
            $enrollment->score = round($totalScore);

            // Calculate Pass Rate only for students who have actually finished
            if (in_array($enrollment->status, ['completed', 'failed'])) {
                $evaluatedCount++;
                if (round($totalScore, 2) >= $passingScore) {
                    $passCount++;
                }
            }
            // Calculate true progress
            // Calculate true progress
            if (in_array($enrollment->status, ['completed', 'read']) || !is_null($enrollment->completed_at)) {
                $enrollment->progress_percentage = 100;
            } else {
                $progressData = is_string($enrollment->progress_data) ? json_decode($enrollment->progress_data) : $enrollment->progress_data;
                $highestUnlocked = isset($progressData->highest_unlocked) ? (int) $progressData->highest_unlocked : 0;
                $currentContent = isset($progressData->content) ? (int) $progressData->content : 0;
                $currentLesson = isset($progressData->lesson) ? (int) $progressData->lesson : 0;

                $contentsPassed = 0;
                for ($i = 0; $i < $highestUnlocked; $i++) {
                    if (isset($timeline[$i])) {
                        $contentsPassed += $timeline[$i]->items_count;
                    }
                }

                if ($currentLesson === $highestUnlocked) {
                    $contentsPassed += $currentContent;
                }

                $enrollment->progress_percentage = $totalContents > 0 ? min(100, round(($contentsPassed / $totalContents) * 100)) : 0;
            }
        }

        $passRate = $evaluatedCount > 0 ? round(($passCount / $evaluatedCount) * 100) : null;
        // ============================================================

        // 3. Attach the enrollment data to the access object
        foreach ($whitelistedStudents as $access) {
            $access->current_enrollment = null;
            if ($access->student && $enrollments->has($access->student->id)) {
                $access->current_enrollment = $enrollments->get($access->student->id);
            }
        }

        $availableCriterias = [];
        if (Storage::exists('criterias.json')) {
            $criteriasArray = json_decode(Storage::get('criterias.json'), true);
            if (is_array($criteriasArray)) {
                // Convert arrays to objects so Blade can read them as $criteria->title
                $availableCriterias = collect($criteriasArray)->map(fn($item) => (object) $item);
            }
        }

        // Pass the new variable to the view
        return view('dashboard.partials.shared.materials-manage', compact('material', 'whitelistedStudents', 'availableCriterias', 'passRate'));
    }
    

    public function addAccess(Request $request, $id)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        if (\App\Models\MaterialAccess::where('material_id', $id)->where('email', $email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email is already in the access list.']);
        }

        \App\Models\MaterialAccess::create([
            'material_id' => $id,
            'email' => $email,
            'status' => 'pending'
        ]);

        $material = \App\Models\Material::find($id);
        $student = \App\Models\User::where('email', $email)->first();

        if ($student && $material) {
            // UPDATED: Grab full name instead of just last name
            $instructor = auth()->user();
            $instructorName = trim(($instructor->first_name ?? '') . ' ' . ($instructor->last_name ?? '')) ?: 'An Instructor';

            $student->notify(new \App\Notifications\LmsAlertNotification(
                'New Module Access',
                $instructorName . ' granted you access to a private module: "' . $material->title . '".',
                route('dashboard.materials.show', $material->hashid),
                'fas fa-unlock-alt',
                'text-blue-600'
            ));
        }

        return response()->json(['success' => true, 'message' => 'Student added successfully!']);
    }
    public function removeBulkAccess(Request $request)
    {
        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No access records selected.']);
        }

        DB::beginTransaction();
        try {
            $accesses = MaterialAccess::whereIn('id', $ids)->get();

            if ($accesses->isEmpty()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid access records.']);
            }

            // Group by material in case multiple materials are involved
            foreach ($accesses->groupBy('material_id') as $materialId => $group) {
                $emails = $group->pluck('email')->toArray();
                $students = User::whereIn('email', $emails)->get();
                $studentIds = $students->pluck('id')->toArray();

                if (!empty($studentIds)) {
                    $examIds = DB::table('exams')->where('material_id', $materialId)->pluck('id');
                    $quizIds = DB::table('lesson_contents')
                        ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
                        ->where('lessons.material_id', $materialId)
                        ->pluck('lesson_contents.id');

                    QuizAnswer::whereIn('user_id', $studentIds)->whereIn('lesson_content_id', $quizIds)->delete();
                    ExamAnswer::whereIn('user_id', $studentIds)->whereIn('exam_id', $examIds)->delete();

                    Enrollment::where('material_id', $materialId)
                        ->whereIn('user_id', $studentIds)
                        ->delete();
                }
            }

            MaterialAccess::whereIn('id', $ids)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => count($ids) . ' students removed.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error removing students.']);
        }
    }


    public function removeAccess($id)
    {
        DB::beginTransaction();
        try {
            // 1. Find the specific access record being revoked
            $access = MaterialAccess::findOrFail($id);

            // 2. Look up if a user exists with this email
            $student = User::where('email', $access->email)->first();

            // 3. If they exist, delete their enrollment AND all their submitted answers
            if ($student) {
                // Get all Exam and Quiz IDs for this material
                $examIds = DB::table('exams')->where('material_id', $access->material_id)->pluck('id');
                $quizIds = DB::table('lesson_contents')
                    ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
                    ->where('lessons.material_id', $access->material_id)
                    ->pluck('lesson_contents.id');

                // Delete the student's answers
                QuizAnswer::where('user_id', $student->id)->whereIn('lesson_content_id', $quizIds)->delete();
                ExamAnswer::where('user_id', $student->id)->whereIn('exam_id', $examIds)->delete();

                // Delete their enrollment to kick them out of the module
                Enrollment::where('material_id', $access->material_id)
                    ->where('user_id', $student->id)
                    ->delete();
            }

            // 4. Delete the email from the whitelist/access table entirely
            $access->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Student access, enrollment, and all progress data revoked successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error revoking access: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            // 1. Validate the basic status
            $request->validate([
                'status' => 'required|in:draft,pending,published,revert_requested'
            ]);

            $targetStatus = $request->status;
            $currentUser = auth()->user();

            // ==========================================
            // NEW: Prevent Pending Teachers from Submitting
            // ==========================================
            if ($currentUser->role === 'teacher' && $currentUser->status === 'pending' && in_array($targetStatus, ['pending', 'published'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is currently pending verification. You cannot submit modules for review until an administrator verifies your account.'
                ], 403);
            }

            // Fetch the material BEFORE updating so we can check ownership and titles
            $material =

                // Fetch the material BEFORE updating so we can check ownership and titles
                $material = \App\Models\Material::with('instructor')->findOrFail($id);

            // ==========================================
            // NEW: Handle Unpublish Request (Stays Published)
            // ==========================================
            if ($targetStatus === 'revert_requested') {
                \Illuminate\Support\Facades\DB::table('materials')->where('id', $id)->update([
                    // We DO NOT change 'status' here so it remains 'published'
                    'revert_reason' => $request->revert_reason,
                    'updated_at' => now()
                ]);

                // Notify Admins
                $approvers = \App\Models\User::whereIn('role', ['admin', 'cid'])->get();
                $teacherName = $currentUser->first_name . ' ' . $currentUser->last_name;
                foreach ($approvers as $approver) {
                    $approver->notify(new \App\Notifications\LmsAlertNotification(
                        'Unpublish Request',
                        "{$teacherName} requested to unpublish the module \"{$material->title}\".",
                        route('dashboard.materials.manage', $material->id),
                        'fas fa-exclamation-circle',
                        'text-amber-600'
                    ));
                }
                return response()->json(['success' => true, 'message' => 'Unpublish request sent to administrators.']);
            }

            // ==========================================
            // NEW: Handle Admin Review of Unpublish Request
            // ==========================================
            // Check if there is an active request via the revert_reason column
            // ==========================================
            // NEW: Handle Admin Review of Unpublish Request
            // ==========================================
            if (!empty($material->revert_reason) && in_array($currentUser->role, ['admin', 'cid'])) {
                if ($targetStatus === 'draft') {

                    // 🚨 NEW: Clear progress if requested!
                    if ($request->data_option === 'clear') {
                        $this->clearStudentProgressAndResults($material);
                    }

                    // Approved: Move to draft and clear reason
                    \Illuminate\Support\Facades\DB::table('materials')->where('id', $id)->update([
                        'status' => 'draft',
                        'revert_reason' => null,
                        'updated_at' => now()
                    ]);
                    // ... (rest of the code remains the same)
                    if ($material->instructor) {
                        $material->instructor->notify(new \App\Notifications\LmsAlertNotification(
                            'Unpublish Request Approved',
                            'Your request to unpublish "' . $material->title . '" was approved. It is now in Draft mode.',
                            route('dashboard.materials.manage', $material->id),
                            'fas fa-check-circle',
                            'text-green-600'
                        ));
                    }
                    return response()->json(['success' => true, 'message' => 'Request approved. Module is now in Draft.']);
                } elseif ($targetStatus === 'published') {
                    // Rejected: Keep published and clear reason
                    \Illuminate\Support\Facades\DB::table('materials')->where('id', $id)->update([
                        'status' => 'published',
                        'revert_reason' => null,
                        'updated_at' => now()
                    ]);
                    if ($material->instructor) {
                        $material->instructor->notify(new \App\Notifications\LmsAlertNotification(
                            'Unpublish Request Declined',
                            'Your request to unpublish "' . $material->title . '" was declined by an admin.',
                            route('dashboard.materials.manage', $material->id),
                            'fas fa-times-circle',
                            'text-red-600'
                        ));
                    }
                    return response()->json(['success' => true, 'message' => 'Request declined. Module remains Published.']);
                }
            }

            // Force teachers to go through 'pending' instead of direct publish
            if ($currentUser->role === 'teacher' && $targetStatus === 'published') {
                $targetStatus = 'pending';
            }

            // Force teachers to go through 'pending' instead of direct publish
            if ($currentUser->role === 'teacher' && $targetStatus === 'published') {
                $targetStatus = 'pending';
            }
            // 🚨 NEW: Catch any other scenario where a published or pending material is forced to draft
            if ($targetStatus === 'draft' && in_array($material->status, ['published', 'pending'])) {
                if ($request->data_option === 'clear') {
                    $this->clearStudentProgressAndResults($material);
                }
            }

            // 2. Prepare the database update array for normal status toggles
            $updateData = [
                'status' => $targetStatus,
                'updated_at' => now()
            ];

            // FIX: Catch the admin's force revert reason and put it in the dedicated column
            if ($request->has('revert_reason')) {
                $updateData['revert_reason'] = $request->revert_reason;
            } elseif (in_array($targetStatus, ['pending', 'published'])) {
                $updateData['revert_reason'] = null;
            }

            // 3. If the Admin/CID is evaluating, capture the Rubric Breakdown & Remarks
            if ($request->has('evaluation_details')) {
                $updateData['admin_remarks'] = $request->admin_remarks; // Stays safe for Evaluations
                $updateData['evaluation_json'] = json_encode([
                    'score_percentage' => $request->score_percentage,
                    'details' => $request->evaluation_details
                ]);
            }

            \Illuminate\Support\Facades\DB::table('materials')
                ->where('id', $id)
                ->update($updateData);

            // 4. --- NOTIFICATION LOGIC ---
            // SCENARIO A: Teacher submits a material for approval
            if ($currentUser->role === 'teacher' && $targetStatus === 'pending') {
                $approvers = \App\Models\User::whereIn('role', ['admin', 'cid'])->get();
                $teacherName = $currentUser->first_name . ' ' . $currentUser->last_name;

                foreach ($approvers as $approver) {
                    $approver->notify(new \App\Notifications\LmsAlertNotification(
                        'Material Submitted for Review',
                        "{$teacherName} has submitted the module \"{$material->title}\" for approval.",
                        route('dashboard.materials.manage', $material->id),
                        'fas fa-file-export',
                        'text-blue-600'
                    ));
                }
            }
            // SCENARIO B: Admin/CID Evaluates or Reverts a material (Notify the Teacher)
            elseif ($currentUser->id !== $material->instructor_id && $material->instructor) {
                $notifTitle = '';
                $notifMessage = '';
                $notifIcon = 'fas fa-info-circle';
                $notifColor = 'text-blue-600';

                $evaluatorTitle = $currentUser->role === 'cid' ? 'CID Personnel' : 'An Admin';

                if ($targetStatus === 'published') {
                    $notifTitle = 'Module Approved!';
                    $notifMessage = 'Congratulations! Your module "' . $material->title . '" has been evaluated and published by ' . $evaluatorTitle . '.';
                    $notifIcon = 'fas fa-check-circle';
                    $notifColor = 'text-green-600';
                } elseif ($targetStatus === 'draft' && $request->has('evaluation_details')) {
                    $notifTitle = 'Module Revision Required';
                    $notifMessage = 'Your module "' . $material->title . '" was returned to Draft by ' . $evaluatorTitle . '. Please review the evaluation remarks.';
                    $notifIcon = 'fas fa-undo';
                    $notifColor = 'text-red-600';
                } elseif ($targetStatus === 'draft') {
                    // This handles the manual "Force Revert" button
                    $notifTitle = 'Module Reverted to Draft';
                    $notifMessage = $evaluatorTitle . ' reverted your module "' . $material->title . '" to Draft mode.';

                    // FIX: Read from the correct request key for the notification
                    if ($request->has('revert_reason') && !empty($request->revert_reason)) {
                        $notifMessage .= ' Reason: "' . $request->revert_reason . '"';
                    }

                    $notifIcon = 'fas fa-archive';
                    $notifColor = 'text-amber-600';
                }

                if ($notifTitle !== '') {
                    $material->instructor->notify(new \App\Notifications\LmsAlertNotification(
                        $notifTitle,
                        $notifMessage,
                        route('dashboard.materials.manage', $material->id),
                        $notifIcon,
                        $notifColor
                    ));
                }
            }

            $statusText = 'in Draft Mode';
            if ($targetStatus === 'published')
                $statusText = 'Published and Live';
            if ($targetStatus === 'pending')
                $statusText = 'Submitted for Approval';

            return response()->json([
                'success' => true,
                'new_status' => $targetStatus,
                'message' => 'Module is now ' . $statusText,
                'redirect_url' => in_array($currentUser->role, ['admin', 'cid']) && $request->has('evaluation_details')
                    ? route('dashboard.materials.evaluation-result', $material->hashid)
                    : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the final evaluation report after an Admin grades a material
     */
    public function evaluationResult($hashid)
    {
        $decoded = Hashids::decode($hashid);
        // If the hash is invalid or tampered with, throw a 404
        if (empty($decoded)) {
            abort(404, 'Invalid material link.');
        }
        $material = Material::with('instructor')->findOrFail($decoded[0]);

        // Decode the JSON so the Blade view can loop through it
        $evaluationData = $material->evaluation_json ? json_decode($material->evaluation_json, true) : null;

        return view('dashboard.partials.shared.evaluation-result', compact('material', 'evaluationData'));
    }
    public function importAccess(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:2048',
            'strategy' => 'nullable|in:skip,update',
            'check_only' => 'nullable|boolean',
        ]);

        $checkOnly = filter_var($request->check_only, FILTER_VALIDATE_BOOLEAN);
        $strategy = $request->strategy ?? 'skip';

        try {
            $import = new EmailMaterialsAccessImport($id, $strategy, $checkOnly);
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            if ($checkOnly) {
                $formatted = collect($import->duplicates)->map(fn($dup) => [
                    'email' => $dup['email'],
                    'existing' => ['status' => $dup['status']],
                    'incoming' => ['status' => 'pending / enrolled'],
                ])->values()->toArray();

                return response()->json([
                    'has_duplicates' => count($formatted) > 0,
                    'duplicates' => $formatted,
                ]);
            }

            // Send notifications only for newly added emails
            $material = \App\Models\Material::find($id);
            $instructor = auth()->user();
            $instructorName = trim(($instructor->first_name ?? '') . ' ' . ($instructor->last_name ?? '')) ?: 'An Instructor';

            foreach ($import->newEmails as $email) {
                $student = \App\Models\User::where('email', $email)->first();
                if ($student && $material) {
                    $student->notify(new \App\Notifications\LmsAlertNotification(
                        'New Module Access',
                        $instructorName . ' granted you access to a private module: "' . $material->title . '".',
                        route('dashboard.materials.show', $material->hashid),
                        'fas fa-unlock-alt',
                        'text-blue-600'
                    ));
                }
            }

            $message = "Successfully added {$import->importedCount} email(s).";
            if ($strategy === 'update' && $import->updatedCount > 0) {
                $message .= " Re-activated {$import->updatedCount} existing email(s).";
            }
            if ($import->skippedCount > 0) {
                $message .= " Skipped {$import->skippedCount} duplicate or invalid rows.";
            }

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed. Check if your file has an "email" header. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $id)
    {
        // 🛑 SECURITY LOCK: Cannot save new content if not draft
        $material = DB::table('materials')->where('id', $id)->first();
        if ($material && $material->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify content while module is published or pending. Please revert to draft first.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Update main Material info
            DB::table('materials')->where('id', $id)->update([
                'title' => $request->input('title', 'Untitled Material'),
                'description' => $request->input('description', ''),
                'status' => $request->input('status', 'draft'),
                'draft_json' => null,
                'updated_at' => now()
            ]);

            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('materials_thumbnails', 'public');
                DB::table('materials')->where('id', $id)->update(['thumbnail' => $path]);
            }

            $categories = $request->input('categories');
            if (is_string($categories)) {
                $categories = json_decode($categories, true);
            }
            $categories = $categories ?? [];

            // Tracking arrays for Targeted Cleanup
            $keptLessonIds = [];
            $keptQuizIds = [];
            $keptQuizOptionIds = [];
            $keptExamIds = [];
            $keptExamOptionIds = [];

            $hasProcessedExam = false;

            foreach ($categories as $index => $cat) {
                $sectionType = $cat['section_type'] ?? ($cat['type'] ?? 'lesson');

                if ($sectionType === 'exam') {
                    if ($hasProcessedExam)
                        continue;
                    $hasProcessedExam = true;

                    foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                        $examId = (isset($q['id']) && is_numeric($q['id'])) ? $q['id'] : null;

                        $examData = [
                            'material_id' => $id,
                            'type' => $q['type'] ?? 'mcq',
                            'question_text' => $q['text'] ?? '',
                            'media_url' => $q['media_url'] ?? null,
                            'media_name' => $q['media_name'] ?? null,
                            'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                            'sort_order' => $qIndex + 1,
                            'updated_at' => now(),
                        ];

                        // UPSERT EXAM QUESTION
                        if ($examId && DB::table('exams')->where('id', $examId)->exists()) {
                            DB::table('exams')->where('id', $examId)->update($examData);
                        } else {
                            $examData['created_at'] = now();
                            $examId = DB::table('exams')->insertGetId($examData);
                        }
                        $keptExamIds[] = $examId;

                        foreach ($q['options'] ?? [] as $opt) {
                            $optId = (isset($opt['id']) && is_numeric($opt['id'])) ? $opt['id'] : null;
                            $optData = [
                                'exam_id' => $examId,
                                'option_text' => $opt['text'] ?? '',
                                'is_correct' => $opt['is_correct'] ?? false,
                                'updated_at' => now(),
                            ];

                            // UPSERT EXAM OPTION
                            if ($optId && DB::table('exam_options')->where('id', $optId)->exists()) {
                                DB::table('exam_options')->where('id', $optId)->update($optData);
                            } else {
                                $optData['created_at'] = now();
                                $optId = DB::table('exam_options')->insertGetId($optData);
                            }
                            $keptExamOptionIds[] = $optId;
                        }
                    }
                } else {
                    // IT IS A LESSON
                    $lessonId = (isset($cat['id']) && is_numeric($cat['id'])) ? $cat['id'] : null;

                    $lessonData = [
                        'material_id' => $id,
                        'section_type' => 'lesson',
                        'title' => $cat['title'] ?? 'New Lesson',
                        'time_limit' => $cat['time_limit'] ?? 0,
                        'sort_order' => $index + 1,
                        'updated_at' => now(),
                    ];

                    // UPSERT LESSON
                    if ($lessonId && DB::table('lessons')->where('id', $lessonId)->exists()) {
                        DB::table('lessons')->where('id', $lessonId)->update($lessonData);
                    } else {
                        $lessonData['created_at'] = now();
                        $lessonId = DB::table('lessons')->insertGetId($lessonData);
                    }
                    $keptLessonIds[] = $lessonId;

                    foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                        $quizId = (isset($q['id']) && is_numeric($q['id'])) ? $q['id'] : null;

                        $quizData = [
                            'lesson_id' => $lessonId,
                            'type' => $q['type'] ?? 'mcq',
                            'question_text' => $q['text'] ?? '',
                            'media_url' => $q['media_url'] ?? null,
                            'media_name' => $q['media_name'] ?? null,
                            'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                            'sort_order' => $qIndex + 1,
                            'updated_at' => now(),
                        ];

                        // UPSERT LESSON CONTENT (QUIZ)
                        if ($quizId && DB::table('lesson_contents')->where('id', $quizId)->exists()) {
                            DB::table('lesson_contents')->where('id', $quizId)->update($quizData);
                        } else {
                            $quizData['created_at'] = now();
                            $quizId = DB::table('lesson_contents')->insertGetId($quizData);
                        }
                        $keptQuizIds[] = $quizId;

                        foreach ($q['options'] ?? [] as $opt) {
                            $optId = (isset($opt['id']) && is_numeric($opt['id'])) ? $opt['id'] : null;
                            $optData = [
                                'quiz_id' => $quizId,
                                'option_text' => $opt['text'] ?? '',
                                'is_correct' => $opt['is_correct'] ?? false,
                                'updated_at' => now(),
                            ];

                            // UPSERT QUIZ OPTION
                            if ($optId && DB::table('quiz_options')->where('id', $optId)->exists()) {
                                DB::table('quiz_options')->where('id', $optId)->update($optData);
                            } else {
                                $optData['created_at'] = now();
                                $optId = DB::table('quiz_options')->insertGetId($optData);
                            }
                            $keptQuizOptionIds[] = $optId;
                        }
                    }
                }
            }

            // 2. TARGETED CLEANUP (Delete only what was removed from the UI)

            // Delete removed Exam Options
            DB::table('exam_options')
                ->whereIn('exam_id', function ($query) use ($id) {
                    $query->select('id')->from('exams')->where('material_id', $id);
                })
                ->whereNotIn('id', $keptExamOptionIds)
                ->delete();

            // Delete removed Exam Questions
            DB::table('exams')
                ->where('material_id', $id)
                ->whereNotIn('id', $keptExamIds)
                ->delete();

            // Delete removed Quiz Options
            DB::table('quiz_options')
                ->whereIn('quiz_id', function ($query) use ($id) {
                    $query->select('id')->from('lesson_contents')
                        ->whereIn('lesson_id', function ($sub) use ($id) {
                            $sub->select('id')->from('lessons')->where('material_id', $id);
                        });
                })
                ->whereNotIn('id', $keptQuizOptionIds)
                ->delete();

            // Delete removed Lesson Contents (Quizzes)
            DB::table('lesson_contents')
                ->whereIn('lesson_id', function ($query) use ($id) {
                    $query->select('id')->from('lessons')->where('material_id', $id);
                })
                ->whereNotIn('id', $keptQuizIds)
                ->delete();

            // Delete removed Lessons
            DB::table('lessons')
                ->where('material_id', $id)
                ->whereNotIn('id', $keptLessonIds)
                ->delete();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Material Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function autosave(Request $request, $id)
    {
        try {
            if ($request->has('clear_draft') && $request->clear_draft) {
                DB::table('materials')->where('id', $id)->update(['draft_json' => null]);
                return response()->json(['success' => true]);
            }

            $categories = $request->input('categories');
            if (is_string($categories)) {
                $categories = json_decode($categories, true);
            }

            $draftData = [
                'title' => $request->title,
                'description' => $request->description,
                'categories' => $categories ?? []
            ];

            DB::table('materials')
                ->where('id', $id)
                ->update([
                    'draft_json' => json_encode($draftData),
                    'updated_at' => now()
                ]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'media_file' => 'required|file|max:10240',
        ]);

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');

            // 1. Grab the exact name the user uploaded it as
            $originalName = $file->getClientOriginalName();

            // 2. Store it securely
            $path = $file->store('materials/media', 'public');

            // 3. Return the payload to your frontend Builder
            return response()->json([
                'success' => true,
                'media_url' => asset('storage/' . $path),
                'media_name' => $originalName, // Sending the correct key back
                'media_type' => $file->getClientMimeType()
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
    }



    public function destroy($id)
    {
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $material = \Illuminate\Support\Facades\DB::table('materials')->where('id', $id)->first();
            if ($material && $material->status === 'published') {
                return response()->json(['success' => false, 'message' => 'Cannot delete a published module. Please request to unpublish it to Draft first.'], 403);
            }
            // 1. Delete Exam dependencies (Answers, Options, and the Exams themselves)
            $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $id)->pluck('id');
            if ($examIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('exam_answers')->whereIn('exam_id', $examIds)->delete();
                \Illuminate\Support\Facades\DB::table('exam_options')->whereIn('exam_id', $examIds)->delete();
                \Illuminate\Support\Facades\DB::table('exams')->whereIn('id', $examIds)->delete();
            }

            // 2. Delete Lesson dependencies (Answers, Quizzes, Options, and the Lessons themselves)
            $lessonIds = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $id)->pluck('id');
            if ($lessonIds->isNotEmpty()) {
                $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->pluck('id');

                if ($quizIds->isNotEmpty()) {
                    \Illuminate\Support\Facades\DB::table('quiz_answers')->whereIn('lesson_content_id', $quizIds)->delete();
                    \Illuminate\Support\Facades\DB::table('quiz_options')->whereIn('quiz_id', $quizIds)->delete();
                }

                \Illuminate\Support\Facades\DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->delete();
                \Illuminate\Support\Facades\DB::table('lessons')->whereIn('id', $lessonIds)->delete();
            }

            // 3. Delete Material specific relationships (Tags, Access Lists, Enrollments)
            \Illuminate\Support\Facades\DB::table('material_accesses')->where('material_id', $id)->delete();
            \Illuminate\Support\Facades\DB::table('material_tag')->where('material_id', $id)->delete();
            \Illuminate\Support\Facades\DB::table('enrollments')->where('material_id', $id)->delete();

            // 4. Finally, safely delete the Material itself
            \Illuminate\Support\Facades\DB::table('materials')->where('id', $id)->delete();

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete module: ' . $e->getMessage()], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:materials,id'
        ]);

        $ids = $request->ids;
        $deletedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $material = \Illuminate\Support\Facades\DB::table('materials')->where('id', $id)->first();
                if (!$material) continue;

                // Ensure it's not published
                if ($material->status === 'published') {
                    continue; // Skip published materials
                }

                // Delete Exam dependencies
                $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $id)->pluck('id');
                if ($examIds->isNotEmpty()) {
                    \Illuminate\Support\Facades\DB::table('exam_answers')->whereIn('exam_id', $examIds)->delete();
                    \Illuminate\Support\Facades\DB::table('exam_options')->whereIn('exam_id', $examIds)->delete();
                    \Illuminate\Support\Facades\DB::table('exams')->whereIn('id', $examIds)->delete();
                }

                // Delete Lesson dependencies
                $lessonIds = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $id)->pluck('id');
                if ($lessonIds->isNotEmpty()) {
                    $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->pluck('id');

                    if ($quizIds->isNotEmpty()) {
                        \Illuminate\Support\Facades\DB::table('quiz_answers')->whereIn('lesson_content_id', $quizIds)->delete();
                        \Illuminate\Support\Facades\DB::table('quiz_options')->whereIn('quiz_id', $quizIds)->delete();
                    }

                    \Illuminate\Support\Facades\DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->delete();
                    \Illuminate\Support\Facades\DB::table('lessons')->whereIn('id', $lessonIds)->delete();
                }

                // Delete Material specific relationships
                \Illuminate\Support\Facades\DB::table('material_accesses')->where('material_id', $id)->delete();
                \Illuminate\Support\Facades\DB::table('material_tag')->where('material_id', $id)->delete();
                \Illuminate\Support\Facades\DB::table('enrollments')->where('material_id', $id)->delete();

                // Finally, safely delete the Material itself
                \Illuminate\Support\Facades\DB::table('materials')->where('id', $id)->delete();
                $deletedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();

            if ($deletedCount > 0) {
                return response()->json(['success' => true, 'message' => "$deletedCount module(s) deleted successfully."]);
            } else {
                return response()->json(['success' => false, 'message' => 'No modules were deleted. Published modules cannot be deleted.'], 403);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete modules: ' . $e->getMessage()], 500);
        }
    }


    public function downloadTemplate()
    {
        return Excel::download(new MaterialTemplateExport, 'module_template.xlsx');
    }

    public function importLessons(Request $request, $id)
    {
        $request->validate([
            'module_file' => 'required|mimes:xlsx,csv,xls|max:5120'
        ]);

        DB::beginTransaction();
        try {
            // 1. Update module info and clear drafts
            DB::table('materials')->where('id', $id)->update([
                'title' => $request->title ?? 'Untitled Material',
                'description' => $request->description ?? '',
                'status' => 'draft',
                'draft_json' => null,
                'updated_at' => now()
            ]);

            // 2. Process and save manual UI changes using Upsert (Prevent Duplication)
            $keptLessonIds = [];
            $keptQuizIds = [];
            $keptQuizOptionIds = [];
            $keptExamIds = [];
            $keptExamOptionIds = [];

            if ($request->has('categories')) {
                $categories = $request->input('categories');
                // Ensure it's safely decoded into an array
                if (is_string($categories)) {
                    $categories = json_decode($categories, true);
                }

                $examSortOrder = 1;

                if (is_array($categories)) {
                    foreach ($categories as $index => $cat) {
                        $sectionType = $cat['section_type'] ?? ($cat['type'] ?? 'lesson');

                        if ($sectionType === 'exam') {
                            foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                                $examId = (isset($q['id']) && is_numeric($q['id'])) ? $q['id'] : null;

                                $examData = [
                                    'material_id' => $id,
                                    'type' => $q['type'] ?? 'mcq',
                                    'question_text' => $q['text'] ?? '',
                                    'media_url' => $q['media_url'] ?? null,
                                    'media_name' => $q['media_name'] ?? null,
                                    'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                                    'sort_order' => $examSortOrder++,
                                    'updated_at' => now(),
                                ];

                                // UPSERT EXAM QUESTION
                                if ($examId && DB::table('exams')->where('id', $examId)->exists()) {
                                    DB::table('exams')->where('id', $examId)->update($examData);
                                } else {
                                    $examData['created_at'] = now();
                                    $examId = DB::table('exams')->insertGetId($examData);
                                }
                                $keptExamIds[] = $examId;

                                foreach ($q['options'] ?? [] as $opt) {
                                    $optId = (isset($opt['id']) && is_numeric($opt['id'])) ? $opt['id'] : null;
                                    $optData = [
                                        'exam_id' => $examId,
                                        'option_text' => $opt['text'] ?? '',
                                        'is_correct' => $opt['is_correct'] ?? false,
                                        'updated_at' => now(),
                                    ];

                                    // UPSERT EXAM OPTION
                                    if ($optId && DB::table('exam_options')->where('id', $optId)->exists()) {
                                        DB::table('exam_options')->where('id', $optId)->update($optData);
                                    } else {
                                        $optData['created_at'] = now();
                                        $optId = DB::table('exam_options')->insertGetId($optData);
                                    }
                                    $keptExamOptionIds[] = $optId;
                                }
                            }
                        } else {
                            // IT IS A LESSON
                            $lessonId = (isset($cat['id']) && is_numeric($cat['id'])) ? $cat['id'] : null;

                            $lessonData = [
                                'material_id' => $id,
                                'section_type' => 'lesson',
                                'title' => $cat['title'] ?? 'New Lesson',
                                'time_limit' => $cat['time_limit'] ?? 0,
                                'sort_order' => $index + 1,
                                'updated_at' => now(),
                            ];

                            // UPSERT LESSON
                            if ($lessonId && DB::table('lessons')->where('id', $lessonId)->exists()) {
                                DB::table('lessons')->where('id', $lessonId)->update($lessonData);
                            } else {
                                $lessonData['created_at'] = now();
                                $lessonId = DB::table('lessons')->insertGetId($lessonData);
                            }
                            $keptLessonIds[] = $lessonId;

                            foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                                $quizId = (isset($q['id']) && is_numeric($q['id'])) ? $q['id'] : null;

                                $quizData = [
                                    'lesson_id' => $lessonId,
                                    'type' => $q['type'] ?? 'mcq',
                                    'question_text' => $q['text'] ?? '',
                                    'media_url' => $q['media_url'] ?? null,
                                    'media_name' => $q['media_name'] ?? null,
                                    'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                                    'sort_order' => $qIndex + 1,
                                    'updated_at' => now(),
                                ];

                                // UPSERT LESSON CONTENT (QUIZ)
                                if ($quizId && DB::table('lesson_contents')->where('id', $quizId)->exists()) {
                                    DB::table('lesson_contents')->where('id', $quizId)->update($quizData);
                                } else {
                                    $quizData['created_at'] = now();
                                    $quizId = DB::table('lesson_contents')->insertGetId($quizData);
                                }
                                $keptQuizIds[] = $quizId;

                                foreach ($q['options'] ?? [] as $opt) {
                                    $optId = (isset($opt['id']) && is_numeric($opt['id'])) ? $opt['id'] : null;
                                    $optData = [
                                        'quiz_id' => $quizId,
                                        'option_text' => $opt['text'] ?? '',
                                        'is_correct' => $opt['is_correct'] ?? false,
                                        'updated_at' => now(),
                                    ];

                                    // UPSERT QUIZ OPTION
                                    if ($optId && DB::table('quiz_options')->where('id', $optId)->exists()) {
                                        DB::table('quiz_options')->where('id', $optId)->update($optData);
                                    } else {
                                        $optData['created_at'] = now();
                                        $optId = DB::table('quiz_options')->insertGetId($optData);
                                    }
                                    $keptQuizOptionIds[] = $optId;
                                }
                            }
                        }
                    }

                    // 3. TARGETED CLEANUP (Delete what was removed from the UI before running Excel import)
                    DB::table('exam_options')
                        ->whereIn('exam_id', function ($query) use ($id) {
                            $query->select('id')->from('exams')->where('material_id', $id);
                        })
                        ->whereNotIn('id', $keptExamOptionIds)
                        ->delete();

                    DB::table('exams')
                        ->where('material_id', $id)
                        ->whereNotIn('id', $keptExamIds)
                        ->delete();

                    DB::table('quiz_options')
                        ->whereIn('quiz_id', function ($query) use ($id) {
                            $query->select('id')->from('lesson_contents')
                                ->whereIn('lesson_id', function ($sub) use ($id) {
                                    $sub->select('id')->from('lessons')->where('material_id', $id);
                                });
                        })
                        ->whereNotIn('id', $keptQuizOptionIds)
                        ->delete();

                    DB::table('lesson_contents')
                        ->whereIn('lesson_id', function ($query) use ($id) {
                            $query->select('id')->from('lessons')->where('material_id', $id);
                        })
                        ->whereNotIn('id', $keptQuizIds)
                        ->delete();

                    DB::table('lessons')
                        ->where('material_id', $id)
                        ->whereNotIn('id', $keptLessonIds)
                        ->delete();
                }
            }

            // 4. Run the Excel Import
            Excel::import(new LessonImport($id), $request->file('module_file'));

            // 5. Migrate mistakenly imported exams from 'lessons' to 'exams' table right away
            // This ensures imported exams append directly into the Final Exam block instead of creating separated mock lessons
            $importedExams = DB::table('lessons')
                ->where('material_id', $id)
                ->where('section_type', 'exam')
                ->get();

            if ($importedExams->isNotEmpty()) {
                // Determine the highest existing sort order in the exams table so new questions append at the bottom
                $maxSortOrder = DB::table('exams')->where('material_id', $id)->max('sort_order') ?? 0;

                foreach ($importedExams as $importedExam) {
                    $importedQuestions = DB::table('lesson_contents')
                        ->where('lesson_id', $importedExam->id)
                        ->orderBy('sort_order', 'asc')
                        ->get();

                    foreach ($importedQuestions as $q) {
                        $maxSortOrder++;

                        $examId = DB::table('exams')->insertGetId([
                            'material_id' => $id,
                            'type' => $q->type,
                            'question_text' => $q->question_text,
                            'media_url' => $q->media_url,
                            'media_name' => $q->media_name,
                            'is_case_sensitive' => $q->is_case_sensitive,
                            'sort_order' => $maxSortOrder,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $options = DB::table('quiz_options')->where('quiz_id', $q->id)->get();
                        foreach ($options as $opt) {
                            DB::table('exam_options')->insert([
                                'exam_id' => $examId,
                                'option_text' => $opt->option_text,
                                'is_correct' => $opt->is_correct,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    // Cleanup the imported exam fragments from the lessons/quizzes tables
                    $quizIds = $importedQuestions->pluck('id')->toArray();
                    if (!empty($quizIds)) {
                        DB::table('quiz_options')->whereIn('quiz_id', $quizIds)->delete();
                        DB::table('lesson_contents')->whereIn('id', $quizIds)->delete();
                    }
                    DB::table('lessons')->where('id', $importedExam->id)->delete();
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Lessons imported successfully!']);
        } catch (Exception $e) {
            DB::RollBack();
            Log::error('Material Import Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    public function addTag(Request $request, Material $material)
    {
        $request->validate([
            'tag' => 'required|string|max:50'
        ]);

        // Find the tag or create it if it doesn't exist
        $tag = Tag::firstOrCreate(['name' => current(explode(',', $request->tag))]); // basic sanitization

        // Attach it to the material without duplicating the link
        $material->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json([
            'success' => true,
            'message' => 'Tag attached successfully'
        ]);
    }

    public function removeTag(Material $material, $tagName)
    {
        // Find the tag by name
        $tag = Tag::where('name', $tagName)->first();

        if ($tag) {
            // Detach it from this specific material
            $material->tags()->detach($tag->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tag removed successfully'
        ]);
    }
    public function toggleVisibility(Request $request, $id)
    {
        try {
            $material = DB::table('materials')->where('id', $id)->first();

            // Toggle the boolean
            $newVisibility = !$material->is_public;

            DB::table('materials')
                ->where('id', $id)
                ->update(['is_public' => $newVisibility, 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'is_public' => $newVisibility,
                'message' => 'Module is now ' . ($newVisibility ? 'Public (Open to all)' : 'Private (Restricted access)')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating visibility: ' . $e->getMessage()], 500);
        }
    }

    public function toggleShuffle(Request $request, $id)
    {
        try {
            $material = DB::table('materials')->where('id', $id)->first();

            // Toggle the boolean
            $newShuffle = !$material->is_shuffled;

            DB::table('materials')
                ->where('id', $id)
                ->update(['is_shuffled' => $newShuffle, 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'is_shuffled' => $newShuffle,
                'message' => 'Final Exam questions will ' . ($newShuffle ? 'be shuffled' : 'follow the original order')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating shuffle setting: ' . $e->getMessage()], 500);
        }
    }

    public function toggleDownloadable(Request $request, $id)
    {
        try {
            $material = DB::table('materials')->where('id', $id)->first();

            // Toggle the boolean
            $newDownloadable = !$material->is_downloadable;

            DB::table('materials')
                ->where('id', $id)
                ->update(['is_downloadable' => $newDownloadable, 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'is_downloadable' => $newDownloadable,
                'message' => 'Downloads are now ' . ($newDownloadable ? 'enabled' : 'disabled')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating download setting: ' . $e->getMessage()], 500);
        }
    }

    public function sendIndividualInvite(Request $request, $accessId)
    {
        try {
            $access = MaterialAccess::with('material')->findOrFail($accessId);

            // 1. Pre-validate to avoid unnecessary SMTP connection crashes
            if (!filter_var($access->email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "The email '{$access->email}' is invalid or does not exist. Please remove and re-add the correct email."
                ], 422);
            }

            // 2. Send the Email
            Mail::to($access->email)->send(new MaterialInvitationMail($access->material, $access->email));

            // 3. Update the status
            $access->update(['status' => 'invited']);

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully.'
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Intercept raw SMTP errors and replace them with user-friendly text
            if (str_contains($errorMessage, '553') || str_contains($errorMessage, 'RFC 5321')) {
                $friendlyMessage = "The email address is invalid or does not exist.";
            } else {
                $friendlyMessage = "Failed to send invite due to a mail server error.";
            }

            return response()->json([
                'success' => false,
                'message' => $friendlyMessage
            ], 500);
        }
    }

    /**
     * Bulk send invitations to all pending/invited students.
     */
    /**
     * Bulk send invitations ONLY to pending students.
     */
    public function notifyStudents(Request $request, $id)
    {
        try {
            $material = Material::findOrFail($id);

            // STRICT TARGETING: Only fetch students with 'pending' status
            $targets = MaterialAccess::where('material_id', $material->id)
                ->where('status', 'pending')
                ->get();

            if ($targets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'There are no pending students to invite. Everyone has either been invited or is already enrolled.'
                ]);
            }

            $sentCount = 0;
            $failedCount = 0;

            foreach ($targets as $access) {
                // 1. Skip strictly invalid emails (e.g. ones with commas instead of dots)
                if (!filter_var($access->email, FILTER_VALIDATE_EMAIL)) {
                    $failedCount++;
                    continue;
                }

                // 2. Try-Catch inside the loop so one bad email doesn't break the whole list!
                try {
                    Mail::to($access->email)->send(new MaterialInvitationMail($material, $access->email));
                    $access->update(['status' => 'invited']);
                    $sentCount++;
                } catch (\Exception $e) {
                    // SMTP error for this specific email, count it as failed and move to the next
                    $failedCount++;
                }
            }

            // Prepare the summary message
            if ($sentCount === 0 && $failedCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send. The pending email addresses are invalid or do not exist.'
                ], 422);
            }

            $message = "Successfully sent invitations to {$sentCount} pending student(s).";
            if ($failedCount > 0) {
                // If some emails worked but others failed, notify the teacher so they can check the list
                $message .= " Failed to send to {$failedCount} invalid email(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
        }
    }


    public function toggleFeatured(\App\Models\Material $material)
    {
        $material->update([
            'is_featured' => !$material->is_featured
        ]);

        return response()->json([
            'success' => true,
            'is_featured' => $material->is_featured,
            'message' => $material->is_featured ? 'Material added to featured carousel!' : 'Material removed from featured carousel.'
        ]);
    }

    public function show($hashid)
    {
        $decoded = Hashids::decode($hashid);
        if (empty($decoded))
            abort(404, 'Invalid link.');
        $material = Material::findOrFail($decoded[0]);

        // 1. Security Check: If private, verify the student is on the access list
        if (!$material->is_public && auth()->user()->role === 'student') {
            $hasAccess = MaterialAccess::where('material_id', $material->id)
                ->where('email', auth()->user()->email)
                ->exists();

            if (!$hasAccess) {
                // If they aren't on the list, block them completely
                abort(403, 'You do not have permission to view this private material.');
            }
        }

        // 2. Increment the view counter every time the page is opened
        $material->increment('views');

        // 3. Eager load the relationships we need to display on the page
        $material->load([
            'instructor',
            'tags',
            'lessons' => function ($query) {
                // Assuming you'll want lessons listed in order
                $query->orderBy('sort_order', 'asc');
            }
        ]);

        // 4. Check if the current user is already enrolled using the Enrollment table
        // Security check for students
        $isEnrolled = false;
        if (auth()->user()->role === 'student') {
            $isEnrolled = \App\Models\Enrollment::where('material_id', $material->id) // use $material->id if in MaterialsController
                ->where('user_id', auth()->id())
                ->where('status', '!=', 'dropped') // <-- THIS IS THE KEY ADDITION
                ->exists();
        }

        return view('dashboard.partials.student.materials-show', compact('material', 'isEnrolled'));
    }

    public function enroll(Request $request, Material $material)
    {
        $user = auth()->user();

        // Security check: Make sure only students can enroll
        if ($user->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Only students can enroll in materials.'
            ], 403);
        }

        // --- NEW: Global Lockout Check ---
        // Fetch access based on user ID or Email (in case it was imported)
        $access = \App\Models\MaterialAccess::where('material_id', $material->id)
            ->where(function($q) use ($user) {
                $q->where('student_id', $user->id)
                  ->orWhere('email', $user->email);
            })
            ->first();

        // If they have 3 retakes, they can NEVER enroll again. Block the API call.
        if ($access && $access->retakes >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum retake limit reached. You can no longer access this material.'
            ], 403);
        }

        // 1. Check Private Material Access
        if (!$material->is_public) {
            if (!$access) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to enroll in this private material.'
                ], 403);
            }

            // Sync access status
            if ($access->status !== 'enrolled' || is_null($access->student_id)) {
                $access->update([
                    'status' => 'enrolled',
                    'student_id' => $user->id
                ]);
            }
        }

        // 2. Check for Existing Enrollment (Handle Dropped vs Active)
        $existingEnrollment = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->status === 'dropped') {
                // RESURRECT THE DROPPED STUDENT
                $existingEnrollment->update([
                    'status' => 'in_progress',
                    'progress_data' => null,
                    'completed_at' => null // Clear any residual progress data
                ]);
            } else {
                // They are actively enrolled, completed, or failed. Block duplicate.
                return response()->json([
                    'success' => false,
                    'message' => 'You are already enrolled in this material.'
                ]);
            }
        } else {
            // 3. Create the New Enrollment
            \App\Models\Enrollment::create([
                'material_id' => $material->id,
                'user_id' => $user->id,
                'status' => 'in_progress'
            ]);
        }

        // 4. Auto-add public enrollees to the access list so teachers can track them
        if ($material->is_public) {
            \App\Models\MaterialAccess::updateOrCreate(
                ['material_id' => $material->id, 'email' => $user->email],
                [
                    'status' => 'enrolled',
                    'student_id' => $user->id
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled!'
        ]);
    }

    public function study($hashid)
    {
        $decoded = Hashids::decode($hashid);
        if (empty($decoded))
            abort(404, 'Invalid link.');
        $material = Material::findOrFail($decoded[0]);
        $user = auth()->user();

        // 1. Fetch Enrollment to get Saved Progress
        $enrollment = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment && $user->role === 'student') {
            abort(403, 'You must enroll in this material before studying.');
        }

        // --- NEW: Hard Lockout Check ---
        if ($user->role === 'student') {
            $access = \App\Models\MaterialAccess::where('material_id', $material->id)
                ->where(function($q) use ($user) {
                    $q->where('student_id', $user->id)
                      ->orWhere('email', $user->email);
                })
                ->first();
            
            // Unconditionally lock them out of the study route if limit reached
            if ($access && $access->retakes >= 3) {
                abort(403, 'Maximum retake limit reached. You can no longer study this material.');
            }
        }

        // Decode the JSON so the Blade file can read it
        $savedProgress = $enrollment && $enrollment->progress_data
            ? json_decode($enrollment->progress_data)
            : null;

        // 2. LOAD LESSONS, CONTENTS, AND EXAMS WITH SORTING
        $material->load([
            'lessons' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            },
            'lessons.contents' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            },
            'lessons.contents.options',
            'exams.options'
        ]);

        return view('dashboard.partials.student.materials-study', compact('material', 'savedProgress'));
    }


    public function unenroll(Request $request, \App\Models\Material $material)
    {
        $user = auth()->user();

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Find all EXAMS for this material
            $examIds = \Illuminate\Support\Facades\DB::table('exams')
                ->where('material_id', $material->id)
                ->pluck('id');

            // 2. Find all true QUIZZES (strictly filtering out regular reading 'content')
            $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')
                ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
                ->where('lessons.material_id', $material->id)
                ->whereIn('lesson_contents.type', ['mcq', 'checkbox', 'true_false', 'text']) // <-- THE FIX IS HERE
                ->pluck('lesson_contents.id');

            // 3. Delete any submitted answers to clear progress
            if ($quizIds->isNotEmpty()) {
                \App\Models\QuizAnswer::where('user_id', $user->id)->whereIn('lesson_content_id', $quizIds)->delete();
            }
            if ($examIds->isNotEmpty()) {
                \App\Models\ExamAnswer::where('user_id', $user->id)->whereIn('exam_id', $examIds)->delete();
            }

            // 4. ALWAYS delete the enrollment instance
            \App\Models\Enrollment::where('material_id', $material->id)
                ->where('user_id', $user->id)
                ->delete();

            // 5. Dynamic Action based on Assessment existence
            $hasAssessments = $examIds->isNotEmpty() || $quizIds->isNotEmpty();

            if ($hasAssessments) {
                // Scenario A: It's a Graded Course -> Keep record, but mark as 'dropped'
                \App\Models\MaterialAccess::where('material_id', $material->id)
                    ->where('email', $user->email)
                    ->update(['status' => 'dropped']);

                $message = 'Successfully dropped the course.';
            } else {
                // Scenario B: It's Read-only -> Completely DELETE the access instance
                \App\Models\MaterialAccess::where('material_id', $material->id)
                    ->where('email', $user->email)
                    ->delete();

                $message = 'Successfully removed the material.';
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to unenroll: ' . $e->getMessage()
            ], 500);
        }
    }



    public function saveProgress(Request $request, \App\Models\Material $material)
    {
        $user = auth()->user();

        // 1. Save Position Progress to Enrollment
        \App\Models\Enrollment::updateOrCreate(
            ['material_id' => $material->id, 'user_id' => $user->id],
            [
                'progress_data' => json_encode([
                    'lesson' => $request->lesson_index,
                    'content' => $request->content_index,
                    'highest_unlocked' => $request->highest_unlocked
                ])
            ]
        );

        $validQuestionTypes = ['mcq', 'true_false', 'checkbox', 'text'];
        $type = $request->question_type;
        $isCorrect = false;
        $feedbackType = 'incorrect';

        // 2. Validate and Grade the Answer
        if (in_array($type, $validQuestionTypes) && $request->has('question_id')) {

            $answerData = $request->answer_data;
            if ($answerData === null || $answerData === '') {
                return response()->json(['success' => true]); // Ignore empty skipped exam answers
            }

            $isExam = $request->is_exam;
            $questionId = $request->question_id;

            // Helper to normalize spaces and casing
            $normalizeText = function ($text, $isCaseSensitive) {
                $text = trim(preg_replace('/\s+/', ' ', $text)); // Remove extra spaces
                return $isCaseSensitive ? $text : strtolower($text);
            };

            $questionTable = $isExam ? 'exams' : 'lesson_contents';
            $optionsTable = $isExam ? 'exam_options' : 'quiz_options';
            $foreignKey = $isExam ? 'exam_id' : 'quiz_id';

            // GRADING LOGIC
            if (in_array($type, ['mcq', 'true_false'])) {
                $isCorrect = \Illuminate\Support\Facades\DB::table($optionsTable)->where('id', $answerData)->value('is_correct') == 1;
                $feedbackType = $isCorrect ? 'correct' : 'incorrect';
            } elseif ($type === 'checkbox') {
                $selectedIds = explode(',', $answerData);
                $correctIds = \Illuminate\Support\Facades\DB::table($optionsTable)->where($foreignKey, $questionId)->where('is_correct', 1)->pluck('id')->toArray();
                sort($selectedIds);
                sort($correctIds);
                $isCorrect = ($selectedIds == $correctIds);
                $feedbackType = $isCorrect ? 'correct' : 'incorrect';
            } elseif ($type === 'text') {
                $question = \Illuminate\Support\Facades\DB::table($questionTable)->find($questionId);
                $correctOptions = \Illuminate\Support\Facades\DB::table($optionsTable)->where($foreignKey, $questionId)->where('is_correct', 1)->pluck('option_text');

                if ($correctOptions->isEmpty()) {
                    $isCorrect = true;
                    $feedbackType = 'recorded_as_is'; // No right answer defined by teacher
                } else {
                    $isCaseSensitive = $question->is_case_sensitive ?? false;
                    $userTextNormalized = $normalizeText($answerData, $isCaseSensitive);

                    foreach ($correctOptions as $opt) {
                        if ($userTextNormalized === $normalizeText($opt, $isCaseSensitive)) {
                            $isCorrect = true;
                            break;
                        }
                    }
                    $feedbackType = $isCorrect ? 'correct' : 'incorrect';
                }
            }

            // SAVE TO DATABASE
            if ($isExam) {
                \App\Models\ExamAnswer::updateOrCreate(
                    ['user_id' => $user->id, 'exam_id' => $questionId],
                    [
                        'exam_option_id' => is_numeric($answerData) && $type !== 'checkbox' ? $answerData : null,
                        'text_answer' => (!is_numeric($answerData) || $type === 'checkbox') ? $answerData : null,
                        'is_correct' => $isCorrect
                    ]
                );
            } else {
                \App\Models\QuizAnswer::updateOrCreate(
                    ['user_id' => $user->id, 'lesson_content_id' => $questionId],
                    [
                        'quiz_option_id' => is_numeric($answerData) && $type !== 'checkbox' ? $answerData : null,
                        'text_answer' => (!is_numeric($answerData) || $type === 'checkbox') ? $answerData : null,
                        'is_correct' => $isCorrect
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'feedback_type' => $feedbackType
        ]);
    }

    private function calculateGrades(\App\Models\Material $material, $user)
    {
        $hasExams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->exists();
        $hasQuizzes = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
            ->where('lessons.material_id', $material->id)
            ->whereIn('lesson_contents.type', ['mcq', 'checkbox', 'true_false', 'text'])
            ->exists();

        $examWeight = $material->exam_weight ?? 60;
        $passingScore = $material->passing_percentage ?? 80;

        if ($hasExams && !$hasQuizzes) {
            $examWeight = 100;
        } elseif (!$hasExams && $hasQuizzes) {
            $examWeight = 0;
        } elseif (!$hasExams && !$hasQuizzes) {
            $examWeight = 0;
        }

        $quizWeight = 100 - $examWeight;

        // Calculate Quiz Score
        $quizScore = 0;
        if ($hasQuizzes) {
            $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')
                ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
                ->where('lessons.material_id', $material->id)
                ->whereIn('lesson_contents.type', ['mcq', 'checkbox', 'true_false', 'text'])
                ->pluck('lesson_contents.id');

            $totalQuizzes = $quizIds->count();
            $correctQuizzes = \Illuminate\Support\Facades\DB::table('quiz_answers')
                ->where('user_id', $user->id)
                ->whereIn('lesson_content_id', $quizIds)
                ->where('is_correct', 1)
                ->count();

            $quizScore = $totalQuizzes > 0 ? ($correctQuizzes / $totalQuizzes) * 100 : 100;
        }

        // Calculate Exam Score
        $examScore = 0;
        if ($hasExams) {
            $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->pluck('id');
            $totalExams = $examIds->count();
            $correctExams = \Illuminate\Support\Facades\DB::table('exam_answers')
                ->where('user_id', $user->id)
                ->whereIn('exam_id', $examIds)
                ->where('is_correct', 1)
                ->count();

            $examScore = $totalExams > 0 ? ($correctExams / $totalExams) * 100 : 100;
        }

        // Calculate Total
        if (!$hasExams && !$hasQuizzes) {
            $totalScore = 100;
        } else {
            $totalScore = ($quizScore * ($quizWeight / 100)) + ($examScore * ($examWeight / 100));
        }

        // UPDATE THIS RETURN BLOCK:
        return [
            'hasQuizzes' => $hasQuizzes,   // <-- Added this
            'hasExams' => $hasExams,       // <-- Added this
            'quizScore' => round($quizScore, 2),
            'examScore' => round($examScore, 2),
            'totalScore' => round($totalScore, 2),
            'passingScore' => $passingScore,
            'quizWeight' => $quizWeight,
            'examWeight' => $examWeight,
            'passed' => round($totalScore, 2) >= $passingScore
        ];
    }

    public function complete(Request $request, \App\Models\Material $material)
    {
        $user = auth()->user();

        // --- SERVER-SIDE VALIDATION FOR INCOMPLETE ASSESSMENTS ---
        $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lessons.id', '=', 'lesson_contents.lesson_id')
            ->where('lessons.material_id', $material->id)
            ->whereIn('lesson_contents.type', ['mcq', 'checkbox', 'true_false', 'text'])
            ->pluck('lesson_contents.id');

        $examIds = \Illuminate\Support\Facades\DB::table('exams')
            ->where('material_id', $material->id)
            ->pluck('id');

        $answeredQuizzesCount = \Illuminate\Support\Facades\DB::table('quiz_answers')
            ->where('user_id', $user->id)
            ->whereIn('lesson_content_id', $quizIds)
            ->distinct('lesson_content_id')
            ->count('lesson_content_id');

        $answeredExamsCount = \Illuminate\Support\Facades\DB::table('exam_answers')
            ->where('user_id', $user->id)
            ->whereIn('exam_id', $examIds)
            ->distinct('exam_id')
            ->count('exam_id');

        if ($answeredQuizzesCount < $quizIds->count() || $answeredExamsCount < $examIds->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Incomplete assessments. You must answer all questions before finishing the module.'
            ], 422);
        }
        // ---------------------------------------------------------

        $grades = $this->calculateGrades($material, $user);

        $enrollment = \App\Models\Enrollment::where('material_id', $material->id)->where('user_id', $user->id)->firstOrFail();

        // Push progress to max so it shows 100% on the show page
        $totalLessons = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->count();
        $hasExams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->exists();
        $totalTimelineCount = $totalLessons + ($hasExams ? 1 : 0);

        // 1. Check if material actually has graded items BEFORE saving to database
        $hasAssessments = $grades['hasQuizzes'] || $grades['hasExams'];

        if ($grades['passed']) {
            // 2. Dynamically set status to 'completed' or 'read'
            $enrollment->update([
                'status' => $hasAssessments ? 'completed' : 'read',
                'completed_at' => now(),
                'progress_data' => json_encode(['lesson' => $totalTimelineCount - 1, 'content' => 0, 'highest_unlocked' => $totalTimelineCount])
            ]);

            if ($hasAssessments) {
                // Scenario A: Earned a Certificate -> Send Notification
                $user->notify(new \App\Notifications\LmsAlertNotification(
                    'Certificate Unlocked!',
                    'Congratulations! You passed "' . $material->title . '" with a score of ' . $grades['totalScore'] . '% and earned your certificate.',
                    route('dashboard.materials.certificate', $material->hashid),
                    'fas fa-trophy',
                    'text-yellow-500' // Golden color for the trophy
                ));

                // 🛑 FIXED: Redirect to the RESULTS page so they can see their score breakdown first!
                $redirectUrl = route('dashboard.materials.result', $material->hashid);
            } else {
                // Scenario B: Read-only module completed -> NO System Notification, just redirect
                $redirectUrl = route('dashboard.materials.show', $material->hashid);
            }

            return response()->json([
                'success' => true,
                'passed' => true,
                'has_certificate' => $hasAssessments,
                'redirect_url' => $redirectUrl
            ]);

        } else {
            $enrollment->update([
                'status' => 'failed',
                'progress_data' => json_encode(['lesson' => $totalTimelineCount - 1, 'content' => 0, 'highest_unlocked' => $totalTimelineCount])
            ]);

            return response()->json([
                'success' => true,
                'passed' => false,
                'redirect_url' => route('dashboard.materials.result', $material->hashid)
            ]);
        }
    }

    public function result($hashid)
    {

        $decoded = Hashids::decode($hashid);
        if (empty($decoded))
            abort(404, 'Invalid link.');
        $material = Material::findOrFail($decoded[0]);
        $user = auth()->user();
        $grades = $this->calculateGrades($material, $user);

        // Calculate if a perfect exam score is mathematically enough to pass
        $maxPossibleScore = ($grades['quizScore'] * ($grades['quizWeight'] / 100)) + (100 * ($grades['examWeight'] / 100));
        $canPassWithExamRetake = $maxPossibleScore >= $grades['passingScore'];

        return view('dashboard.partials.student.materials-result', compact('material', 'grades', 'canPassWithExamRetake', 'maxPossibleScore'));
    }

    public function retake(Request $request, $hashid)
    {
        $decoded = Hashids::decode($hashid);
        if (empty($decoded))
            abort(404, 'Invalid link.');
        $material = Material::findOrFail($decoded[0]);
        $user = auth()->user();

        // --- NEW: Block action if max retakes reached ---
        $access = \App\Models\MaterialAccess::where('material_id', $material->id)
            ->where('student_id', $user->id)
            ->first();

        if ($access && $access->retakes >= 3) {
            abort(403, 'Maximum retake limit reached.');
        }

        $type = $request->type; // 'exam' or 'module'

        $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->pluck('id');
        $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
            ->where('lessons.material_id', $material->id)
            ->pluck('lesson_contents.id');

        if ($type === 'module') {
            // Delete EVERYTHING
            \App\Models\QuizAnswer::where('user_id', $user->id)->whereIn('lesson_content_id', $quizIds)->delete();
            \App\Models\ExamAnswer::where('user_id', $user->id)->whereIn('exam_id', $examIds)->delete();

            // Reset Enrollment Progress (Removed 'retakes' from here)
            \App\Models\Enrollment::where('material_id', $material->id)->where('user_id', $user->id)->update([
                'progress_data' => json_encode(['lesson' => 0, 'content' => 0, 'highest_unlocked' => 0]),
                'status' => 'in_progress'
            ]);
        } elseif ($type === 'exam') {
            // Delete EXAMS only
            \App\Models\ExamAnswer::where('user_id', $user->id)->whereIn('exam_id', $examIds)->delete();

            // Jump back to the start of the Exam section (Removed 'retakes' from here)
            $examSectionIndex = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->count();
            \App\Models\Enrollment::where('material_id', $material->id)->where('user_id', $user->id)->update([
                'progress_data' => json_encode(['lesson' => $examSectionIndex, 'content' => 0, 'highest_unlocked' => $examSectionIndex]),
                'status' => 'in_progress'
            ]);
        }

        // --- NEW LOGIC: Increment retakes in the material_accesses table ---
        if ($access) {
            $access->increment('retakes');
        }

        return redirect()->route('dashboard.materials.study', $material->hashid);
    }

    public function certificate($hashid)
    {


        // 1. Decode the material hashid from the URL
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($hashid);
        if (empty($decoded)) {
            abort(404, 'Invalid link.');
        }
        $material = Material::findOrFail($decoded[0]);

        // 2. Fetch the enrollment
        $enrollment = \App\Models\Enrollment::with(['user', 'material.instructor'])
            ->where('material_id', $material->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        // ADD THIS LINE TO ENCODE THE HASHID
        $hashid = \Vinkla\Hashids\Facades\Hashids::encode($enrollment->id);


        return view('dashboard.partials.student.certificate-achieved', compact('enrollment', 'hashid'));
    }

    public function downloadCertificate($hashid)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($hashid);
        if (empty($decoded)) {
            abort(404, 'Invalid link.');
        }
        
        $enrollmentId = $decoded[0];
        
        $enrollment = \App\Models\Enrollment::with(['user', 'material.instructor'])
            ->findOrFail($enrollmentId);

        $url = route('dashboard.materials.certificate', ['hashid' => $hashid]);
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate($url));

        $data = [
            'studentName' => trim($enrollment->user->first_name . ' ' . $enrollment->user->last_name),
            'courseName' => $enrollment->material->title,
            'instructorName' => trim(($enrollment->material->instructor->first_name ?? 'Instructor') . ' ' . ($enrollment->material->instructor->last_name ?? '')),
            'date' => $enrollment->completed_at ? $enrollment->completed_at->format('F j, Y') : now()->format('F j, Y'),
            'certificateId' => 'CERT-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
            'qrCode' => $qrCode
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.student.certificate-template', $data)
            ->setPaper('a4', 'landscape');
            
        return $pdf->download('Certificate_of_Completion_' . $enrollment->user->last_name . '.pdf');
    }

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
                    'title' => $notif->data['title'] ?? 'Notification',
                    'message' => $notif->data['message'] ?? '',
                    'url' => $notif->data['url'] ?? '#',
                    'icon' => $notif->data['icon'] ?? 'fas fa-bell',
                    'colorClass' => $notif->data['colorClass'] ?? 'text-blue-500',
                    'time_ago' => $notif->created_at->diffForHumans(),
                    'timestamp' => $notif->created_at->timestamp,
                    'is_read' => $notif->read_at !== null
                ];
            });

        // 2. Fetch Global Broadcasts (Last 30 days)
        $broadcasts = \App\Models\Broadcast::where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->get();
            
        // Get an array of broadcast IDs this user has already read
        $readBroadcastIds = \App\Models\BroadcastRead::where('user_id', $user->id)
            ->pluck('broadcast_id')
            ->toArray();

        $broadcastNotifs = $broadcasts->map(function ($broadcast) use ($readBroadcastIds) {
            // Set dynamic styling based on broadcast type
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
        $allNotifications = collect($standardNotifs)
            ->merge($broadcastNotifs)
            ->sortByDesc('timestamp')
            ->values();
            
        $unreadCount = $allNotifications->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'notifications' => $allNotifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark a single notification or broadcast as read
     */
    public function markNotificationRead(\Illuminate\Http\Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if the frontend flagged this as a broadcast
        $isBroadcast = $request->query('is_broadcast') === 'true';

        if ($isBroadcast) {
            // Log it in the pivot table so they don't see it as "unread" again
            \App\Models\BroadcastRead::firstOrCreate([
                'user_id' => $user->id,
                'broadcast_id' => $id
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

    public function preview($hashid)
    {
        $decoded = Hashids::decode($hashid);
        // If the hash is invalid or tampered with, throw a 404
        if (empty($decoded)) {
            abort(404, 'Invalid material link.');
        }
        $id = $decoded[0];

        // Fetch the material, ensuring it is public and published

        // Fetch the material with its relationships
        $material = Material::with([
            'instructor',
            // We add an orderBY constraint to the nested lesson contents
            'lessons' => function ($query) {
                $query->orderBy('sort_order', 'asc'); // Ensures lessons themselves are in order
            },
            'lessons.contents' => function ($query) {
                $query->orderBy('sort_order', 'asc'); // 👈 Sorts contents inside each lesson
            },
            'exams'
        ])->findOrFail($id);

        // Authorization: Allow the instructor (owner), admins, or CID personnel
        $user = auth()->user();

        if (in_array($user->role, ['admin', 'cid']) && $material->status === 'draft' && $material->instructor_id !== $user->id) {
            return response('
            <div class="flex flex-col items-center justify-center h-[60vh] text-center px-4">
                <div class="w-20 h-20 bg-red-50 text-[#a52a2a] rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-eye-slash text-3xl"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">Preview Unavailable</h2>
                <p class="text-gray-500 max-w-md text-sm">This module is currently in <b>Draft</b> mode. It cannot be previewed until the instructor submits it.</p>
            </div>
        ');
        }


        if (auth()->id() !== $material->instructor_id && !in_array($user->role, ['admin', 'cid'])) {
            abort(403, 'Unauthorized action.');
        }

        // Return the preview view
        return view('dashboard.partials.shared.materials-preview', compact('material'));
    }

    public function analytics($id)
    {
        $material = \App\Models\Material::findOrFail($id);
        $passingScore = $material->passing_percentage ?? 80;

        // 1. Enrollment KPIs (Keep these showing everyone)
        $totalLearners = \App\Models\Enrollment::where('material_id', $material->id)->count();
        $pendingRequests = \App\Models\MaterialAccess::where('material_id', $material->id)->where('status', 'pending')->count();
        $totalDropped = \App\Models\Enrollment::where('material_id', $material->id)->where('status', 'dropped')->count();
        $activeLearners = \App\Models\Enrollment::where('material_id', $material->id)->where('updated_at', '>=', \Carbon\Carbon::now()->subDays(7))->count();

        // 2. Student Progress
        $completedCount = \App\Models\Enrollment::where('material_id', $material->id)->where('status', 'completed')->count();
        $inProgressCount = \App\Models\Enrollment::where('material_id', $material->id)->whereIn('status', ['in_progress', 'failed'])->count();

        // 3. Assessment Fetching
        $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->pluck('id');
        $lessonIds = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->pluck('id');
        $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->where('type', '!=', 'content')->pluck('id');

        $hasQuizzes = $quizIds->isNotEmpty();
        $hasExams = $examIds->isNotEmpty();

        $quizItemsCount = $quizIds->count();
        $examItemsCount = $examIds->count();

        // 4. Activity Trend
        $activityTrendData = \App\Models\Enrollment::select(
            \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
            \Illuminate\Support\Facades\DB::raw('count(*) as count')
        )
            ->where('material_id', $material->id)
            ->where('created_at', '>=', \Carbon\Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('count', 'date')
            ->toArray();

        $activityDates = [];
        $activityTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');
            $activityDates[] = \Carbon\Carbon::parse($date)->format('M d');
            $activityTrend[] = $activityTrendData[$date] ?? 0;
        }

        // ========================================================
        // STRICT FILTER: Get ONLY students who have finished
        // ========================================================
        $completedUserIds = \App\Models\Enrollment::where('material_id', $material->id)
            ->whereIn('status', ['completed', 'failed'])
            ->pluck('user_id');

        // 5. Competency Breakdown (Filtered)
        $competencies = [];
        $lessons = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->get();

        foreach ($lessons as $lesson) {
            $lqIds = \Illuminate\Support\Facades\DB::table('lesson_contents')->where('lesson_id', $lesson->id)->where('type', '!=', 'content')->pluck('id');
            $hasQuiz = $lqIds->isNotEmpty();

            $mps = 0;
            $totalAns = 0;

            if ($hasQuiz) {
                // Apply the strict completed filter here
                $answers = \Illuminate\Support\Facades\DB::table('quiz_answers')
                    ->whereIn('lesson_content_id', $lqIds)
                    ->whereIn('user_id', $completedUserIds);
                    
                $correct = (clone $answers)->where('is_correct', 1)->count();
                $totalAns = $answers->count();

                $mps = $totalAns > 0 ? round(($correct / $totalAns) * 100, 2) : 0;
            }

            $competencies[] = (object) [
                'title' => $lesson->title,
                'has_quiz' => $hasQuiz,
                'mps' => $mps,
                'total_answers' => $totalAns
            ];
        }

        if ($hasExams) {
            $examStats = \Illuminate\Support\Facades\DB::table('exam_answers')
                ->select(
                    'user_id',
                    \Illuminate\Support\Facades\DB::raw('SUM(is_correct = 1) as correct'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total')
                )
                ->whereIn('exam_id', $examIds)
                ->whereIn('user_id', $completedUserIds) // Filter exams too
                ->groupBy('user_id')
                ->get();

            $avgExamScoreCompetency = $examStats->count() > 0
                ? round($examStats->avg(fn($e) => $e->total > 0 ? ($e->correct / $e->total) * 100 : 0), 2)
                : 0;

            $competencies[] = (object) [
                'title' => 'Final Exam',
                'has_quiz' => true,
                'mps' => $avgExamScoreCompetency,
                'total_answers' => $examStats->sum('total')
            ];
        }

        // 6. LEADERBOARD & OVERALL AVERAGES (Filtered)
        $examWeight = $material->exam_weight ?? 60;
        $quizWeight = 100 - $examWeight;

        $allStudents = \App\Models\Enrollment::with('user')
            ->where('material_id', $material->id)
            ->whereIn('status', ['completed', 'failed']) // ONLY Fetch completed students
            ->get()
            ->map(function ($enrollment) use ($examIds, $quizIds, $lessons, $quizWeight, $examWeight, $hasQuizzes, $hasExams) {
                $quizAnswers = \Illuminate\Support\Facades\DB::table('quiz_answers')
                    ->where('user_id', $enrollment->user_id)
                    ->whereIn('lesson_content_id', $quizIds);

                $quizCorrect = (clone $quizAnswers)->where('is_correct', 1)->count();
                $quizTotal = $quizAnswers->count();

                $examAnswers = \Illuminate\Support\Facades\DB::table('exam_answers')
                    ->where('user_id', $enrollment->user_id)
                    ->whereIn('exam_id', $examIds);

                $examCorrect = (clone $examAnswers)->where('is_correct', 1)->count();
                $examTotal = $examAnswers->count();

                $quizScore = $quizTotal > 0 ? round(($quizCorrect / $quizTotal) * 100, 2) : 100;
                $examScore = $examTotal > 0 ? round(($examCorrect / $examTotal) * 100, 2) : 100;
                
                if (!$hasExams && !$hasQuizzes) {
                    $score = 0;
                } elseif ($hasQuizzes && $hasExams) {
                    $score = round(($quizScore * ($quizWeight / 100)) + ($examScore * ($examWeight / 100)));
                } elseif ($hasQuizzes) {
                    $score = round($quizScore);
                } else {
                    $score = round($examScore);
                }

                // Since we only fetch completed/failed, progress is effectively 100%
                $prog = 100; 

                return (object) [
                    'name' => $enrollment->user ? $enrollment->user->first_name . ' ' . $enrollment->user->last_name : 'Unknown Student',
                    'progress' => $prog,
                    'quiz_score' => $quizScore,
                    'exam_score' => $examScore,
                    'quiz_weight' => $quizWeight,
                    'quiz_score_raw' => $quizTotal > 0 ? "{$quizCorrect}/{$quizTotal}" : "0/0",
                    'exam_score_raw' => $examTotal > 0 ? "{$examCorrect}/{$examTotal}" : "0/0",
                    'score' => $score,
                    'status' => $enrollment->status 
                ];
            });

        // Pass Rate Calculation (Using the newly filtered collection)
        $evaluatedCount = $allStudents->count();
        $passCount = $allStudents->where('score', '>=', $passingScore)->count();
        $passRate = $evaluatedCount > 0 ? round(($passCount / $evaluatedCount) * 100) : null;

        // Overall Average
        $overallAverage = $allStudents->count() > 0 ? round($allStudents->avg('score'), 2) : 0;

        // Leaderboard
        $studentLeaderboard = $allStudents->sortByDesc('score')->take(10)->values();

        $validQuizScores = $studentLeaderboard->filter(fn($s) => $s->quiz_score_raw !== "0/0");
        $avgQuizScore = $validQuizScores->count() > 0 ? round($validQuizScores->avg('quiz_score'), 2) : 0;

        $validExamScores = $studentLeaderboard->filter(fn($s) => $s->exam_score_raw !== "0/0");
        $avgExamScore = $validExamScores->count() > 0 ? round($validExamScores->avg('exam_score'), 2) : 0;

        // 7. Item Analysis (Filtered)
        $quizItemAnalysis = [];
        $examItemAnalysis = [];

        if ($hasQuizzes) {
            $quizzes = \Illuminate\Support\Facades\DB::table('lesson_contents')
                ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
                ->select('lesson_contents.id', 'lesson_contents.type', 'lesson_contents.question_text', 'lessons.title as category_name')
                ->where('lessons.material_id', $material->id)
                ->where('lesson_contents.type', '!=', 'content')
                ->get();

            foreach ($quizzes as $q) {
                // Apply strict completed filter here
                $answers = \Illuminate\Support\Facades\DB::table('quiz_answers')
                    ->where('lesson_content_id', $q->id)
                    ->whereIn('user_id', $completedUserIds)
                    ->get();
                    
                $cCount = $answers->where('is_correct', 1)->count();
                $wCount = $answers->where('is_correct', 0)->count();
                $tCount = $cCount + $wCount;

                $opts = [];
                if ($q->type === 'text') {
                    $responses = $answers->groupBy(fn($a) => strtolower(trim($a->text_answer ?? '')));
                    foreach ($responses as $text => $group) {
                        $opts[] = (object) [
                            'text' => $text === '' ? '(Blank)' : $text,
                            'pct' => $tCount > 0 ? round(($group->count() / $tCount) * 100) : 0,
                            'is_correct' => $group->where('is_correct', 1)->isNotEmpty()
                        ];
                    }
                    usort($opts, fn($a, $b) => $b->pct <=> $a->pct);
                } else {
                    $options = \Illuminate\Support\Facades\DB::table('quiz_options')->where('quiz_id', $q->id)->get();
                    foreach ($options as $o) {
                        if ($q->type === 'checkbox') {
                            $selCount = $answers->filter(fn($a) => !empty($a->text_answer) && in_array((string)$o->id, explode(',', $a->text_answer)))->count();
                        } else {
                            $selCount = $answers->where('quiz_option_id', $o->id)->count();
                        }
                        $opts[] = (object) [
                            'text' => $o->option_text,
                            'pct' => $tCount > 0 ? round(($selCount / $tCount) * 100) : 0,
                            'is_correct' => $o->is_correct
                        ];
                    }
                }

                $quizItemAnalysis[] = (object) [
                    'question_text' => $q->question_text,
                    'category_name' => $q->category_name,
                    'correct_count' => $cCount,
                    'wrong_count' => $wCount,
                    'difficulty_index' => $tCount > 0 ? round(($cCount / $tCount) * 100) : 0,
                    'distractor_stats' => $opts
                ];
            }
        }

        if ($hasExams) {
            $exams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->get();

            foreach ($exams as $e) {
                // Apply strict completed filter here
                $answers = \Illuminate\Support\Facades\DB::table('exam_answers')
                    ->where('exam_id', $e->id)
                    ->whereIn('user_id', $completedUserIds)
                    ->get();
                    
                $cCount = $answers->where('is_correct', 1)->count();
                $wCount = $answers->where('is_correct', 0)->count();
                $tCount = $cCount + $wCount;

                $opts = [];
                if ($e->type === 'text') {
                    $responses = $answers->groupBy(fn($a) => strtolower(trim($a->text_answer ?? '')));
                    foreach ($responses as $text => $group) {
                        $opts[] = (object) [
                            'text' => $text === '' ? '(Blank)' : $text,
                            'pct' => $tCount > 0 ? round(($group->count() / $tCount) * 100) : 0,
                            'is_correct' => $group->where('is_correct', 1)->isNotEmpty()
                        ];
                    }
                    usort($opts, fn($a, $b) => $b->pct <=> $a->pct);
                } else {
                    $options = \Illuminate\Support\Facades\DB::table('exam_options')->where('exam_id', $e->id)->get();
                    foreach ($options as $o) {
                        if ($e->type === 'checkbox') {
                            $selCount = $answers->filter(fn($a) => !empty($a->text_answer) && in_array((string)$o->id, explode(',', $a->text_answer)))->count();
                        } else {
                            $selCount = $answers->where('exam_option_id', $o->id)->count();
                        }
                        $opts[] = (object) [
                            'text' => $o->option_text,
                            'pct' => $tCount > 0 ? round(($selCount / $tCount) * 100) : 0,
                            'is_correct' => $o->is_correct
                        ];
                    }
                }

                $examItemAnalysis[] = (object) [
                    'question_text' => $e->question_text,
                    'category_name' => 'Final Exam',
                    'correct_count' => $cCount,
                    'wrong_count' => $wCount,
                    'difficulty_index' => $tCount > 0 ? round(($cCount / $tCount) * 100) : 0,
                    'distractor_stats' => $opts
                ];
            }
        }

        return view('dashboard.partials.shared.materials-analytics', compact(
            'material', 'totalLearners', 'activeLearners', 'pendingRequests', 'totalDropped',
            'overallAverage', 'passRate', 'avgQuizScore', 'avgExamScore', 'completedCount', 
            'inProgressCount', 'activityDates', 'activityTrend', 'competencies', 
            'studentLeaderboard', 'quizItemAnalysis', 'examItemAnalysis', 'hasQuizzes', 
            'hasExams', 'quizItemsCount', 'examItemsCount', 'quizWeight', 'examWeight'
        ));
    }


    public function exportMaterialAnalyticsPdf(Request $request, $id)
    {
        $material = \App\Models\Material::with('instructor')->findOrFail($id);

        if (\Illuminate\Support\Facades\Auth::user()->role === 'teacher' && $material->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $passingScore = $material->passing_percentage ?? 80;

        // 1. Enrollment KPIs
        $totalLearners = \App\Models\Enrollment::where('material_id', $material->id)->count();
        $pendingRequests = \App\Models\MaterialAccess::where('material_id', $material->id)->where('status', 'pending')->count();
        $totalDropped = \App\Models\Enrollment::where('material_id', $material->id)->where('status', 'dropped')->count();
        $activeLearners = \App\Models\Enrollment::where('material_id', $material->id)->where('updated_at', '>=', \Carbon\Carbon::now()->subDays(7))->count();

        // 2. Student Progress
        $completedCount = \App\Models\Enrollment::where('material_id', $material->id)->where('status', 'completed')->count();
        $inProgressCount = \App\Models\Enrollment::where('material_id', $material->id)->whereIn('status', ['in_progress', 'failed'])->count();

        // 3. Assessment Fetching
        $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->pluck('id');
        $lessonIds = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->pluck('id');
        $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->where('type', '!=', 'content')->pluck('id');

        $hasQuizzes = $quizIds->isNotEmpty();
        $hasExams = $examIds->isNotEmpty();

        // ========================================================
        // STRICT FILTER: Get ONLY students who have finished
        // ========================================================
        $completedUserIds = \App\Models\Enrollment::where('material_id', $material->id)
            ->whereIn('status', ['completed', 'failed'])
            ->pluck('user_id');

        // 5. Competency Breakdown (Filtered)
        $competencies = [];
        $lessons = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->get();

        foreach ($lessons as $lesson) {
            $lqIds = \Illuminate\Support\Facades\DB::table('lesson_contents')->where('lesson_id', $lesson->id)->where('type', '!=', 'content')->pluck('id');
            $hasQuiz = $lqIds->isNotEmpty();
            $mps = 0;
            $totalAns = 0;

            if ($hasQuiz) {
                $answers = \Illuminate\Support\Facades\DB::table('quiz_answers')
                    ->whereIn('lesson_content_id', $lqIds)
                    ->whereIn('user_id', $completedUserIds);
                    
                $correct = (clone $answers)->where('is_correct', 1)->count();
                $totalAns = $answers->count();
                $mps = $totalAns > 0 ? round(($correct / $totalAns) * 100, 2) : 0;
            }

            $competencies[] = (object) ['title' => $lesson->title, 'has_quiz' => $hasQuiz, 'mps' => $mps, 'total_answers' => $totalAns];
        }

        if ($hasExams) {
            $examStats = \Illuminate\Support\Facades\DB::table('exam_answers')
                ->select('user_id', \Illuminate\Support\Facades\DB::raw('SUM(is_correct = 1) as correct'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                ->whereIn('exam_id', $examIds)
                ->whereIn('user_id', $completedUserIds)
                ->groupBy('user_id')->get();
                
            $avgExamScoreCompetency = $examStats->count() > 0 ? round($examStats->avg(fn($e) => $e->total > 0 ? ($e->correct / $e->total) * 100 : 0), 2) : 0;
            $competencies[] = (object) ['title' => 'Final Exam', 'has_quiz' => true, 'mps' => $avgExamScoreCompetency, 'total_answers' => $examStats->sum('total')];
        }

        // 6. LEADERBOARD & OVERALL AVERAGES (Filtered)
        $allStudents = \App\Models\Enrollment::with('user')
            ->where('material_id', $material->id)
            ->whereIn('status', ['completed', 'failed']) // ONLY Fetch completed students
            ->get()
            ->map(function ($enrollment) use ($examIds, $quizIds, $lessons) {
                $quizAnswers = \Illuminate\Support\Facades\DB::table('quiz_answers')->where('user_id', $enrollment->user_id)->whereIn('lesson_content_id', $quizIds);
                $quizCorrect = (clone $quizAnswers)->where('is_correct', 1)->count();
                $quizTotal = $quizAnswers->count();

                $examAnswers = \Illuminate\Support\Facades\DB::table('exam_answers')->where('user_id', $enrollment->user_id)->whereIn('exam_id', $examIds);
                $examCorrect = (clone $examAnswers)->where('is_correct', 1)->count();
                $examTotal = $examAnswers->count();

                $totalCorrect = $quizCorrect + $examCorrect;
                $totalAnswered = $quizTotal + $examTotal;

                $quizScore = $quizTotal > 0 ? round(($quizCorrect / $quizTotal) * 100, 2) : 0;
                $examScore = $examTotal > 0 ? round(($examCorrect / $examTotal) * 100, 2) : 0;
                $score = $totalAnswered > 0 ? round(($totalCorrect / $totalAnswered) * 100, 2) : 0;

                return (object) [
                    'name' => $enrollment->user ? $enrollment->user->first_name . ' ' . $enrollment->user->last_name : 'Unknown Student',
                    'progress' => 100, 'quiz_score' => $quizScore, 'exam_score' => $examScore,
                    'quiz_score_raw' => $quizTotal > 0 ? "{$quizCorrect}/{$quizTotal}" : "0/0",
                    'exam_score_raw' => $examTotal > 0 ? "{$examCorrect}/{$examTotal}" : "0/0",
                    'score' => $score, 'status' => $enrollment->status
                ];
            });

        $evaluatedCount = $allStudents->count();
        $passCount = $allStudents->where('score', '>=', $passingScore)->count();
        $passRate = $evaluatedCount > 0 ? round(($passCount / $evaluatedCount) * 100) : null;

        $overallAverage = $allStudents->count() > 0 ? round($allStudents->avg('score'), 2) : 0;
        $studentLeaderboard = $allStudents->sortByDesc('score')->take(10)->values();

        $validQuizScores = $studentLeaderboard->filter(fn($s) => $s->quiz_score_raw !== "0/0");
        $avgQuizScore = $validQuizScores->count() > 0 ? round($validQuizScores->avg('quiz_score'), 2) : 0;
        $validExamScores = $studentLeaderboard->filter(fn($s) => $s->exam_score_raw !== "0/0");
        $avgExamScore = $validExamScores->count() > 0 ? round($validExamScores->avg('exam_score'), 2) : 0;

        // 7. Item Analysis (Filtered)
        $quizItemAnalysis = [];
        $examItemAnalysis = [];

        if ($hasQuizzes) {
            $quizzes = \Illuminate\Support\Facades\DB::table('lesson_contents')
                ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
                ->select('lesson_contents.id', 'lesson_contents.type', 'lesson_contents.question_text', 'lessons.title as category_name')
                ->where('lessons.material_id', $material->id)->where('lesson_contents.type', '!=', 'content')->get();

            foreach ($quizzes as $q) {
                $answers = \Illuminate\Support\Facades\DB::table('quiz_answers')
                    ->where('lesson_content_id', $q->id)
                    ->whereIn('user_id', $completedUserIds)
                    ->get();
                    
                $cCount = $answers->where('is_correct', 1)->count();
                $wCount = $answers->where('is_correct', 0)->count();
                $tCount = $cCount + $wCount;

                $quizItemAnalysis[] = (object) ['question_text' => $q->question_text, 'category_name' => $q->category_name, 'difficulty_index' => $tCount > 0 ? round(($cCount / $tCount) * 100) : 0];
            }
        }

        if ($hasExams) {
            $exams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->get();
            foreach ($exams as $e) {
                $answers = \Illuminate\Support\Facades\DB::table('exam_answers')
                    ->where('exam_id', $e->id)
                    ->whereIn('user_id', $completedUserIds)
                    ->get();
                    
                $cCount = $answers->where('is_correct', 1)->count();
                $wCount = $answers->where('is_correct', 0)->count();
                $tCount = $cCount + $wCount;

                $examItemAnalysis[] = (object) ['question_text' => $e->question_text, 'category_name' => 'Final Exam', 'difficulty_index' => $tCount > 0 ? round(($cCount / $tCount) * 100) : 0];
            }
        }

        $isPrint = $request->input('action') === 'print';

        $data = [
            'material' => $material,
            'totalLearners' => $totalLearners ?? 0,
            'pendingRequests' => $pendingRequests ?? 0,
            'totalDropped' => $totalDropped ?? 0,
            'overallAverage' => $overallAverage ?? 0,
            'passRate' => $passRate,
            'completedCount' => $completedCount ?? 0,
            'inProgressCount' => $inProgressCount ?? 0,
            'competencies' => $competencies ?? [],
            'studentLeaderboard' => $studentLeaderboard ?? [],
            'hasQuizzes' => $hasQuizzes ?? false,
            'hasExams' => $hasExams ?? false,
            'quizItemAnalysis' => $quizItemAnalysis ?? [],
            'examItemAnalysis' => $examItemAnalysis ?? [],
            'avgQuizScore' => $avgQuizScore ?? 0,
            'avgExamScore' => $avgExamScore ?? 0,
            'showMetrics' => $request->has('check_metrics'),
            'showCompetency' => $request->has('check_competency'),
            'showItemAnalysis' => $request->has('check_item_analysis'),
            'isPrint' => $isPrint,
        ];

        if ($isPrint) {
            return view('dashboard.partials.shared.materials-report', $data);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.shared.materials-report', $data);
        return $pdf->download('Material_Analytics_' . \Illuminate\Support\Str::slug($material->title) . '_' . now()->format('Y_m_d') . '.pdf');
    }
    public function updateGrading(Request $request, $id)
    {
        $request->validate([
            'exam_weight' => 'required|integer|min:0|max:100',
            'passing_percentage' => 'required|integer|min:0|max:100',
        ]);

        try {
            \Illuminate\Support\Facades\DB::table('materials')
                ->where('id', $id)
                ->update([
                    'exam_weight' => $request->exam_weight,
                    'passing_percentage' => $request->passing_percentage,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Grading settings saved successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Basic Information (Title, Description, Thumbnail)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB Max
        ]);

        try {
            $material = \App\Models\Material::findOrFail($id);

            // 🛑 SECURITY LOCK: Cannot update title/desc/thumbnail if not draft
            if ($material->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update details while module is published or pending. Please revert to draft first.'
                ], 403);
            }

            // Handle Thumbnail Upload
            if ($request->hasFile('thumbnail')) {
                // (Optional) Delete old thumbnail from storage to save space
                if ($material->thumbnail && \Illuminate\Support\Facades\Storage::disk('public')->exists($material->thumbnail)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($material->thumbnail);
                }

                // Save the new image to storage/app/public/materials/thumbnails
                $path = $request->file('thumbnail')->store('materials/thumbnails', 'public');
                $material->thumbnail = $path;
            }

            // Update text fields
            $material->title = $request->title;
            $material->description = $request->description;

            $material->save();

            return response()->json([
                'success' => true,
                'message' => 'Material details updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duplicate($id)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $material = \Illuminate\Support\Facades\DB::table('materials')->where('id', $id)->first();
            if (!$material) {
                return response()->json(['success' => false, 'message' => 'Material not found.'], 404);
            }

            // 1. Insert new duplicate material
            $newMaterialId = \Illuminate\Support\Facades\DB::table('materials')->insertGetId([
                'title' => $material->title . ' (Copy)',
                'description' => $material->description,
                'instructor_id' => auth()->id(), // Make the duplicating user the owner
                'thumbnail' => $material->thumbnail,
                'status' => 'draft', // Copies always start as drafts
                'is_public' => 0, // Copies start as private
                'access_code' => strtoupper(\Illuminate\Support\Str::random(6)), // Generate fresh access code
                'exam_weight' => $material->exam_weight,
                'passing_percentage' => $material->passing_percentage,
                'draft_json' => $material->draft_json,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Clone Tags
            $tags = \Illuminate\Support\Facades\DB::table('material_tag')->where('material_id', $id)->get();
            foreach ($tags as $tag) {
                \Illuminate\Support\Facades\DB::table('material_tag')->insert([
                    'material_id' => $newMaterialId,
                    'tag_id' => $tag->tag_id
                ]);
            }

            // 3. Deep Clone Lessons & Lesson Contents (Quizzes/Content)
            $lessons = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $id)->get();
            foreach ($lessons as $lesson) {
                $newLessonId = \Illuminate\Support\Facades\DB::table('lessons')->insertGetId([
                    'material_id' => $newMaterialId,
                    'section_type' => $lesson->section_type,
                    'title' => $lesson->title,
                    'time_limit' => $lesson->time_limit,
                    'sort_order' => $lesson->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $contents = \Illuminate\Support\Facades\DB::table('lesson_contents')->where('lesson_id', $lesson->id)->get();
                foreach ($contents as $content) {
                    $newContentId = \Illuminate\Support\Facades\DB::table('lesson_contents')->insertGetId([
                        'lesson_id' => $newLessonId,
                        'type' => $content->type,
                        'question_text' => $content->question_text,
                        'media_url' => $content->media_url,
                        'media_name' => $content->media_name ?? null,
                        'is_case_sensitive' => $content->is_case_sensitive,
                        'sort_order' => $content->sort_order,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $options = \Illuminate\Support\Facades\DB::table('quiz_options')->where('quiz_id', $content->id)->get();
                    foreach ($options as $opt) {
                        \Illuminate\Support\Facades\DB::table('quiz_options')->insert([
                            'quiz_id' => $newContentId,
                            'option_text' => $opt->option_text,
                            'is_correct' => $opt->is_correct,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // 4. Deep Clone Exams
            $exams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $id)->get();
            foreach ($exams as $exam) {
                $newExamId = \Illuminate\Support\Facades\DB::table('exams')->insertGetId([
                    'material_id' => $newMaterialId,
                    'type' => $exam->type,
                    'question_text' => $exam->question_text,
                    'media_url' => $exam->media_url,
                    'media_name' => $exam->media_name ?? null,
                    'is_case_sensitive' => $exam->is_case_sensitive,
                    'sort_order' => $exam->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $examOptions = \Illuminate\Support\Facades\DB::table('exam_options')->where('exam_id', $exam->id)->get();
                foreach ($examOptions as $opt) {
                    \Illuminate\Support\Facades\DB::table('exam_options')->insert([
                        'exam_id' => $newExamId,
                        'option_text' => $opt->option_text,
                        'is_correct' => $opt->is_correct,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Material duplicated successfully.'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error duplicating material: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while duplicating the material.'
            ], 500);
        }
    }

    public function generateAccessCode($id)
    {
        $material = \App\Models\Material::findOrFail($id);

        // 1. Authorization: Only the owner or admins can do this
        if (auth()->id() !== $material->instructor_id && !in_array(auth()->user()->role, ['admin', 'cid'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        // 2. Enforce the 3-hour lock
        // Replace your current 3-hour lock check with this:
        if ($material->access_code && $material->access_code_expires_at && now()->lessThan($material->access_code_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'The current access code is still active. Please wait for it to expire.'
            ], 422);
        }
        // 3. Generate new code and expiration time
        $newCode = strtoupper(\Illuminate\Support\Str::random(6));
        $expiresAt = now()->addHours(3);

        // 4. Update the database
        $material->access_code = $newCode;
        $material->access_code_expires_at = $expiresAt;
        $material->updated_at = now();
        $material->save();

        return response()->json([
            'success' => true,
            'code' => $newCode,
            'expires_at' => $expiresAt->toIso8601String(), // Send standard format for JS to read
            'message' => 'New 3-hour access key generated successfully!'
        ]);
    }

    /**
     * Clears student progress but keeps their enrollment records.
     */
    private function clearStudentProgressAndResults($material)
    {
        // 1. Find all exam and quiz IDs
        $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->pluck('id');
        $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
            ->where('lessons.material_id', $material->id)
            ->pluck('lesson_contents.id');

        // 2. Identify enrolled students so we can notify them
        $enrolledStudentIds = \Illuminate\Support\Facades\DB::table('enrollments')
            ->where('material_id', $material->id)
            ->pluck('user_id');

        // 3. Delete all submitted student answers
        if ($examIds->isNotEmpty()) {
            \Illuminate\Support\Facades\DB::table('exam_answers')->whereIn('exam_id', $examIds)->delete();
        }
        if ($quizIds->isNotEmpty()) {
            \Illuminate\Support\Facades\DB::table('quiz_answers')->whereIn('lesson_content_id', $quizIds)->delete();
        }

        // 4. RESET Enrollments (Do not delete them!)
        \Illuminate\Support\Facades\DB::table('enrollments')
            ->where('material_id', $material->id)
            ->update([
                'status' => 'in_progress',
                'progress_data' => json_encode(['lesson' => 0, 'content' => 0, 'highest_unlocked' => 0]),
                'completed_at' => null,
                'updated_at' => now()
            ]);

        // 5. Notify the students who were enrolled
        if ($enrolledStudentIds->isNotEmpty()) {
            $studentsToNotify = \App\Models\User::whereIn('id', $enrolledStudentIds)->get();

            foreach ($studentsToNotify as $student) {
                $student->notify(new \App\Notifications\LmsAlertNotification(
                    'Module Progress Reset',
                    'The module "' . $material->title . '" has been reverted to draft and your progress has been reset by the instructor.',
                    '#',
                    'fas fa-tools',
                    'text-amber-600'
                ));
            }
        }
    }

    /**
     * Bulk send invitations ONLY to pending students.
     */

    public function studentResult($id, $student_id)
    {
        $material = Material::findOrFail($id);
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'cid' && $material->instructor_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $student = \App\Models\User::findOrFail($student_id);

        $hasExams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->exists();
        $quizTypes = ['mcq', 'checkbox', 'true_false', 'text'];
        $hasQuizzes = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lessons.id', '=', 'lesson_contents.lesson_id')
            ->where('lessons.material_id', $material->id)
            ->whereIn('lesson_contents.type', $quizTypes)
            ->exists();

        $score = 0;
        $totalQuestions = 0;
        
        $quizScore = 0;
        $quizTotalQuestions = 0;
        $examScore = 0;
        $examTotalQuestions = 0;

        $quizLessons = [];   // array of {title, items[]}
        $examItems = [];     // flat array of items

        if ($hasQuizzes) {
            $lessons = \App\Models\Lesson::where('material_id', $material->id)->orderBy('sort_order')->get();
            foreach ($lessons as $lesson) {
                $quizzes = \App\Models\LessonContent::where('lesson_id', $lesson->id)
                    ->whereIn('type', $quizTypes)
                    ->orderBy('sort_order')->get();
                if ($quizzes->isEmpty()) continue;

                $lessonItems = [];

                foreach ($quizzes as $q) {
                    $totalQuestions++;
                    $quizTotalQuestions++;
                    $ans = \App\Models\QuizAnswer::where('user_id', $student->id)->where('lesson_content_id', $q->id)->first();
                    
                    $isCorrect = $ans ? (bool)$ans->is_correct : false;
                    if ($isCorrect) {
                        $score++;
                        $quizScore++;
                    }

                    $questionObj = (object)[
                        'type' => $q->type,
                        'question_text' => strip_tags($q->question_text)
                    ];

                    $studentAnswerText = 'No answer provided';
                    if ($ans) {
                        if ($ans->text_answer) {
                            if ($q->type === 'checkbox') {
                                $selectedIds = array_filter(array_map('trim', explode(',', $ans->text_answer)));
                                $opts = \App\Models\QuizOption::whereIn('id', $selectedIds)->get();
                                $studentAnswerText = $opts->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(', ');
                            } else {
                                $studentAnswerText = $ans->text_answer;
                            }
                        } elseif ($ans->quiz_option_id) {
                            $opt = \App\Models\QuizOption::find($ans->quiz_option_id);
                            $studentAnswerText = $opt ? strip_tags($opt->option_text) : 'Selected Option';
                        }
                    }

                    $correctOptions = \App\Models\QuizOption::where('quiz_id', $q->id)->where('is_correct', true)->get();
                    $correctAnswerText = $correctOptions->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(', ');

                    $lessonItems[] = (object) [
                        'question' => clone $questionObj,
                        'is_correct' => $isCorrect,
                        'is_pending' => false,
                        'student_answer_text' => $studentAnswerText,
                        'correct_answer_text' => $correctAnswerText,
                    ];
                }
                $quizLessons[] = (object) ['title' => $lesson->title, 'items' => $lessonItems];
            }
        }

        if ($hasExams) {
            $exams = \App\Models\Exam::where('material_id', $material->id)->orderBy('sort_order')->get();
            foreach ($exams as $e) {
                $totalQuestions++;
                $examTotalQuestions++;
                $ans = \App\Models\ExamAnswer::where('user_id', $student->id)->where('exam_id', $e->id)->first();
                
                $isCorrect = $ans ? (bool)$ans->is_correct : false;
                if ($isCorrect) {
                    $score++;
                    $examScore++;
                }

                $questionObj = (object)[
                    'type' => $e->type,
                    'question_text' => strip_tags($e->question_text)
                ];

                $studentAnswerText = 'No answer provided';
                if ($ans) {
                    if ($ans->text_answer) {
                        if ($e->type === 'checkbox') {
                            $selectedIds = array_filter(array_map('trim', explode(',', $ans->text_answer)));
                            $opts = \App\Models\ExamOption::whereIn('id', $selectedIds)->get();
                            $studentAnswerText = $opts->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(', ');
                        } else {
                            $studentAnswerText = $ans->text_answer;
                        }
                    } elseif ($ans->exam_option_id) {
                        $opt = \App\Models\ExamOption::find($ans->exam_option_id);
                        $studentAnswerText = $opt ? strip_tags($opt->option_text) : 'Selected Option';
                    }
                }

                $correctOptions = \App\Models\ExamOption::where('exam_id', $e->id)->where('is_correct', true)->get();
                $correctAnswerText = $correctOptions->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(', ');

                $examItems[] = (object) [
                    'question' => clone $questionObj,
                    'is_correct' => $isCorrect,
                    'is_pending' => false,
                    'student_answer_text' => $studentAnswerText,
                    'correct_answer_text' => $correctAnswerText,
                ];
            }
        }

        $assessment = (object)[
            'title' => $material->title,
        ];

        $examWeight = $material->exam_weight ?? 60;
        $quizWeight = 100 - $examWeight;

        $qPct = $quizTotalQuestions > 0 ? ($quizScore / $quizTotalQuestions) * 100 : 100;
        $ePct = $examTotalQuestions > 0 ? ($examScore / $examTotalQuestions) * 100 : 100;

        if (!$hasExams && !$hasQuizzes) {
            $finalPercentage = 0;
        } elseif ($hasQuizzes && $hasExams) {
            $finalPercentage = ($qPct * ($quizWeight / 100)) + ($ePct * ($examWeight / 100));
        } elseif ($hasQuizzes) {
            $finalPercentage = $qPct;
        } else {
            $finalPercentage = $ePct;
        }

        $finalPercentage = round($finalPercentage);

        return view('dashboard.partials.student.assessmentExam.student-result', compact(
            'assessment', 'score', 'totalQuestions', 'quizLessons', 'examItems',
            'hasQuizzes', 'hasExams', 'student', 'material', 'finalPercentage',
            'quizScore', 'quizTotalQuestions', 'examScore', 'examTotalQuestions',
            'quizWeight', 'examWeight'
        ));
    }

    public function exportStudents(Request $request, $id)
    {
        $material = Material::findOrFail($id);
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'cid' && $material->instructor_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $exportType = $request->input('export_type', 'info');
        $singleStudentId = $request->input('student_id');

        $hasExams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->exists();
        $quizTypes = ['mcq', 'checkbox', 'true_false', 'text'];
        $hasQuizzes = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lessons.id', '=', 'lesson_contents.lesson_id')
            ->where('lessons.material_id', $material->id)
            ->whereIn('lesson_contents.type', $quizTypes)
            ->exists();

        $exportType = $request->input('export_type', 'summary'); // summary | detailed
        $service    = new \App\Services\MaterialExportService();

        if ($exportType === 'detailed') {
            if (!$hasExams && !$hasQuizzes) {
                abort(400, 'No assessments found for detailed export.');
            }

            $spreadsheet = $service->exportDetailedAsExcel($material);
            $filename    = 'detailed_report_' . date('Ymd_His') . '.xlsx';

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'no-cache, no-store, must-revalidate',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // --- Summary (CSV) ---
        $data     = $service->exportSummaryAsCsv($material);
        $filename = 'summary_report_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
            fputcsv($file, $data['headers']);
            foreach ($data['rows'] as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }
}