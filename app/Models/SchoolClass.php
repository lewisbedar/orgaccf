<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['school_year_id', 'name', 'color', 'language_ids'])]
class SchoolClass extends Model
{
    protected function casts(): array
    {
        return ['language_ids' => 'array'];
    }

    public function schoolYear() { return $this->belongsTo(SchoolYear::class); }
    public function students() { return $this->hasMany(Student::class); }

    public function displayColor(): string
    {
        $palette = ['#1f6f78', '#8a5d00', '#7a3b69', '#2f6b3f', '#92413b', '#4f5f9f', '#b35f2d', '#317082'];

        return $this->color ?: $palette[$this->id % count($palette)];
    }
}
