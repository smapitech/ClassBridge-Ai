<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('default_provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->boolean('ai_enabled')->default(true);
            $table->boolean('allow_teacher_ai')->default(true);
            $table->boolean('allow_school_override')->default(false);
            $table->integer('monthly_generation_limit')->nullable();
            $table->integer('monthly_token_limit')->nullable();
            $table->decimal('monthly_cost_limit', 10, 4)->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};