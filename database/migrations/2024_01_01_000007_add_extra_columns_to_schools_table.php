<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add SaaS-specific columns to schools table.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('country')->nullable()->after('phone');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
            $table->string('website')->nullable()->after('logo_path');
            $table->foreignId('owner_user_id')->nullable()->after('website')->constrained('users')->nullOnDelete();
            $table->string('status')->default('trial')->after('owner_user_id'); // active, suspended, trial, inactive
            $table->foreignId('subscription_plan_id')->nullable()->after('status')->constrained('subscription_plans')->nullOnDelete();
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['owner_user_id']);
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn([
                'country', 'state', 'city', 'website',
                'owner_user_id', 'status', 'subscription_plan_id', 'trial_ends_at',
            ]);
        });
    }
};