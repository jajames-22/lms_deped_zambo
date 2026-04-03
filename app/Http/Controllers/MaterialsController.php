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
        $materials = Material::with('instructor')
            ->withCount([
                'lessons' => function ($query) {
                    $query->where('section_type', 'lesson');
                }
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dashboard.partials.admin.materials', compact('materials'));
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

        // THE FIX: Query the new MaterialAccess table instead of Enrollment
        $whitelistedStudents = \App\Models\MaterialAccess::with('student')
            ->where('material_id', $material->id)
            ->latest()
            ->get();

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

            // 2. If the student has already registered an account and enrolled,
            // delete their enrollment record to remove them from the class and clear progress.
            if ($access->student_id) {
                Enrollment::where('material_id', $access->material_id)
                    ->where('user_id', $access->student_id)
                    ->delete();
            }

            // 3. Delete from the MaterialAccess table (the VIP list)
            $access->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Student access and enrollment revoked successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error revoking access: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $material = DB::table('materials')->where('id', $id)->first();
            $newStatus = $material->status === 'published' ? 'draft' : 'published';

            DB::table('materials')
                ->where('id', $id)
                ->update(['status' => $newStatus, 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'new_status' => $newStatus,
                'message' => 'Module is now ' . ($newStatus === 'published' ? 'Published' : 'in Draft Mode')
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()], 500);
        }
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

            // Tracking arrays to know what NOT to delete
            $keptLessonIds = [];
            $keptQuizIds = [];
            $keptQuizOptionIds = [];
            $keptExamIds = [];
            $keptExamOptionIds = [];

            $hasProcessedExam = false; // Restricts to 1 Exam

            // 1. PROCESS AND UPSERT
            foreach ($categories as $index => $cat) {
                $sectionType = $cat['section_type'] ?? ($cat['type'] ?? 'lesson');

                if ($sectionType === 'exam') {
                    if ($hasProcessedExam)
                        continue; // Skip if user hacked UI to send multiple exams
                    $hasProcessedExam = true;

                    foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                        $examId = isset($q['id']) && is_numeric($q['id']) ? $q['id'] : null;

                        $examData = [
                            'material_id' => $id,
                            'type' => $q['type'] ?? 'mcq',
                            'question_text' => $q['text'] ?? '',
                            'media_url' => $q['media_url'] ?? null,
                            'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                            'sort_order' => $qIndex + 1, // Map sort order
                            'updated_at' => now(),
                        ];

                        if ($examId && DB::table('exams')->where('id', $examId)->exists()) {
                            DB::table('exams')->where('id', $examId)->update($examData);
                        } else {
                            $examData['created_at'] = now();
                            $examId = DB::table('exams')->insertGetId($examData);
                        }
                        $keptExamIds[] = $examId;

                        foreach ($q['options'] ?? [] as $opt) {
                            $optId = isset($opt['id']) && is_numeric($opt['id']) ? $opt['id'] : null;
                            $optData = [
                                'exam_id' => $examId,
                                'option_text' => $opt['text'] ?? '',
                                'is_correct' => $opt['is_correct'] ?? false,
                                'updated_at' => now(),
                            ];

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
                    $lessonId = isset($cat['id']) && is_numeric($cat['id']) ? $cat['id'] : null;

                    $lessonData = [
                        'material_id' => $id,
                        'section_type' => 'lesson',
                        'title' => $cat['title'] ?? 'New Lesson',
                        'time_limit' => $cat['time_limit'] ?? 0,
                        'sort_order' => $index + 1, // Map sort order
                        'updated_at' => now(),
                    ];

                    if ($lessonId && DB::table('lessons')->where('id', $lessonId)->exists()) {
                        DB::table('lessons')->where('id', $lessonId)->update($lessonData);
                    } else {
                        $lessonData['created_at'] = now();
                        $lessonId = DB::table('lessons')->insertGetId($lessonData);
                    }
                    $keptLessonIds[] = $lessonId;

                    foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                        $quizId = isset($q['id']) && is_numeric($q['id']) ? $q['id'] : null;

                        $quizData = [
                            'lesson_id' => $lessonId,
                            'type' => $q['type'] ?? 'mcq',
                            'question_text' => $q['text'] ?? '',
                            'media_url' => $q['media_url'] ?? null,
                            'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                            'sort_order' => $qIndex + 1, // Map sort order
                            'updated_at' => now(),
                        ];

                        if ($quizId && DB::table('lesson_contents')->where('id', $quizId)->exists()) {
                            DB::table('lesson_contents')->where('id', $quizId)->update($quizData);
                        } else {
                            $quizData['created_at'] = now();
                            $quizId = DB::table('lesson_contents')->insertGetId($quizData);
                        }
                        $keptQuizIds[] = $quizId;

                        foreach ($q['options'] ?? [] as $opt) {
                            $optId = isset($opt['id']) && is_numeric($opt['id']) ? $opt['id'] : null;
                            $optData = [
                                'quiz_id' => $quizId,
                                'option_text' => $opt['text'] ?? '',
                                'is_correct' => $opt['is_correct'] ?? false,
                                'updated_at' => now(),
                            ];

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

            // 2. CLEANUP (Delete items removed from the builder)

            // Cleanup Exams
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

            // Cleanup Lessons
            $allLessonIdsForMaterial = DB::table('lessons')->where('material_id', $id)->pluck('id');

            if ($allLessonIdsForMaterial->isNotEmpty()) {
                DB::table('quiz_options')
                    ->whereIn('quiz_id', function ($query) use ($allLessonIdsForMaterial) {
                        $query->select('id')->from('lesson_contents')
                            ->whereIn('lesson_id', $allLessonIdsForMaterial);
                    })
                    ->whereNotIn('id', $keptQuizOptionIds)
                    ->delete();

                DB::table('lesson_contents')
                    ->whereIn('lesson_id', $allLessonIdsForMaterial)
                    ->whereNotIn('id', $keptQuizIds)
                    ->delete();
            }

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
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');

            // Capture the original name before saving
            $originalName = $file->getClientOriginalName();

            // Save the file (this usually generates a new unique name like xyz123.pdf)
            $path = $file->store('materials_media', 'public');
            $url = asset('storage/' . $path);

            // This is the JSON response your JavaScript receives
            return response()->json([
                'success' => true,
                'media_url' => $url,
                'media_type' => $file->getClientOriginalExtension(),
                'original_name' => $originalName, // ADD THIS LINE
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    public function destroy($id)
    {
        try {
            $lessonIds = DB::table('lessons')->where('material_id', $id)->pluck('id');

            if ($lessonIds->isNotEmpty()) {
                // FIX: Changed 'quizzes' to 'lesson_contents'
                $quizIds = DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->pluck('id');

                if ($quizIds->isNotEmpty()) {
                    DB::table('quiz_options')->whereIn('quiz_id', $quizIds)->delete();
                }

                // FIX: Changed 'quizzes' to 'lesson_contents'
                DB::table('lesson_contents')->whereIn('lesson_id', $lessonIds)->delete();
                DB::table('lessons')->where('material_id', $id)->delete();
            }

            DB::table('enrollments')->where('material_id', $id)->delete();
            DB::table('materials')->where('id', $id)->delete();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
                            $quizId = DB::table('quizzes')->insertGetId([
                                'lesson_id' => $lessonId,
                                'type' => $q['type'] ?? 'mcq',
                                'question_text' => $q['text'] ?? '',
                                'media_url' => $q['media_url'] ?? null,
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
        $isEnrolled = false;
        if (auth()->user()->role === 'student') {
            $isEnrolled = Enrollment::where('material_id', $material->id)
                ->where('user_id', auth()->id())
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
            $access = MaterialAccess::where('material_id', $material->id)
                ->where('email', $user->email)
                ->first();

            if (!$access) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to enroll in this private material.'
                ], 403);
            }

            // Update their invitation status to enrolled
            if ($access->status !== 'enrolled') {
                $access->update(['status' => 'enrolled']);
            }
        }

        // 2. Check for Duplicate Enrollment
        $alreadyEnrolled = Enrollment::where('material_id', $material->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this material.'
            ]);
        }

        // 3. Create the New Enrollment
        Enrollment::create([
            'material_id' => $material->id,
            'user_id' => $user->id,
            'status' => 'in_progress' // Setting the default status based on your schema
        ]);

        // Return a success JSON response to trigger the frontend animation
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

        // 1. Delete all Quiz and Exam Answers for this specific material
        $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->pluck('id');
        $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
            ->where('lessons.material_id', $material->id)
            ->pluck('lesson_contents.id');

        \App\Models\QuizAnswer::where('user_id', $user->id)->whereIn('lesson_content_id', $quizIds)->delete();
        \App\Models\ExamAnswer::where('user_id', $user->id)->whereIn('exam_id', $examIds)->delete();

        // 2. Find and delete the enrollment record
        $enrollment = Enrollment::where('material_id', $material->id)
            ->where('user_id', $user->id)
            ->first();

        if ($enrollment) {
            $enrollment->delete();
        }

        // 3. If it's a private material, revert their access status back to pending/invited
        if (!$material->is_public) {
            $access = \App\Models\MaterialAccess::where('material_id', $material->id)
                ->where('email', $user->email)
                ->first();

            if ($access && $access->status === 'enrolled') {
                // Change it back to 'pending' so they can re-enroll later if they want
                $access->update(['status' => 'pending']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully dropped the course and cleared previous progress.'
        ]);
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
        
        if ($hasExams && !$hasQuizzes) { $examWeight = 100; }
        elseif (!$hasExams && $hasQuizzes) { $examWeight = 0; }
        elseif (!$hasExams && !$hasQuizzes) { $examWeight = 0; }
        
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
            ->map(function($notif) {
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
}