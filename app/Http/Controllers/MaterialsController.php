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

        $lessons = DB::table('lessons')
            ->where('material_id', $id)
            ->get()
            ->map(function ($lesson) {
                $lesson->questions = DB::table('quizzes')
                    ->where('lesson_id', $lesson->id)
                    ->get()
                    ->map(function ($quiz) {
                        $quiz->options = DB::table('quiz_options')
                            ->where('quiz_id', $quiz->id)
                            ->get();
                        return $quiz;
                    });
                return $lesson;
            });

        $isNew = false;

        // UPDATED: Pointing to shared folder
        return view('dashboard.partials.shared.materials-create', compact('material', 'lessons', 'isNew'));
    }

    public function manage($id)
    {
        $material = Material::findOrFail($id);

        $material->lessons_count = DB::table('lessons')
            ->where('material_id', $id) // Note: Make sure your DB column is actually material_id here and not material_id
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

        // Prevent duplicate invites by checking the NEW MaterialAccess table
        if (MaterialAccess::where('material_id', $id)->where('email', $email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email is already in the access list.']);
        }

        // Create the record in the new material_accesses table
        // As you requested, it always defaults to 'pending'
        MaterialAccess::create([
            'material_id' => $id,
            'email' => $email,
            'status' => 'pending'
        ]);

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
            // NOTE: You will also need to update this Import class to read the 'email' column instead of 'lrn'
            Excel::import(new EmailMaterialsAccessImport($id), $request->file('file'));
            return response()->json(['success' => true, 'message' => 'List imported successfully!']);
        } catch (Exception $e) {
            // CHANGED: Updated error message hint
            return response()->json(['success' => false, 'message' => 'Import failed. Check if your file has an "email" header.'], 500);
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

            // 1. CLEANUP OLD LESSONS AND QUIZZES
            $existingLessons = DB::table('lessons')->where('material_id', $id)->pluck('id');
            if ($existingLessons->isNotEmpty()) {
                $existingQuizzes = DB::table('quizzes')->whereIn('lesson_id', $existingLessons)->pluck('id');

                if ($existingQuizzes->isNotEmpty()) {
                    DB::table('quiz_options')->whereIn('quiz_id', $existingQuizzes)->delete();
                }

                DB::table('quizzes')->whereIn('lesson_id', $existingLessons)->delete();
                DB::table('lessons')->where('material_id', $id)->delete();
            }

            // 2. CLEANUP OLD EXAMS
            $existingExams = DB::table('exams')->where('material_id', $id)->pluck('id');
            if ($existingExams->isNotEmpty()) {
                DB::table('exam_options')->whereIn('id', $existingExams)->delete();
                DB::table('exams')->where('material_id', $id)->delete();
            }

            // 3. INSERT NEW DATA TO PROPER TABLES
            foreach ($categories as $cat) {
                $sectionType = $cat['section_type'] ?? ($cat['type'] ?? 'lesson');

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

                        $options = $q['options'] ?? [];
                        foreach ($options as $opt) {
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
                        'section_type' => 'lesson',
                        'title' => $cat['title'] ?? 'New Lesson',
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

                        $options = $q['options'] ?? [];
                        foreach ($options as $opt) {
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
            'media_file' => 'required|file|mimes:jpeg,png,jpg,gif,mp3,wav,mp4,webm,pdf,ppt,pptx,zip|max:51200',
        ]);

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $path = $file->store('materials_media', 'public');

            $ext = strtolower($file->getClientOriginalExtension());

            $type = 'image';
            if (in_array($ext, ['mp4', 'webm']))
                $type = 'video';
            if (in_array($ext, ['mp3', 'wav', 'ogg']))
                $type = 'audio';
            if (in_array($ext, ['pdf', 'ppt', 'pptx', 'zip']))
                $type = 'document';

            return response()->json([
                'success' => true,
                'media_url' => asset('storage/' . $path),
                'media_type' => $type
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No media uploaded.'], 400);
    }

    public function destroy($id)
    {
        try {
            $lessonIds = DB::table('lessons')->where('material_id', $id)->pluck('id');

            if ($lessonIds->isNotEmpty()) {
                $quizIds = DB::table('quizzes')->whereIn('lesson_id', $lessonIds)->pluck('id');

                if ($quizIds->isNotEmpty()) {
                    DB::table('quiz_options')->whereIn('quiz_id', $quizIds)->delete();
                }

                DB::table('quizzes')->whereIn('lesson_id', $lessonIds)->delete();
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
                $query->orderBy('created_at', 'asc');
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
}

