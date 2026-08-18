<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whiteboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('classroom_session_id')->constrained('classroom_sessions')->cascadeOnDelete();
            $table->foreignId('live_classroom_id')->nullable()->constrained('live_classrooms')->nullOnDelete();
            $table->string('title');
            $table->unsignedBigInteger('current_page_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique('classroom_session_id');
            $table->index(['school_id', 'classroom_session_id']);
        });

        Schema::create('whiteboard_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whiteboard_id')->constrained('whiteboards')->cascadeOnDelete();
            $table->string('page_key');
            $table->string('title');
            $table->unsignedInteger('page_number')->default(1);
            $table->string('background_type')->nullable();
            $table->longText('background_value')->nullable();
            $table->longText('thumbnail_path')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['whiteboard_id', 'page_key']);
            $table->index(['whiteboard_id', 'page_number']);
        });

        Schema::create('whiteboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('whiteboard_id')->constrained('whiteboards')->cascadeOnDelete();
            $table->foreignId('whiteboard_page_id')->nullable()->constrained('whiteboard_pages')->nullOnDelete();
            $table->json('snapshot_data');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['whiteboard_id', 'created_at']);
            $table->index(['whiteboard_id', 'whiteboard_page_id']);
        });

        Schema::table('whiteboards', function (Blueprint $table) {
            $table->foreign('current_page_id')
                ->references('id')
                ->on('whiteboard_pages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('whiteboards', function (Blueprint $table) {
            $table->dropForeign(['current_page_id']);
        });

        Schema::dropIfExists('whiteboard_snapshots');
        Schema::dropIfExists('whiteboard_pages');
        Schema::dropIfExists('whiteboards');
    }
};
