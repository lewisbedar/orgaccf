<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['school_year_id', 'school_class_id', 'language_id', 'type', 'exam_date', 'start_time', 'room', 'teacher_id', 'supervisor_name', 'is_catchup', 'initial_exam_id', 'status'])]
class Exam extends Model
{
    protected function casts(): array
    {
        return ['exam_date' => 'date', 'is_catchup' => 'boolean'];
    }

    public function schoolClass() { return $this->belongsTo(SchoolClass::class); }
    public function language() { return $this->belongsTo(Language::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function slots() { return $this->hasMany(ExamSlot::class); }
    public function grades() { return $this->hasMany(Grade::class); }
    public function initialExam() { return $this->belongsTo(Exam::class, 'initial_exam_id'); }
}
