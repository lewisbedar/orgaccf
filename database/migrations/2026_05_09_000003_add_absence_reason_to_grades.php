<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('grades', 'absence_reason')) {
            return;
        }

        Schema::table('grades', function (Blueprint $table) {
            $table->enum('absence_reason', ['justifiee', 'injustifiee'])->nullable()->after('value');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('grades', 'absence_reason')) {
            return;
        }

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('absence_reason');
        });
    }
};
