<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('interactive_worksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('age_group')->nullable();
            $table->string('worksheet_type')->default('mixed'); // mixed, multiple_choice, fill_blank, matching, drag_drop, ordering, short_answer, drawing
            $table->longText('instructions')->nullable();
            $table->json('content_json');
            $table->json('answer_key')->nullable();
            $table->string('status')->default('draft'); // draft, published, archived
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });
        Schema::create('worksheet_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('worksheet_id')->constrained('interactive_worksheets')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->json('answers_json')->nullable();
            $table->decimal('score')->nullable();
            $table->string('status')->default('in_progress'); // in_progress, submitted, graded
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->longText('teacher_feedback')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('worksheet_attempts');
        Schema::dropIfExists('interactive_worksheets');
    }
};