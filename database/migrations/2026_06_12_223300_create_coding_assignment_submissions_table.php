<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coding_assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('coding_assignment_id')->constrained('coding_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('code_project_id')->nullable()->constrained('code_projects')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status')->default('draft');       // draft, submitted, reviewed, returned
            $table->longText('teacher_feedback')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['coding_assignment_id', 'student_id'], 'unique_assignment_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coding_assignment_submissions');
    }
};