<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Roles define the permission levels in ClassBridge AI:
     * - super_admin: Platform owner, manages all schools
     * - school_admin: School owner/principal, manages their school
     * - teacher: Teaches classes within their school
     * - student: Attends classes within their school
     * - parent: Views their child's progress
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // super_admin, school_admin, teacher, student, parent
            $table->string('slug')->unique(); // slug version for route/middleware matching
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};