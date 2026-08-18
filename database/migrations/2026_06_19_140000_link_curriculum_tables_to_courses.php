<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('classes')) {
            Schema::table('classes', function (Blueprint $table) {
                if (! Schema::hasColumn('classes', 'course_id')) {
                    $table->foreignId('course_id')->nullable()->after('school_id')->constrained('courses')->nullOnDelete();
                    $table->index(['school_id', 'course_id']);
                }
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (! Schema::hasColumn('subjects', 'course_id')) {
                    $table->foreignId('course_id')->nullable()->after('school_id')->constrained('courses')->nullOnDelete();
                    $table->index(['school_id', 'course_id']);
                }
            });
        }

        if (Schema::hasTable('teaching_materials')) {
            Schema::table('teaching_materials', function (Blueprint $table) {
                if (! Schema::hasColumn('teaching_materials', 'course_id')) {
                    $table->foreignId('course_id')->nullable()->after('school_id')->constrained('courses')->nullOnDelete();
                    $table->index(['school_id', 'course_id']);
                }
            });
        }

        if (! Schema::hasTable('course_learners')) {
            Schema::create('course_learners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->foreignId('learner_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['course_id', 'learner_id']);
                $table->index(['school_id', 'course_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('course_learners')) {
            Schema::dropIfExists('course_learners');
        }

        if (Schema::hasTable('teaching_materials')) {
            Schema::table('teaching_materials', function (Blueprint $table) {
                if (Schema::hasColumn('teaching_materials', 'course_id')) {
                    $table->dropConstrainedForeignId('course_id');
                }
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (Schema::hasColumn('subjects', 'course_id')) {
                    $table->dropConstrainedForeignId('course_id');
                }
            });
        }

        if (Schema::hasTable('classes')) {
            Schema::table('classes', function (Blueprint $table) {
                if (Schema::hasColumn('classes', 'course_id')) {
                    $table->dropConstrainedForeignId('course_id');
                }
            });
        }
    }
};
