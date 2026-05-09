<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['exam_id', 'student_id', 'pass_time'])]
class ExamSlot extends Model
{
    public $timestamps = false;

    public function student() { return $this->belongsTo(Student::class); }
}
