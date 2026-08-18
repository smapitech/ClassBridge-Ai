<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('live_classroom_id')->nullable()->constrained('live_classrooms')->nullOnDelete();
            $table->foreignId('classroom_session_id')->nullable()->constrained('classroom_sessions')->nullOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('present'); // present, absent, late, excused
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->date('attendance_date');
            $table->timestamps();
            $table->unique(['student_id', 'attendance_date', 'class_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_records'); }
};