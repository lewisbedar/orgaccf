<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('school_settings', 'oral_breaks')) {
            return;
        }

        Schema::table('school_settings', function (Blueprint $table) {
            $table->json('oral_breaks')->nullable()->after('academic_zone');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('school_settings', 'oral_breaks')) {
            return;
        }

        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn('oral_breaks');
        });
    }
};
