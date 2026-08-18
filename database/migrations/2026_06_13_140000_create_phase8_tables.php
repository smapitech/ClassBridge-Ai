<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('module')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['school_id','action']);
        });
        Schema::create('safety_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->boolean('allow_student_chat')->default(true);
            $table->boolean('allow_student_drawing')->default(true);
            $table->boolean('allow_private_teacher_student_chat')->default(false);
            $table->boolean('require_parent_visibility')->default(true);
            $table->boolean('record_classroom_activity')->default(true);
            $table->boolean('show_safety_notice')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique('school_id');
        });
        Schema::create('onboarding_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('step_key');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('onboarding_steps');
        Schema::dropIfExists('safety_settings');
        Schema::dropIfExists('audit_logs');
    }
};