<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['exam_id', 'student_id', 'value', 'absence_reason', 'numeric_value', 'catchup_exam_id', 'updated_by'])]
class Grade extends Model
{
    public function student() { return $this->belongsTo(Student::class); }
    public function exam() { return $this->belongsTo(Exam::class); }
}
