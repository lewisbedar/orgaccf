<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['school_class_id', 'last_name', 'first_name', 'birth_date', 'gender', 'email', 'extra_time', 'pronote_options'])]
class Student extends Model
{
    protected function casts(): array
    {
        return ['birth_date' => 'date', 'extra_time' => 'boolean'];
    }

    public function schoolClass() { return $this->belongsTo(SchoolClass::class); }
    public function languages() { return $this->belongsToMany(Language::class, 'student_languages'); }
}
