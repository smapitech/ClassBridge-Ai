<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_classrooms')) {
            Schema::table('live_classrooms', function (Blueprint $table) {
                if (! Schema::hasColumn('live_classrooms', 'course_id')) {
                    $table->foreignId('course_id')->nullable()->after('school_id')->constrained('courses')->nullOnDelete();
                }

                if (! Schema::hasColumn('live_classrooms', 'duration_minutes')) {
                    $table->unsignedInteger('duration_minutes')->nullable()->after('ends_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('live_classrooms')) {
            Schema::table('live_classrooms', function (Blueprint $table) {
                if (Schema::hasColumn('live_classrooms', 'course_id')) {
                    $table->dropConstrainedForeignId('course_id');
                }

                if (Schema::hasColumn('live_classrooms', 'duration_minutes')) {
                    $table->dropColumn('duration_minutes');
                }
            });
        }
    }
};
