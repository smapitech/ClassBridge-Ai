<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans','currency')) $table->string('currency')->default('USD')->after('price_yearly');
            if (!Schema::hasColumn('subscription_plans','max_live_classrooms')) $table->integer('max_live_classrooms')->nullable()->after('max_classes');
            if (!Schema::hasColumn('subscription_plans','max_storage_mb')) $table->integer('max_storage_mb')->nullable()->after('max_live_classrooms');
            if (!Schema::hasColumn('subscription_plans','features')) $table->json('features')->nullable()->after('max_storage_mb');
            if (!Schema::hasColumn('subscription_plans','is_popular')) $table->boolean('is_popular')->default(false)->after('features');
        });
        Schema::table('school_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('school_subscriptions','billing_cycle')) $table->string('billing_cycle')->default('monthly')->after('status');
            if (!Schema::hasColumn('school_subscriptions','renews_at')) $table->timestamp('renews_at')->nullable()->after('ends_at');
            if (!Schema::hasColumn('school_subscriptions','cancelled_at')) $table->timestamp('cancelled_at')->nullable()->after('renews_at');
            if (!Schema::hasColumn('school_subscriptions','metadata')) $table->json('metadata')->nullable()->after('payment_reference');
        });
    }
    public function down(): void {}
};