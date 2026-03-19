<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LessonImport;
use App\Imports\LrnMaterialAccessImport;
use App\Exports\MaterialTemplateExport;
use Exception;

class MaterialsController extends Controller
{
    public function index()
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

    public function create()
    {
        $materialId = DB::table('materials')->insertGetId([
            'title' => 'Untitled Material',
            'description' => '',
            'instructor_id' => Auth::id(),
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $material = DB::table('materials')->where('id', $materialId)->first();
        $lessons = [];
        $isNew = true;

        return view('dashboard.partials.admin.materials-create', compact('material', 'lessons', 'isNew'));
    }

    public function edit($id)
    {
        $material = DB::table('materials')->where('id', $id)->first();

        if (!$material)
            abort(404, 'Material not found');

        $lessons = DB::table('lessons')
            ->where('materials_id', $id)
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

        return view('dashboard.partials.admin.materials-create', compact('material', 'lessons', 'isNew'));
    }

    public function manage($id)
    {
        $material = Material::findOrFail($id);

        $material->lessons_count = DB::table('lessons')
            ->where('materials_id', $id)
            ->where('section_type', 'lesson')
            ->count();

        $examIds = DB::table('exams')->where('material_id', $id)->pluck('id');
        $material->items_count = DB::table('exams')->whereIn('id', $examIds)->count();

        $whitelistedStudents = Enrollment::with('user')
            ->where('materials_id', $material->id)
            ->latest()
            ->get()
            ->map(function ($enrollment) {
                $enrollment->student = $enrollment->user;
                $enrollment->lrn = $enrollment->user ? $enrollment->user->lrn : 'N/A';
                return $enrollment;
            });

        return view('dashboard.partials.admin.materials-manage', compact('material', 'whitelistedStudents'));
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

    public function addAccess(Request $request, $id)
    {
        $request->validate(['lrn' => 'required|digits:12']);

        $student = User::where('lrn', $request->lrn)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'No student found with this LRN.']);
        }

        if (Enrollment::where('materials_id', $id)->where('user_id', $student->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Student is already enrolled.']);
        }

        Enrollment::create([
            'materials_id' => $id,
            'user_id' => $student->id,
            'status' => 'enrolled'
        ]);

        return response()->json(['success' => true, 'message' => 'Student enrolled successfully!']);
    }

    public function removeAccess($id)
    {
        $access = Enrollment::findOrFail($id);
        $access->delete();

        return response()->json(['success' => true, 'message' => 'Student access revoked.']);
    }

    public function importAccess(Request $request, $id)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:2048']);

        try {
            Excel::import(new LrnMaterialAccessImport($id), $request->file('file'));
            return response()->json(['success' => true, 'message' => 'LRN list imported successfully!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import failed. Check if your file has an "lrn" header.'], 500);
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
            $existingLessons = DB::table('lessons')->where('materials_id', $id)->pluck('id');
            if ($existingLessons->isNotEmpty()) {
                $existingQuizzes = DB::table('quizzes')->whereIn('lesson_id', $existingLessons)->pluck('id');

                if ($existingQuizzes->isNotEmpty()) {
                    DB::table('quiz_options')->whereIn('quiz_id', $existingQuizzes)->delete();
                }

                DB::table('quizzes')->whereIn('lesson_id', $existingLessons)->delete();
                DB::table('lessons')->where('materials_id', $id)->delete();
            }

            // 2. CLEANUP OLD EXAMS (Added this!)
            $existingExams = DB::table('exams')->where('material_id', $id)->pluck('id');
            if ($existingExams->isNotEmpty()) {
                DB::table('exam_options')->whereIn('id', $existingExams)->delete();
                DB::table('exams')->where('material_id', $id)->delete();
            }

            // 3. INSERT NEW DATA TO PROPER TABLES
            foreach ($categories as $cat) {
                // Check if the JS sent section_type (fallback to 'type' just in case)
                $sectionType = $cat['section_type'] ?? ($cat['type'] ?? 'lesson');

                if ($sectionType === 'exam') {
                    // ==========================================
                    // SAVE FINAL EXAM (Directly to Material)
                    // ==========================================
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
                                'exam_id    ' => $examId,
                                'option_text' => $opt['text'] ?? '',
                                'is_correct' => $opt['is_correct'] ?? false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                } else {
                    // ==========================================
                    // SAVE REGULAR LESSON & QUIZ
                    // ==========================================
                    $lessonId = DB::table('lessons')->insertGetId([
                        'materials_id' => $id,
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
            $lessonIds = DB::table('lessons')->where('materials_id', $id)->pluck('id');

            if ($lessonIds->isNotEmpty()) {
                $quizIds = DB::table('quizzes')->whereIn('lesson_id', $lessonIds)->pluck('id');

                if ($quizIds->isNotEmpty()) {
                    DB::table('quiz_options')->whereIn('quiz_id', $quizIds)->delete();
                }

                DB::table('quizzes')->whereIn('lesson_id', $lessonIds)->delete();
                DB::table('lessons')->where('materials_id', $id)->delete();
            }

            DB::table('enrollments')->where('materials_id', $id)->delete();
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

            // Assuming $id is your $materialId
            if ($request->has('categories')) {
                $categories = json_decode($request->categories, true);

                foreach ($categories as $cat) {
                    // Check what type of section this is from the frontend payload
                    $sectionType = $cat['section_type'] ?? 'lesson';

                    if ($sectionType === 'exam') {
                        // ==========================================
                        // SAVE FINAL EXAM (Directly to Material)
                        // ==========================================
                        foreach ($cat['questions'] ?? [] as $q) {
                            $examId = DB::table('exams')->insertGetId([
                                'material_id' => $id, // Attach to Material
                                'type' => $q['type'] ?? 'mcq',
                                'question_text' => $q['text'] ?? '',
                                'media_url' => $q['media_url'] ?? null,
                                'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            foreach ($q['options'] ?? [] as $opt) {
                                DB::table('exam_options')->insert([
                                    'id' => $examId,
                                    'option_text' => $opt['text'] ?? '',
                                    'is_correct' => $opt['is_correct'] ?? false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    } else {
                        // ==========================================
                        // SAVE LESSON & QUIZZES
                        // ==========================================
                        $lessonId = DB::table('lessons')->insertGetId([
                            'material_id' => $id,
                            'title' => $cat['title'] ?? 'New Lesson',
                            'section_type' => 'lesson',
                            // ... any other lesson fields
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        foreach ($cat['questions'] ?? [] as $q) {
                            $quizId = DB::table('quizzes')->insertGetId([
                                'lesson_id' => $lessonId, // Attach to Lesson
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
}