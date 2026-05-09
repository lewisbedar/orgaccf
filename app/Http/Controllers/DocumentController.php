<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Grade;

class DocumentController extends Controller
{
    public function convocations(Exam $exam)
    {
        return $this->document('documents.convocations', $exam, 'Convocations élèves');
    }

    public function attendance(Exam $exam)
    {
        return $this->document('documents.attendance', $exam, 'Feuille d’émargement');
    }

    public function oralList(Exam $exam)
    {
        return $this->document('documents.oral-list', $exam, 'Liste de passage oral');
    }

    public function grades()
    {
        return view('documents.grades', [
            'school' => $this->schoolSetting(),
            'year' => $this->activeYear(),
            'grades' => Grade::with(['student.schoolClass', 'exam.language'])->whereHas('exam', fn ($q) => $q->where('school_year_id', $this->activeYear()?->id))->get(),
        ]);
    }

    private function document(string $view, Exam $exam, string $title)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);
        return view($view, ['title' => $title, 'school' => $this->schoolSetting(), 'exam' => $exam->load(['schoolClass.schoolYear', 'language', 'teacher', 'slots.student'])]);
    }
}
