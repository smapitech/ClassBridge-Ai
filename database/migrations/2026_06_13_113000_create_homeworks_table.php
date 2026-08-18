<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('instructions')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('status')->default('draft'); // draft, published, closed
            $table->timestamps();
            $table->index(['school_id', 'class_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('homeworks'); }
};