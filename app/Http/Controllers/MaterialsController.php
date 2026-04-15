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

    public function evaluateMaterial(Material $material)
    {
        // 1. Security Check: Only allow Admins to access the evaluation mode
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access. Only Admins can evaluate materials.');
        }

        // 2. Eager load all the necessary relationships needed for the Evaluation View
        // We load instructor, tags, lessons, and all options to display the answer keys
        $material->load([
            'instructor',
            'tags',
            'lessons' => function ($query) {
                $query->orderBy('sort_order', 'asc'); // Ensure lessons are in the correct order
            },
            'lessons.contents.options',
            'exams.options'
        ]);

        // 3. Return the specific evaluate view
        return view('dashboard.partials.admin.evaluate-material', compact('material'));
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

        // If the user is an admin or superadmin, load the Admin Index
        if (in_array($user->role, ['admin', 'superadmin'])) {
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

        // 3. Attach the enrollment data to the access object
        foreach ($whitelistedStudents as $access) {
            $access->current_enrollment = null;
            if ($access->student && $enrollments->has($access->student->id)) {
                $access->current_enrollment = $enrollments->get($access->student->id);
            }
        }

        return view('dashboard.partials.shared.materials-manage', compact('material', 'whitelistedStudents'));
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
                route('dashboard.materials.show', $material->id),
                'fas fa-unlock-alt',
                'text-blue-600'
            ));
        }

        return response()->json(['success' => true, 'message' => 'Student added successfully!']);
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
                'status' => 'required|in:draft,pending,published'
            ]);

            $targetStatus = $request->status;

            // Fetch the material BEFORE updating so we can check ownership and titles
            $material = \App\Models\Material::with('instructor')->findOrFail($id);

            if (auth()->user()->role === 'teacher' && $targetStatus === 'published') {
                $targetStatus = 'pending';
            }

            // 2. Prepare the database update array
            $updateData = [
                'status' => $targetStatus,
                'updated_at' => now()
            ];

            // 3. If the Admin is evaluating, capture the Rubric Breakdown & Remarks
            if ($request->has('evaluation_details')) {
                $updateData['admin_remarks'] = $request->admin_remarks;
                $updateData['evaluation_json'] = json_encode([
                    'score_percentage' => $request->score_percentage,
                    'details' => $request->evaluation_details
                ]);
            }

            \Illuminate\Support\Facades\DB::table('materials')
                ->where('id', $id)
                ->update($updateData);

            // 4. --- NOTIFICATION LOGIC ---
            // If the user making the change is NOT the owner of the material, send a notification
            if (auth()->id() !== $material->instructor_id && $material->instructor) {
                $notifTitle = '';
                $notifMessage = '';
                $notifIcon = 'fas fa-info-circle';
                $notifColor = 'text-blue-600';

                // Customize notification based on the target status
                if ($targetStatus === 'published') {
                    $notifTitle = 'Module Approved!';
                    $notifMessage = 'Congratulations! Your module "' . $material->title . '" has been evaluated and published.';
                    $notifIcon = 'fas fa-check-circle';
                    $notifColor = 'text-green-600';
                } elseif ($targetStatus === 'draft' && $request->has('evaluation_details')) {
                    $notifTitle = 'Module Revision Required';
                    $notifMessage = 'Your module "' . $material->title . '" was returned to Draft. Please review the evaluation remarks.';
                    $notifIcon = 'fas fa-undo';
                    $notifColor = 'text-red-600';
                } elseif ($targetStatus === 'draft') {
                    $notifTitle = 'Module Reverted to Draft';
                    $notifMessage = 'An Admin reverted your module "' . $material->title . '" to Draft mode.';
                    $notifIcon = 'fas fa-archive';
                    $notifColor = 'text-amber-600';
                }

                // Fire the notification if a relevant status change occurred
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
                $statusText = 'Submitted for Admin Approval';

            return response()->json([
                'success' => true,
                'new_status' => $targetStatus,
                'message' => 'Module is now ' . $statusText,
                // Redirect Admin to the new evaluation results page
                'redirect_url' => auth()->user()->role === 'admin' && $request->has('evaluation_details')
                    ? route('dashboard.materials.evaluation-result', $id)
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
    public function evaluationResult($id)
    {
        $material = Material::with('instructor')->findOrFail($id);

        // Decode the JSON so the Blade view can loop through it
        $evaluationData = $material->evaluation_json ? json_decode($material->evaluation_json, true) : null;

        return view('dashboard.partials.shared.evaluation-result', compact('material', 'evaluationData'));
    }

    public function importAccess(Request $request, $id)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:2048']);

        try {
            $existingEmails = \App\Models\MaterialAccess::where('material_id', $id)->pluck('email')->toArray();

            \Maatwebsite\Excel\Facades\Excel::import(new EmailMaterialsAccessImport($id), $request->file('file'));

            $newlyAddedAccesses = \App\Models\MaterialAccess::where('material_id', $id)
                ->whereNotIn('email', $existingEmails)
                ->get();

            $material = \App\Models\Material::find($id);

            // UPDATED: Grab full name
            $instructor = auth()->user();
            $instructorName = trim(($instructor->first_name ?? '') . ' ' . ($instructor->last_name ?? '')) ?: 'An Instructor';

            foreach ($newlyAddedAccesses as $access) {
                $student = \App\Models\User::where('email', $access->email)->first();

                if ($student && $material) {
                    $student->notify(new \App\Notifications\LmsAlertNotification(
                        'New Module Access',
                        $instructorName . ' granted you access to a private module: "' . $material->title . '".',
                        route('dashboard.materials.show', $material->id),
                        'fas fa-unlock-alt',
                        'text-blue-600'
                    ));
                }
            }

            return response()->json(['success' => true, 'message' => 'List imported successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import failed. Check if your file has an "email" header. Error: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request, $id)
    {
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
                            'media_name' => $q['media_name'] ?? null, // <--- ADDED HERE
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
                            'media_name' => $q['media_name'] ?? null, // <--- ADDED HERE
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


    public function downloadTemplate()
    {
        return Excel::download(new MaterialTemplateExport, 'module_template.xlsx');
    }

    public function importLessons(Request $request, $id)
    {
        $request->validate(['module_file' => 'required|mimes:xlsx,csv,xls|max:5120']);

        DB::beginTransaction();
        try {
            DB::table('materials')->where('id', $id)->update([
                'title' => $request->title ?? 'Untitled Material',
                'description' => $request->description ?? '',
                'status' => 'draft',
                'draft_json' => null,
                'updated_at' => now()
            ]);

            if ($request->has('categories')) {
                $categories = json_decode($request->categories, true);

                foreach ($categories as $cat) {
                    $sectionType = $cat['section_type'] ?? 'lesson';

                    if ($sectionType === 'exam') {
                        foreach ($cat['questions'] ?? [] as $q) {
                            $examId = DB::table('exams')->insertGetId([
                                'material_id' => $id,
                                'type' => $q['type'] ?? 'mcq',
                                'question_text' => $q['text'] ?? '',
                                'media_url' => $q['media_url'] ?? null,
                                'media_name' => $q['media_name'] ?? null, // <--- ADDED HERE
                                'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            foreach ($q['options'] ?? [] as $opt) {
                                DB::table('exam_options')->insert([
                                    'exam_id' => $examId,
                                    'option_text' => $opt['text'] ?? '',
                                    'is_correct' => $opt['is_correct'] ?? false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    } else {
                        $lessonId = DB::table('lessons')->insertGetId([
                            'material_id' => $id,
                            'title' => $cat['title'] ?? 'New Lesson',
                            'section_type' => 'lesson',
                            'time_limit' => $cat['time_limit'] ?? 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        foreach ($cat['questions'] ?? [] as $q) {
                            $quizId = DB::table('lesson_contents')->insertGetId([
                                'lesson_id' => $lessonId,
                                'type' => $q['type'] ?? 'mcq',
                                'question_text' => $q['text'] ?? '',
                                'media_url' => $q['media_url'] ?? null,
                                'media_name' => $q['media_name'] ?? null, // <--- ADDED HERE
                                'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            foreach ($q['options'] ?? [] as $opt) {
                                DB::table('quiz_options')->insert([
                                    'quiz_id' => $quizId,
                                    'option_text' => $opt['text'] ?? '',
                                    'is_correct' => $opt['is_correct'] ?? false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            Excel::import(new LessonImport($id), $request->file('module_file'));
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Lessons imported successfully!']);
        } catch (Exception $e) {
            DB::RollBack();
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating visibility: ' . $e->getMessage()], 500);
        }
    }

    public function sendIndividualInvite(Request $request, $accessId)
    {
        try {
            $access = MaterialAccess::with('material')->findOrFail($accessId);

            // Send the Email
            Mail::to($access->email)->send(new MaterialInvitationMail($access->material, $access->email));

            // Update the status to 'invited'
            $access->update(['status' => 'invited']);

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invite: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk send invitations to all pending/invited students.
     */
    public function notifyStudents(Request $request, $id)
    {
        try {
            $material = Material::findOrFail($id);

            // Targeted students: those who are 'pending' or have already been 'invited'
            $targets = MaterialAccess::where('material_id', $material->id)
                ->whereIn('status', ['pending', 'invited'])
                ->get();

            if ($targets->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No students to invite.']);
            }

            foreach ($targets as $access) {
                Mail::to($access->email)->send(new MaterialInvitationMail($material, $access->email));

                // This ensures their status moves to 'invited', 
                // which triggers the "Send Again" text in your Blade file.
                $access->update(['status' => 'invited']);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully sent invitations to {$targets->count()} student(s)."
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

    public function show(Material $material)
    {
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

        // 1. Check Private Material Access
        if (!$material->is_public) {
            $access = \App\Models\MaterialAccess::where('material_id', $material->id)
                ->where('email', $user->email)
                ->first();

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

    public function study(Material $material)
    {
        $user = auth()->user();

        // 1. Fetch Enrollment to get Saved Progress
        $enrollment = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment && $user->role === 'student') {
            abort(403, 'You must enroll in this material before studying.');
        }

        // Decode the JSON so the Blade file can read it
        $savedProgress = $enrollment && $enrollment->progress_data
            ? json_decode($enrollment->progress_data)
            : null;

        // LOAD LESSONS, CONTENTS, AND EXAMS
        $material->load([
            'lessons' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }
            ,
            'lessons.contents.options',
            'exams.options'
        ]);

        // Pass $savedProgress to the view
        return view('dashboard.partials.student.materials-study', compact('material', 'savedProgress'));
    }


    public function unenroll(Request $request, Material $material)
    {
        $user = auth()->user();

        DB::beginTransaction();
        try {
            // 1. Delete all Quiz and Exam Answers to clear their progress
            $examIds = DB::table('exams')->where('material_id', $material->id)->pluck('id');
            $quizIds = DB::table('lesson_contents')
                ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
                ->where('lessons.material_id', $material->id)
                ->pluck('lesson_contents.id');

            QuizAnswer::where('user_id', $user->id)->whereIn('lesson_content_id', $quizIds)->delete();
            ExamAnswer::where('user_id', $user->id)->whereIn('exam_id', $examIds)->delete();

            // Delete their enrollment to kick them out of the module
            Enrollment::where('material_id', $material->id)
                ->where('user_id', $user->id)
                ->delete();

            // 3. Mark their MaterialAccess record as 'dropped'
            MaterialAccess::where('material_id', $material->id)
                ->where('email', $user->email)
                ->update(['status' => 'dropped']);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Successfully dropped the course.'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to drop course: ' . $e->getMessage()
            ], 500);
        }
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
        $grades = $this->calculateGrades($material, $user);

        $enrollment = \App\Models\Enrollment::where('material_id', $material->id)->where('user_id', $user->id)->firstOrFail();

        // Push progress to max so it shows 100% on the show page
        $totalLessons = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->count();
        $hasExams = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->exists();
        $totalTimelineCount = $totalLessons + ($hasExams ? 1 : 0);

        if ($grades['passed']) {
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'progress_data' => json_encode(['lesson' => $totalTimelineCount - 1, 'content' => 0, 'highest_unlocked' => $totalTimelineCount])
            ]);

            $user->notify(new \App\Notifications\LmsAlertNotification(
                'Certificate Unlocked!',
                'Congratulations! You passed "' . $material->title . '" with a score of ' . $grades['totalScore'] . '% and earned your certificate.',
                route('dashboard.materials.certificate', $material->id),
                'fas fa-trophy',
                'text-yellow-500' // Golden color for the trophy
            ));

            return response()->json(['success' => true, 'passed' => true, 'redirect_url' => route('dashboard.materials.certificate', $material->id)]);
        } else {
            $enrollment->update([
                'status' => 'failed',
                'progress_data' => json_encode(['lesson' => $totalTimelineCount - 1, 'content' => 0, 'highest_unlocked' => $totalTimelineCount])
            ]);
            return response()->json(['success' => true, 'passed' => false, 'redirect_url' => route('dashboard.materials.result', $material->id)]);
        }
    }

    public function result(\App\Models\Material $material)
    {
        $user = auth()->user();
        $grades = $this->calculateGrades($material, $user);

        // Calculate if a perfect exam score is mathematically enough to pass
        $maxPossibleScore = ($grades['quizScore'] * ($grades['quizWeight'] / 100)) + (100 * ($grades['examWeight'] / 100));
        $canPassWithExamRetake = $maxPossibleScore >= $grades['passingScore'];

        return view('dashboard.partials.student.materials-result', compact('material', 'grades', 'canPassWithExamRetake', 'maxPossibleScore'));
    }

    public function retake(Request $request, \App\Models\Material $material)
    {
        $user = auth()->user();
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

            \App\Models\Enrollment::where('material_id', $material->id)->where('user_id', $user->id)->update([
                'progress_data' => json_encode(['lesson' => 0, 'content' => 0, 'highest_unlocked' => 0]),
                'status' => 'in_progress',
                'retakes' => \Illuminate\Support\Facades\DB::raw('retakes + 1')
            ]);
        } elseif ($type === 'exam') {
            // Delete EXAMS only
            \App\Models\ExamAnswer::where('user_id', $user->id)->whereIn('exam_id', $examIds)->delete();

            // Jump back to the start of the Exam section
            $examSectionIndex = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->count();
            \App\Models\Enrollment::where('material_id', $material->id)->where('user_id', $user->id)->update([
                'progress_data' => json_encode(['lesson' => $examSectionIndex, 'content' => 0, 'highest_unlocked' => $examSectionIndex]),
                'status' => 'in_progress',
                'retakes' => \Illuminate\Support\Facades\DB::raw('retakes + 1')
            ]);
        }

        return redirect()->route('dashboard.materials.study', $material->id);
    }

    public function certificate(\App\Models\Material $material)
    {
        $enrollment = \App\Models\Enrollment::with(['user', 'material.instructor'])
            ->where('material_id', $material->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('dashboard.partials.student.certificate-achieved', compact('enrollment'));
    }

    public function getNotifications()
    {
        $user = auth()->user();

        // 1. Fetch ALL notifications from the last 30 days (Both Read and Unread)
        $notifications = $user->notifications()
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->limit(50) // Safe limit to prevent massive dropdowns
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->data['title'],
                    'message' => $notif->data['message'],
                    'url' => $notif->data['url'],
                    'icon' => $notif->data['icon'],
                    'colorClass' => $notif->data['colorClass'],
                    'time_ago' => $notif->created_at->diffForHumans(),
                    'is_read' => $notif->read_at !== null // Tell frontend if it's read
                ];
            });

        // 2. Get the specific count of UNREAD notifications for the red bell badge
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function markNotificationRead($id)
    {
        // Query ALL notifications so we don't get a 404 if it's already read
        $notification = auth()->user()->notifications()->find($id);

        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true, 'message' => 'Already read']);
    }

    public function preview($id)
    {
        $material = Material::with([
            'instructor',        // For the mock certificate
            'lessons.contents',  // For the timeline (Lessons & Quizzes)
            'exams'              // For the timeline (Final Exam)
        ])->findOrFail($id);

        // Optional: Add authorization to ensure only the owner or an admin can preview
        if (auth()->id() !== $material->instructor_id && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Return the preview view (adjust the view path if your folders are structured differently)
        return view('dashboard.partials.shared.materials-preview', compact('material'));
    }

    public function analytics($id)
    {
        $material = \App\Models\Material::findOrFail($id);

        // 1. Enrollment KPIs
        $totalLearners = \App\Models\Enrollment::where('material_id', $material->id)->count();

        $pendingRequests = \App\Models\MaterialAccess::where('material_id', $material->id)
            ->where('status', 'pending')
            ->count();

        $totalDropped = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('status', 'dropped')
            ->count();

        $activeLearners = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('updated_at', '>=', \Carbon\Carbon::now()->subDays(7))
            ->count();

        // 2. Student Progress
        $completedCount = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('status', 'completed')
            ->count();

        $inProgressCount = \App\Models\Enrollment::where('material_id', $material->id)
            ->whereIn('status', ['in_progress', 'failed'])
            ->count();

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

        // 5. Competency Breakdown
        $competencies = [];
        $lessons = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->get();

        foreach ($lessons as $lesson) {
            $lqIds = \Illuminate\Support\Facades\DB::table('lesson_contents')->where('lesson_id', $lesson->id)->where('type', '!=', 'content')->pluck('id');
            $hasQuiz = $lqIds->isNotEmpty();

            $mps = 0;

            if ($hasQuiz) {
                $answers = \Illuminate\Support\Facades\DB::table('quiz_answers')
                    ->whereIn('lesson_content_id', $lqIds);

                $correct = (clone $answers)->where('is_correct', 1)->count();
                $total = $answers->count();

                $mps = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
            }

            $competencies[] = (object) [
                'title' => $lesson->title,
                'has_quiz' => $hasQuiz,
                'mps' => $mps
            ];
        }

        if ($hasExams) {
            // Exam average strictly for the competency row
            $examStats = \Illuminate\Support\Facades\DB::table('exam_answers')
                ->select(
                    'user_id',
                    \Illuminate\Support\Facades\DB::raw('SUM(is_correct = 1) as correct'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total')
                )
                ->whereIn('exam_id', $examIds)
                ->groupBy('user_id')
                ->get();

            $avgExamScoreCompetency = $examStats->count() > 0
                ? round($examStats->avg(fn($e) => $e->total > 0 ? ($e->correct / $e->total) * 100 : 0), 2)
                : 0;

            $competencies[] = (object) [
                'title' => 'Final Exam',
                'has_quiz' => true,
                'mps' => $avgExamScoreCompetency
            ];
        }

        // 6. LEADERBOARD & OVERALL AVERAGES
        $studentLeaderboard = \App\Models\Enrollment::with('user')
            ->where('material_id', $material->id)
            ->get()
            ->map(function ($enrollment) use ($examIds, $quizIds, $lessons) {

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

                $totalCorrect = $quizCorrect + $examCorrect;
                $totalAnswered = $quizTotal + $examTotal;

                $quizScore = $quizTotal > 0 ? round(($quizCorrect / $quizTotal) * 100, 2) : 0;
                $examScore = $examTotal > 0 ? round(($examCorrect / $examTotal) * 100, 2) : 0;
                $score = $totalAnswered > 0 ? round(($totalCorrect / $totalAnswered) * 100, 2) : 0;

                // Progress FIX
                $progData = json_decode($enrollment->progress_data);
                $lessonCount = $lessons->count();

                $prog = $enrollment->status === 'completed'
                    ? 100
                    : (isset($progData->highest_unlocked) && $lessonCount > 0
                        ? min(99, round(($progData->highest_unlocked / ($lessonCount + 1)) * 100))
                        : 0);

                return (object) [
                    'name' => $enrollment->user
                        ? $enrollment->user->first_name . ' ' . $enrollment->user->last_name
                        : 'Unknown Student',
                    'progress' => $prog,
                    'quiz_score' => $quizScore,
                    'exam_score' => $examScore,
                    'quiz_score_raw' => $quizTotal > 0 ? "{$quizCorrect}/{$quizTotal}" : "0/0",
                    'exam_score_raw' => $examTotal > 0 ? "{$examCorrect}/{$examTotal}" : "0/0",
                    'score' => $score
                ];
            })
            ->sortByDesc('score')
            ->take(10)
            ->values();

        // Calculate Overall Averages
        $overallAverage = $studentLeaderboard->count() > 0 ? round($studentLeaderboard->avg('score'), 2) : 0;
        
        $validQuizScores = $studentLeaderboard->filter(function($s) { return $s->quiz_score_raw !== "0/0"; });
        $avgQuizScore = $validQuizScores->count() > 0 ? round($validQuizScores->avg('quiz_score'), 2) : 0;
        
        $validExamScores = $studentLeaderboard->filter(function($s) { return $s->exam_score_raw !== "0/0"; });
        $avgExamScore = $validExamScores->count() > 0 ? round($validExamScores->avg('exam_score'), 2) : 0;

        // 7. Item Analysis
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
                $answers = \Illuminate\Support\Facades\DB::table('quiz_answers')->where('lesson_content_id', $q->id)->get();
                $cCount = $answers->where('is_correct', 1)->count();
                $wCount = $answers->where('is_correct', 0)->count();
                $tCount = $cCount + $wCount;

                $opts = [];
                if ($q->type === 'text') {
                    $responses = $answers->groupBy(function($a) { return strtolower(trim($a->text_answer ?? '')); });
                    foreach ($responses as $text => $group) {
                        $count = $group->count();
                        $isCorrect = $group->where('is_correct', 1)->isNotEmpty();
                        $opts[] = (object) [
                            'text' => $text === '' ? '(Blank)' : $text,
                            'pct' => $tCount > 0 ? round(($count / $tCount) * 100) : 0,
                            'is_correct' => $isCorrect
                        ];
                    }
                    usort($opts, function($a, $b) { return $b->pct <=> $a->pct; }); 
                } else {
                    $options = \Illuminate\Support\Facades\DB::table('quiz_options')->where('quiz_id', $q->id)->get();
                    foreach ($options as $o) {
                        if ($q->type === 'checkbox') {
                            $selCount = $answers->filter(function($a) use ($o) {
                                if (empty($a->text_answer)) return false;
                                $ids = explode(',', $a->text_answer);
                                return in_array((string)$o->id, $ids);
                            })->count();
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
                $answers = \Illuminate\Support\Facades\DB::table('exam_answers')->where('exam_id', $e->id)->get();
                $cCount = $answers->where('is_correct', 1)->count();
                $wCount = $answers->where('is_correct', 0)->count();
                $tCount = $cCount + $wCount;

                $opts = [];
                if ($e->type === 'text') {
                    $responses = $answers->groupBy(function($a) { return strtolower(trim($a->text_answer ?? '')); });
                    foreach ($responses as $text => $group) {
                        $count = $group->count();
                        $isCorrect = $group->where('is_correct', 1)->isNotEmpty();
                        $opts[] = (object) [
                            'text' => $text === '' ? '(Blank)' : $text,
                            'pct' => $tCount > 0 ? round(($count / $tCount) * 100) : 0,
                            'is_correct' => $isCorrect
                        ];
                    }
                    usort($opts, function($a, $b) { return $b->pct <=> $a->pct; }); 
                } else {
                    $options = \Illuminate\Support\Facades\DB::table('exam_options')->where('exam_id', $e->id)->get();
                    foreach ($options as $o) {
                        if ($e->type === 'checkbox') {
                            $selCount = $answers->filter(function($a) use ($o) {
                                if (empty($a->text_answer)) return false;
                                $ids = explode(',', $a->text_answer);
                                return in_array((string)$o->id, $ids);
                            })->count();
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
            'material',
            'totalLearners',
            'activeLearners',
            'pendingRequests',
            'totalDropped',
            'overallAverage',
            'avgQuizScore',
            'avgExamScore',
            'completedCount',
            'inProgressCount',
            'activityDates',
            'activityTrend',
            'competencies',
            'studentLeaderboard',
            'quizItemAnalysis',
            'examItemAnalysis',
            'hasQuizzes',
            'hasExams',
            'quizItemsCount',
            'examItemsCount'
        ));
    }

    public function exportMaterialAnalyticsPdf(Request $request, $id)
{
    // 1. Fetch material & Authorization check
    $material = \App\Models\Material::with('instructor')->findOrFail($id);
    
    if (\Illuminate\Support\Facades\Auth::user()->role === 'teacher' && $material->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    // ========================================================================
    // 2. IMPORTANT: PASTE YOUR DATA FETCHING LOGIC HERE
    //  $material = \App\Models\Material::findOrFail($id);

        // 1. Enrollment KPIs
        $totalLearners = \App\Models\Enrollment::where('material_id', $material->id)->count();

        $pendingRequests = \App\Models\MaterialAccess::where('material_id', $material->id)
            ->where('status', 'pending')
            ->count();

        $totalDropped = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('status', 'dropped')
            ->count();

        $activeLearners = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('updated_at', '>=', \Carbon\Carbon::now()->subDays(7))
            ->count();

        // 2. Student Progress
        $completedCount = \App\Models\Enrollment::where('material_id', $material->id)
            ->where('status', 'completed')
            ->count();

        $inProgressCount = \App\Models\Enrollment::where('material_id', $material->id)
            ->whereIn('status', ['in_progress', 'failed'])
            ->count();

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

        // 5. Competency Breakdown
        $competencies = [];
        $lessons = \Illuminate\Support\Facades\DB::table('lessons')->where('material_id', $material->id)->get();

        foreach ($lessons as $lesson) {
            $lqIds = \Illuminate\Support\Facades\DB::table('lesson_contents')->where('lesson_id', $lesson->id)->where('type', '!=', 'content')->pluck('id');
            $hasQuiz = $lqIds->isNotEmpty();

            $mps = 0;

            if ($hasQuiz) {
                $answers = \Illuminate\Support\Facades\DB::table('quiz_answers')
                    ->whereIn('lesson_content_id', $lqIds);

                $correct = (clone $answers)->where('is_correct', 1)->count();
                $total = $answers->count();

                $mps = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
            }

            $competencies[] = (object) [
                'title' => $lesson->title,
                'has_quiz' => $hasQuiz,
                'mps' => $mps
            ];
        }

        if ($hasExams) {
            // Exam average strictly for the competency row
            $examStats = \Illuminate\Support\Facades\DB::table('exam_answers')
                ->select(
                    'user_id',
                    \Illuminate\Support\Facades\DB::raw('SUM(is_correct = 1) as correct'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total')
                )
                ->whereIn('exam_id', $examIds)
                ->groupBy('user_id')
                ->get();

            $avgExamScoreCompetency = $examStats->count() > 0
                ? round($examStats->avg(fn($e) => $e->total > 0 ? ($e->correct / $e->total) * 100 : 0), 2)
                : 0;

            $competencies[] = (object) [
                'title' => 'Final Exam',
                'has_quiz' => true,
                'mps' => $avgExamScoreCompetency
            ];
        }

        // 6. LEADERBOARD & OVERALL AVERAGES
        $studentLeaderboard = \App\Models\Enrollment::with('user')
            ->where('material_id', $material->id)
            ->get()
            ->map(function ($enrollment) use ($examIds, $quizIds, $lessons) {

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

                $totalCorrect = $quizCorrect + $examCorrect;
                $totalAnswered = $quizTotal + $examTotal;

                $quizScore = $quizTotal > 0 ? round(($quizCorrect / $quizTotal) * 100, 2) : 0;
                $examScore = $examTotal > 0 ? round(($examCorrect / $examTotal) * 100, 2) : 0;
                $score = $totalAnswered > 0 ? round(($totalCorrect / $totalAnswered) * 100, 2) : 0;

                // Progress FIX
                $progData = json_decode($enrollment->progress_data);
                $lessonCount = $lessons->count();

                $prog = $enrollment->status === 'completed'
                    ? 100
                    : (isset($progData->highest_unlocked) && $lessonCount > 0
                        ? min(99, round(($progData->highest_unlocked / ($lessonCount + 1)) * 100))
                        : 0);

                return (object) [
                    'name' => $enrollment->user
                        ? $enrollment->user->first_name . ' ' . $enrollment->user->last_name
                        : 'Unknown Student',
                    'progress' => $prog,
                    'quiz_score' => $quizScore,
                    'exam_score' => $examScore,
                    'quiz_score_raw' => $quizTotal > 0 ? "{$quizCorrect}/{$quizTotal}" : "0/0",
                    'exam_score_raw' => $examTotal > 0 ? "{$examCorrect}/{$examTotal}" : "0/0",
                    'score' => $score
                ];
            })
            ->sortByDesc('score')
            ->take(10)
            ->values();

        // Calculate Overall Averages
        $overallAverage = $studentLeaderboard->count() > 0 ? round($studentLeaderboard->avg('score'), 2) : 0;
        
        $validQuizScores = $studentLeaderboard->filter(function($s) { return $s->quiz_score_raw !== "0/0"; });
        $avgQuizScore = $validQuizScores->count() > 0 ? round($validQuizScores->avg('quiz_score'), 2) : 0;
        
        $validExamScores = $studentLeaderboard->filter(function($s) { return $s->exam_score_raw !== "0/0"; });
        $avgExamScore = $validExamScores->count() > 0 ? round($validExamScores->avg('exam_score'), 2) : 0;

        // 7. Item Analysis
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
                $answers = \Illuminate\Support\Facades\DB::table('quiz_answers')->where('lesson_content_id', $q->id)->get();
                $cCount = $answers->where('is_correct', 1)->count();
                $wCount = $answers->where('is_correct', 0)->count();
                $tCount = $cCount + $wCount;

                $opts = [];
                if ($q->type === 'text') {
                    $responses = $answers->groupBy(function($a) { return strtolower(trim($a->text_answer ?? '')); });
                    foreach ($responses as $text => $group) {
                        $count = $group->count();
                        $isCorrect = $group->where('is_correct', 1)->isNotEmpty();
                        $opts[] = (object) [
                            'text' => $text === '' ? '(Blank)' : $text,
                            'pct' => $tCount > 0 ? round(($count / $tCount) * 100) : 0,
                            'is_correct' => $isCorrect
                        ];
                    }
                    usort($opts, function($a, $b) { return $b->pct <=> $a->pct; }); 
                } else {
                    $options = \Illuminate\Support\Facades\DB::table('quiz_options')->where('quiz_id', $q->id)->get();
                    foreach ($options as $o) {
                        if ($q->type === 'checkbox') {
                            $selCount = $answers->filter(function($a) use ($o) {
                                if (empty($a->text_answer)) return false;
                                $ids = explode(',', $a->text_answer);
                                return in_array((string)$o->id, $ids);
                            })->count();
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
                $answers = \Illuminate\Support\Facades\DB::table('exam_answers')->where('exam_id', $e->id)->get();
                $cCount = $answers->where('is_correct', 1)->count();
                $wCount = $answers->where('is_correct', 0)->count();
                $tCount = $cCount + $wCount;

                $opts = [];
                if ($e->type === 'text') {
                    $responses = $answers->groupBy(function($a) { return strtolower(trim($a->text_answer ?? '')); });
                    foreach ($responses as $text => $group) {
                        $count = $group->count();
                        $isCorrect = $group->where('is_correct', 1)->isNotEmpty();
                        $opts[] = (object) [
                            'text' => $text === '' ? '(Blank)' : $text,
                            'pct' => $tCount > 0 ? round(($count / $tCount) * 100) : 0,
                            'is_correct' => $isCorrect
                        ];
                    }
                    usort($opts, function($a, $b) { return $b->pct <=> $a->pct; }); 
                } else {
                    $options = \Illuminate\Support\Facades\DB::table('exam_options')->where('exam_id', $e->id)->get();
                    foreach ($options as $o) {
                        if ($e->type === 'checkbox') {
                            $selCount = $answers->filter(function($a) use ($o) {
                                if (empty($a->text_answer)) return false;
                                $ids = explode(',', $a->text_answer);
                                return in_array((string)$o->id, $ids);
                            })->count();
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

    // ========================================================================

    // 3. Setup Export Data
    $isPrint = $request->input('action') === 'print';

    $data = [
        'material' => $material,
        
        // Pass your calculated variables here (using ?? fallbacks for safety)
        'totalLearners' => $totalLearners ?? 0,
        'pendingRequests' => $pendingRequests ?? 0,
        'totalDropped' => $totalDropped ?? 0,
        'overallAverage' => $overallAverage ?? 0,
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

        // Checkboxes from the modal form
        'showMetrics' => $request->has('check_metrics'),
        'showCompetency' => $request->has('check_competency'),
        'showItemAnalysis' => $request->has('check_item_analysis'),
        
        'isPrint' => $isPrint,
    ];

    // 4. Return Print View or Download PDF
    if ($isPrint) {
        return view('dashboard.partials.shared.materials-report', $data);
    }

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.teacher.materials-report', $data);
    return $pdf->download('Material_Analytics_' . \Illuminate\Support\Str::slug($material->title) . '_' . now()->format('Y_m_d') . '.pdf');
}
}