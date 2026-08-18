<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');                         // e.g., "Mathematics", "English", "Coding"
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('category')->nullable();         // e.g., "STEM", "Languages", "Arts"
            $table->string('status')->default('active');    // active, inactive
            $table->timestamps();

            $table->unique(['school_id', 'slug']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};