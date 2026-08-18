<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('relationship')->nullable();     // e.g., "Father", "Mother", "Guardian"
            $table->string('occupation')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('active');    // active, inactive
            $table->timestamps();

            $table->unique(['user_id', 'school_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_profiles');
    }
};