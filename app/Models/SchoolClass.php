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
        $palette = ['#2563A6', '#7C3AED', '#2F855A', '#C76A1A', '#B83280', '#0F766E', '#4F46E5', '#B91C1C'];

        return $this->color ?: $palette[$this->id % count($palette)];
    }
}
