<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrateur OrgaCCF',
            'email' => 'admin@orgaccf.local',
            'display_name' => 'Administrateur OrgaCCF',
            'role' => 'coordinateur',
            'languages_text' => 'Toutes',
            'classes_text' => 'Toutes',
            'password' => Hash::make('admin123'),
            'is_active' => true,
        ]);

        SchoolSetting::firstOrCreate([], ['school_name' => 'Lycée à configurer']);

        foreach ([
            ['Anglais', 'LVA', '/images/flags/gb.svg', 10],
            ['Anglais', 'LVB', '/images/flags/gb.svg', 20],
            ['Espagnol', 'LVA', '/images/flags/es.svg', 30],
            ['Espagnol', 'LVB', '/images/flags/es.svg', 40],
        ] as [$name, $level, $iconPath, $sortOrder]) {
            Language::updateOrCreate(['name' => $name, 'level' => $level], [
                'icon_path' => $iconPath,
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]);
        }
    }
}
