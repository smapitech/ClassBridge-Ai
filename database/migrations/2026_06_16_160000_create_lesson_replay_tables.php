<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('classroom_activity_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('classroom_session_id')->constrained('classroom_sessions')->cascadeOnDelete();
            $table->foreignId('live_classroom_id')->nullable()->constrained('live_classrooms')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type'); // session_started, session_ended, participant_joined, whiteboard_added, code_updated, textpad_updated, chat_message, mode_changed, etc.
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('event_data')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
            $table->index(['classroom_session_id', 'event_type']);
            $table->index('occurred_at');
        });

        Schema::create('lesson_replays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('classroom_session_id')->constrained('classroom_sessions')->cascadeOnDelete();
            $table->foreignId('live_classroom_id')->nullable()->constrained('live_classrooms')->nullOnDelete();
            $table->string('title');
            $table->longText('summary')->nullable();
            $table->json('replay_data')->nullable();
            $table->string('visibility')->default('teacher_only'); // teacher_only, student, parent, school_admin
            $table->string('status')->default('processing'); // processing, ready, hidden
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('lesson_replays');
        Schema::dropIfExists('classroom_activity_events');
    }
};