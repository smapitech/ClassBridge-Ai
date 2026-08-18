<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('material_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('material_folders')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('visibility')->default('teacher'); // private, teacher, school, public_placeholder
            $table->timestamps();
        });
        Schema::create('teaching_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('material_folders')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('material_type')->default('other'); // note, pdf, image, video, link, code, whiteboard, worksheet, quiz, homework, ai_lesson, other
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->json('metadata')->nullable();
            $table->string('visibility')->default('teacher'); // private, teacher, school, public_placeholder
            $table->string('status')->default('draft'); // draft, published, archived
            $table->timestamps();
        });
        Schema::create('lesson_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('subject')->nullable();
            $table->string('topic')->nullable();
            $table->string('age_group')->nullable();
            $table->string('level')->nullable();
            $table->longText('template_content');
            $table->json('metadata')->nullable();
            $table->string('visibility')->default('private'); // private, school, global
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('lesson_templates');
        Schema::dropIfExists('teaching_materials');
        Schema::dropIfExists('material_folders');
    }
};