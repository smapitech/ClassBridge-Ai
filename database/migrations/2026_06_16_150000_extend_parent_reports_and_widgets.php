<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        // Add missing columns to existing parent_reports
        Schema::table('parent_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('parent_reports','report_type')) $table->string('report_type')->default('custom')->after('student_id'); // weekly, monthly, termly, custom
            if (!Schema::hasColumn('parent_reports','badge_summary')) $table->json('badge_summary')->nullable()->after('quiz_summary');
            if (!Schema::hasColumn('parent_reports','parent_recommendation')) $table->longText('parent_recommendation')->nullable()->after('ai_summary');
        });

        Schema::create('parent_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('widget_key');
            $table->string('title');
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('parent_dashboard_widgets');
        Schema::table('parent_reports', function (Blueprint $table) {
            $table->dropColumn(['report_type','badge_summary','parent_recommendation']);
        });
    }
};