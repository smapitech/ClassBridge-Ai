<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('feedback_type')->default('general'); // general, homework, quiz, behavior, progress
            $table->string('title')->nullable();
            $table->longText('comment');
            $table->string('visibility')->default('internal'); // internal, parent_visible
            $table->timestamps();
            $table->index(['school_id', 'student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_feedback'); }
};