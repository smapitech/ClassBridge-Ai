<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->json('availability')->nullable();       // e.g., {"mon":["9-12","14-16"],"tue":["9-12"]}
            $table->string('status')->default('active');    // active, inactive
            $table->timestamps();

            $table->unique(['user_id', 'school_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};