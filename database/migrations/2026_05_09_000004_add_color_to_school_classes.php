<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $palette = ['#1f6f78', '#8a5d00', '#7a3b69', '#2f6b3f', '#92413b', '#4f5f9f', '#b35f2d', '#317082'];

    public function up(): void
    {
        if (!Schema::hasColumn('school_classes', 'color')) {
            Schema::table('school_classes', function (Blueprint $table) {
                $table->string('color', 7)->nullable()->after('name');
            });
        }

        DB::table('school_classes')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($class, int $index) {
                DB::table('school_classes')
                    ->where('id', $class->id)
                    ->update(['color' => $this->palette[$index % count($this->palette)]]);
            });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('school_classes', 'color')) {
            return;
        }

        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
