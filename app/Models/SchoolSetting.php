<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['school_name', 'address', 'phone', 'email', 'academic_zone', 'oral_breaks', 'logo_path', 'active_school_year_id'])]
class SchoolSetting extends Model
{
    protected function casts(): array
    {
        return ['oral_breaks' => 'array'];
    }

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'school_name' => 'Lycée à configurer',
            'academic_zone' => 'C',
            'oral_breaks' => self::defaultOralBreaks(),
        ]);
    }

    public static function defaultOralBreaks(): array
    {
        return [
            ['label' => 'Récréation matin', 'start' => '10:00', 'end' => '10:15'],
            ['label' => 'Pause midi', 'start' => '12:15', 'end' => '13:05'],
            ['label' => 'Récréation après-midi', 'start' => '15:55', 'end' => '16:20'],
        ];
    }

    public function oralBreaks(): array
    {
        return $this->oral_breaks ?: self::defaultOralBreaks();
    }
}
