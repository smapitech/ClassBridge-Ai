<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coding_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->longText('starter_html')->nullable();
            $table->longText('starter_css')->nullable();
            $table->longText('starter_js')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('status')->default('draft');       // draft, published, closed
            $table->timestamps();
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coding_assignments');
    }
};