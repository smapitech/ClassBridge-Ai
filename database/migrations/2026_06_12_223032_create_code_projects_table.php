<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('live_classroom_id')->nullable()->constrained('live_classrooms')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('project_type')->default('html_css_js'); // html_css_js, python_placeholder, block_coding_placeholder
            $table->string('status')->default('draft');       // draft, active, submitted, reviewed, archived
            $table->string('visibility')->default('private');  // private, teacher, class, public_placeholder
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_projects');
    }
};