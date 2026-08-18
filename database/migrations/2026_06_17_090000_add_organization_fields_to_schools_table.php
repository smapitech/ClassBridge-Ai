<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('schools', 'organization_type')) {
                $table->string('organization_type')->default('school')->after('slug');
            }

            if (!Schema::hasColumn('schools', 'preferred_teaching_mode')) {
                $table->string('preferred_teaching_mode')->default('whiteboard')->after('timezone');
            }
        });

        DB::table('schools')->whereNull('display_name')->update(['display_name' => DB::raw('name')]);
        DB::table('schools')->whereNull('organization_type')->update(['organization_type' => 'school']);
        DB::table('schools')->whereNull('preferred_teaching_mode')->update(['preferred_teaching_mode' => 'whiteboard']);
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'preferred_teaching_mode')) {
                $table->dropColumn('preferred_teaching_mode');
            }

            if (Schema::hasColumn('schools', 'organization_type')) {
                $table->dropColumn('organization_type');
            }

            if (Schema::hasColumn('schools', 'display_name')) {
                $table->dropColumn('display_name');
            }
        });
    }
};
