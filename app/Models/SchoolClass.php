<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['school_year_id', 'name', 'language_ids'])]
class SchoolClass extends Model
{
    protected function casts(): array
    {
        return ['language_ids' => 'array'];
    }

    public function schoolYear() { return $this->belongsTo(SchoolYear::class); }
    public function students() { return $this->hasMany(Student::class); }
}
