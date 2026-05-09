<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->enum('academic_zone', ['A', 'B', 'C'])->default('C')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn('academic_zone');
        });
    }
};
