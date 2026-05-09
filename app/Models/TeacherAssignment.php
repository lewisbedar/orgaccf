<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'school_class_id', 'language_id'])]
class TeacherAssignment extends Model
{
    public $timestamps = false;
}
