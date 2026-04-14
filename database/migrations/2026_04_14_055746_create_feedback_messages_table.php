<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('feedback_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained('feedbacks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // The sender of this specific reply
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->timestamps();
        });

        // Safe Data Migration: Move existing admin_replies to the new table
        $feedbacks = DB::table('feedbacks')->whereNotNull('admin_reply')->get();
        foreach ($feedbacks as $fb) {
            DB::table('feedback_messages')->insert([
                'feedback_id' => $fb->id,
                'user_id' => $fb->replied_by_admin_id ?: 1, // Fallback to user ID 1 if null
                'message' => $fb->admin_reply,
                'created_at' => $fb->replied_at ?: now(),
                'updated_at' => $fb->replied_at ?: now(),
            ]);
        }

        // Clean up the old columns from the feedbacks table
        Schema::table('feedbacks', function (Blueprint $table) {
            // 1. DROP THE FOREIGN KEY FIRST
            $table->dropForeign(['replied_by_admin_id']);

            // 2. THEN DROP THE COLUMNS
            $table->dropColumn(['admin_reply', 'replied_by_admin_id', 'replied_at']);
        });
    }

    public function down()
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            // Re-add columns
            $table->text('admin_reply')->nullable();
            $table->foreignId('replied_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();
        });

        Schema::dropIfExists('feedback_messages');
    }
};