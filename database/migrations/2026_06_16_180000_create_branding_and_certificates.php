<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('school_branding_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete()->unique();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('login_background')->nullable();
            $table->string('portal_theme')->nullable();
            $table->string('email_sender_name')->nullable();
            $table->string('support_email')->nullable();
            $table->string('certificate_signature')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->string('status')->default('pending'); // pending, verified, failed, disabled
            $table->string('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('template_type')->default('custom'); // course_completion, achievement, attendance, custom
            $table->string('background_image')->nullable();
            $table->json('layout_json')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('student_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('certificate_template_id')->nullable()->constrained('certificate_templates')->nullOnDelete();
            $table->string('title');
            $table->string('course_name')->nullable();
            $table->text('description')->nullable();
            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique();
            $table->timestamp('issued_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('status')->default('draft'); // draft, issued, revoked
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('student_certificates');
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('custom_domains');
        Schema::dropIfExists('school_branding_settings');
    }
};