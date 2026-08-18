<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Subscription plans define pricing tiers for schools.
     * Each plan has limits on users, classes, AI usage, and features.
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Starter", "Growth", "Enterprise"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->integer('max_students')->default(50);
            $table->integer('max_teachers')->default(5);
            $table->integer('max_classes')->default(10);
            $table->integer('ai_requests_per_month')->default(100);
            $table->boolean('has_whiteboard')->default(true);
            $table->boolean('has_code_editor')->default(false);
            $table->boolean('has_ai_assistant')->default(false);
            $table->boolean('has_attendance')->default(true);
            $table->boolean('has_homework')->default(true);
            $table->boolean('has_parent_reports')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Pivot: which plan a school is subscribed to
        Schema::create('school_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');
            $table->string('status')->default('active'); // active, canceled, expired, trial
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('ai_requests_used')->default(0);
            $table->string('payment_method')->nullable(); // paystack, flutterwave, stripe, manual
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};