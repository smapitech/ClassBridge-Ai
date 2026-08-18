<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('code_project_id')->constrained('code_projects')->cascadeOnDelete();
            $table->string('filename');                       // e.g., "index.html", "style.css", "script.js"
            $table->string('language')->default('html');      // html, css, javascript, python, text
            $table->longText('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_files');
    }
};