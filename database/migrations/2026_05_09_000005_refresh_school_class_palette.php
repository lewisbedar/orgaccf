<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $palette = ['#2563A6', '#7C3AED', '#2F855A', '#C76A1A', '#B83280', '#0F766E', '#4F46E5', '#B91C1C'];

    public function up(): void
    {
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
        //
    }
};
