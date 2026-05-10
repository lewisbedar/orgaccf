<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;

class DashboardController extends Controller
{
    public function __invoke()
    {
        if (!$this->activeYear()) {
            return redirect()->route('setup.year');
        }

        $classIds = $this->visibleClassIds();
        $year = $this->activeYear();
        $examQuery = Exam::with(['schoolClass', 'language', 'teacher'])
            ->where('school_year_id', $year->id)
            ->whereIn('school_class_id', $classIds);
        $absentQuery = Grade::whereHas('exam', fn ($q) => $q
            ->where('school_year_id', $year->id)
            ->whereIn('school_class_id', $classIds))
            ->where('value', 'AB')
            ->whereNull('catchup_exam_id');

        return view('dashboard', [
            'school' => $this->schoolSetting(),
            'year' => $year,
            'stats' => [
                'classes' => count($classIds),
                'students' => Student::whereIn('school_class_id', $classIds)->count(),
                'planned' => (clone $examQuery)->count(),
                'finished' => (clone $examQuery)->where('status', 'terminee')->count(),
                'notes_to_enter' => (clone $examQuery)->where('status', 'prevue')->whereDate('exam_date', '<=', now())->count(),
                'absents' => (clone $absentQuery)->count(),
            ],
            'upcoming' => (clone $examQuery)->whereDate('exam_date', '>=', now())->orderBy('exam_date')->orderBy('start_time')->limit(12)->get(),
            'todoExams' => (clone $examQuery)->where('status', 'prevue')->whereDate('exam_date', '<=', now())->orderBy('exam_date')->orderBy('start_time')->limit(5)->get(),
            'catchupExams' => (clone $absentQuery)->with(['exam.schoolClass', 'exam.language'])->get()->pluck('exam')->unique('id')->values(),
        ]);
    }
}
