<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('live_classrooms', function (Blueprint $table) {
            if (!Schema::hasColumn('live_classrooms','classroom_mode'))
                $table->string('classroom_mode')->default('whiteboard')->after('settings');
            if (!Schema::hasColumn('live_classrooms','layout_settings'))
                $table->json('layout_settings')->nullable()->after('classroom_mode');
        });
        Schema::table('classroom_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('classroom_sessions','active_mode'))
                $table->string('active_mode')->default('whiteboard')->after('textpad_snapshot');
            if (!Schema::hasColumn('classroom_sessions','mode_settings'))
                $table->json('mode_settings')->nullable()->after('active_mode');
        });
    }
    public function down(): void {
        Schema::table('live_classrooms', function (Blueprint $table) {
            if (Schema::hasColumn('live_classrooms','classroom_mode')) $table->dropColumn(['classroom_mode','layout_settings']);
        });
        Schema::table('classroom_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('classroom_sessions','active_mode')) $table->dropColumn(['active_mode','mode_settings']);
        });
    }
};