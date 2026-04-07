<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            
            // The user (Student or Teacher) who submitted the feedback
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Categorization
            // e.g., 'assessment', 'material', 'account', 'bug_report', 'other'
            $table->string('category'); 
            
            // The actual content
            $table->string('subject');
            $table->text('message');
            
            // Media support (Path to the uploaded screenshot/image)
            $table->string('media_url')->nullable(); 
            
            // Tracking status: 'pending', 'in_progress', 'resolved', 'closed'
            $table->string('status')->default('pending');

            // --- ADMIN REPLY SECTION ---
            $table->text('admin_reply')->nullable();
            
            // Tracks which admin replied to the ticket
            $table->foreignId('replied_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};