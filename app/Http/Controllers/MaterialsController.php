<?php

namespace App\Http\Controllers;

use App\Models\Materials;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class MaterialsController extends Controller
{
    public function index()
    {
        // Fetch materials with instructor info and lesson counts
        $materials = Materials::with('instructor')
            ->withCount('lessons')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('dashboard.partials.admin.materials', compact('materials'));
    }

    public function create()
    {
        // Create a blank draft material to attach lessons/files to immediately
        $materialId = DB::table('materials')->insertGetId([
            'title' => 'Untitled Material',
            'description' => '',
            'instructor_id' => Auth::id(),
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $material = DB::table('materials')->where('id', $materialId)->first();
        $lessons = []; // Empty for new materials
        
        return view('dashboard.partials.admin.materials-create', compact('material', 'lessons'));
    }

    public function edit($id)
    {
        $material = DB::table('materials')->where('id', $id)->first();

        if (!$material) {
            abort(404, 'Material not found');
        }

        // Fetch lessons (Categories) and their quizzes (Questions)
        $lessons = DB::table('lessons')
            ->where('materials_id', $id)
            ->get()
            ->map(function ($lesson) {
                // Map the JS "questions" to your "quizzes" table
                $lesson->questions = DB::table('quizzes')
                    ->where('lesson_id', $lesson->id)
                    ->get()
                    ->map(function ($quiz) {
                        // Assuming you have a quiz_options table for the multiple choices
                        $quiz->options = DB::table('quiz_options')
                            ->where('quiz_id', $quiz->id)
                            ->get();
                        return $quiz;
                    });
                return $lesson;
            });

        return view('dashboard.partials.admin.materials-create', compact('material', 'lessons'));
    }

    public function store(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // 1. Update core material details
            DB::table('materials')->where('id', $id)->update([
                'title' => $request->title ?? 'Untitled Material',
                'description' => $request->description ?? '',
                'status' => $request->status ?? 'draft',
                'draft_json' => null, // Clear draft on successful save
                'updated_at' => now()
            ]);

            // 2. Clear old lessons/quizzes to replace with current builder state
            $existingLessons = DB::table('lessons')->where('materials_id', $id)->pluck('id');

            if ($existingLessons->isNotEmpty()) {
                $existingQuizzes = DB::table('quizzes')->whereIn('lesson_id', $existingLessons)->pluck('id');

                if ($existingQuizzes->isNotEmpty()) {
                    DB::table('quiz_options')->whereIn('quiz_id', $existingQuizzes)->delete();
                }

                DB::table('quizzes')->whereIn('lesson_id', $existingLessons)->delete();
                DB::table('lessons')->where('materials_id', $id)->delete();
            }

            // 3. Insert new lessons and quizzes from the payload
            foreach ($request->categories as $cat) {
                $lessonId = DB::table('lessons')->insertGetId([
                    'materials_id' => $id,
                    'title' => $cat['title'] ?? 'New Lesson',
                    'time_limit' => $cat['time_limit'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($cat['questions'] as $q) {
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

            $draftData = [
                'title' => $request->title,
                'description' => $request->description,
                'categories' => $request->categories ?? [] // Matches the JS payload name
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
        // Accepts images, audio, video, AND master documents (PDF, PPT)
        $request->validate([
            'media_file' => 'required|file|mimes:jpeg,png,jpg,gif,mp3,wav,mp4,webm,pdf,ppt,pptx,zip|max:51200',
        ]);

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $path = $file->store('materials_media', 'public');

            $ext = strtolower($file->getClientOriginalExtension());
            
            // Determine type for the frontend renderer
            $type = 'image';
            if (in_array($ext, ['mp4', 'webm'])) $type = 'video';
            if (in_array($ext, ['mp3', 'wav', 'ogg'])) $type = 'audio';
            if (in_array($ext, ['pdf', 'ppt', 'pptx', 'zip'])) $type = 'document';

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

            DB::table('materials')->where('id', $id)->delete();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}