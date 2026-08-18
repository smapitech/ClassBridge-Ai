<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coding_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('coding_assignment_id')->nullable()->constrained('coding_assignments')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('join_code')->unique();
            $table->string('status')->default('waiting'); // waiting, live, ended, archived
            $table->string('lesson_mode')->default('coding'); // coding, html, css, javascript, php, mixed
            $table->string('active_file_key')->nullable(); // e.g. index.html, style.css
            $table->json('permissions')->nullable(); // editing, chat, pointer, code, preview
            $table->json('lesson_steps')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('last_saved_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['coding_assignment_id', 'student_id']);
            $table->index('join_code');
        });

        Schema::create('session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('coding_session_id')->constrained('coding_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_session')->default('student'); // teacher, student, assistant, observer
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('typing_status')->nullable(); // typing, idle, requesting_help, raising_hand
            $table->string('active_file_key')->nullable();
            $table->integer('cursor_line')->nullable();
            $table->integer('cursor_column')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();

            $table->unique(['coding_session_id', 'user_id']);
            $table->index('school_id');
        });

        Schema::create('coding_session_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('coding_session_id')->constrained('coding_sessions')->cascadeOnDelete();
            $table->string('filename');
            $table->string('language')->default('plaintext');
            $table->longText('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_entry_point')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->index(['coding_session_id', 'sort_order']);
            $table->index(['coding_session_id', 'filename']);
        });

        Schema::create('coding_session_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('coding_session_id')->constrained('coding_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('message_type')->default('text'); // text, instruction, ai, system
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['coding_session_id', 'created_at']);
        });

        Schema::create('coding_session_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('coding_session_id')->constrained('coding_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['coding_session_id', 'event_type']);
            $table->index(['coding_session_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coding_session_events');
        Schema::dropIfExists('coding_session_messages');
        Schema::dropIfExists('coding_session_files');
        Schema::dropIfExists('session_participants');
        Schema::dropIfExists('coding_sessions');
    }
};
