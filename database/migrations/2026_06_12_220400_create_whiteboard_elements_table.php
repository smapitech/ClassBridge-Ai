<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whiteboard_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('classroom_session_id')->constrained('classroom_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('element_type');                    // pen, text, shape, image, eraser, pointer, sticky_note
            $table->json('data');                              // {x, y, color, stroke, points, text, width, height, shapeType}
            $table->timestamps();

            $table->index(['classroom_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whiteboard_elements');
    }
};