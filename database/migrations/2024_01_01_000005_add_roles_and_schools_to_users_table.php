<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds role_id, school_id, and profile fields to users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles');
            $table->foreignId('school_id')->nullable()->after('role_id')->constrained('schools');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('status')->default('active')->after('avatar'); // active, inactive, suspended
            $table->json('metadata')->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('metadata');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['school_id']);
            $table->dropColumn([
                'role_id', 'school_id', 'first_name', 'last_name', 'phone',
                'avatar', 'status', 'metadata', 'last_login_at', 'deleted_at'
            ]);
        });
    }
};