<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whiteboard_elements', function (Blueprint $table) {
            $table->uuid('element_uuid')->nullable()->after('id');
            $table->foreignId('whiteboard_id')->nullable()->after('classroom_session_id')->constrained('whiteboards')->cascadeOnDelete();
            $table->foreignId('whiteboard_page_id')->nullable()->after('whiteboard_id')->constrained('whiteboard_pages')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('z_index')->default(0)->after('updated_by');
            $table->boolean('is_locked')->default(false)->after('z_index');

            $table->index(['whiteboard_id', 'whiteboard_page_id']);
        });
    }

    public function down(): void
    {
        Schema::table('whiteboard_elements', function (Blueprint $table) {
            $table->dropIndex(['whiteboard_id', 'whiteboard_page_id']);
            $table->dropConstrainedForeignId('whiteboard_page_id');
            $table->dropConstrainedForeignId('whiteboard_id');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['element_uuid', 'z_index', 'is_locked']);
        });
    }
};
