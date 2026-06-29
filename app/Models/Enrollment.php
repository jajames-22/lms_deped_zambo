<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'material_id',
        'status',
        'progress_data',
        'completed_at',
        'calculated_time',
        'study_session_started_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // A student belongs to a user account
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public static function reactivateAndResetForStudent($user, $material)
    {
        // 1. Collect all quiz and exam IDs for this material
        $examIds = \Illuminate\Support\Facades\DB::table('exams')->where('material_id', $material->id)->pluck('id');
        $quizIds = \Illuminate\Support\Facades\DB::table('lesson_contents')
            ->join('lessons', 'lesson_contents.lesson_id', '=', 'lessons.id')
            ->where('lessons.material_id', $material->id)
            ->pluck('lesson_contents.id');

        // 2. Delete quiz and exam answers (reset progress)
        if ($quizIds->isNotEmpty()) {
            \App\Models\QuizAnswer::where('user_id', $user->id)->whereIn('lesson_content_id', $quizIds)->delete();
        }
        if ($examIds->isNotEmpty()) {
            \App\Models\ExamAnswer::where('user_id', $user->id)->whereIn('exam_id', $examIds)->delete();
        }

        // 3. Reactivate enrollment record (reset progress but keep the row)
        $enrollment = self::where('material_id', $material->id)->where('user_id', $user->id)->first();
        if ($enrollment) {
            $enrollment->update([
                'status'                   => 'in_progress',
                'progress_data'            => null,
                'completed_at'             => null,
                'calculated_time'          => 0,
                'study_session_started_at' => null,
            ]);
        } else {
            $enrollment = self::create([
                'material_id' => $material->id,
                'user_id'     => $user->id,
                'status'      => 'in_progress',
            ]);
        }

        // 4. Reactivate MaterialAccess record — increment retakes, preserve history
        $access = \App\Models\MaterialAccess::where('material_id', $material->id)->where('email', $user->email)->first();
        if ($access) {
            $access->update([
                'status'  => 'enrolled',
                'retakes' => ($access->retakes ?? 0) + 1,
            ]);
        } else {
            $access = \App\Models\MaterialAccess::create([
                'material_id' => $material->id,
                'email'       => $user->email,
                'student_id'  => $user->id,
                'status'      => 'enrolled',
                'retakes'     => 1,
            ]);
        }

        // 5. Send notification
        $user->notify(new \App\Notifications\LmsAlertNotification(
            'Module Re-enrolled',
            'You have successfully enrolled in the module "' . $material->title . '". Your learning progress has been restarted.',
            route('dashboard.materials.show', $material->hashid),
            'fas fa-rotate-left',
            'text-blue-500'
        ));

        return $enrollment;
    }
}