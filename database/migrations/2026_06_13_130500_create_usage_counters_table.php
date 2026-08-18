<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('usage_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->integer('teachers_count')->default(0);
            $table->integer('students_count')->default(0);
            $table->integer('live_classrooms_count')->default(0);
            $table->integer('ai_generations_count')->default(0);
            $table->decimal('storage_used_mb', 10, 2)->default(0);
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('usage_counters'); }
};