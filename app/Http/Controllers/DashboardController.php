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
        $examQuery = Exam::with(['schoolClass', 'language', 'teacher'])->where('school_year_id', $year->id)->whereIn('school_class_id', $classIds);

        return view('dashboard', [
            'school' => $this->schoolSetting(),
            'year' => $year,
            'stats' => [
                'classes' => count($classIds),
                'students' => Student::whereIn('school_class_id', $classIds)->count(),
                'planned' => (clone $examQuery)->count(),
                'finished' => (clone $examQuery)->where('status', 'terminee')->count(),
                'absents' => Grade::whereHas('exam', fn ($q) => $q->where('school_year_id', $year->id)->whereIn('school_class_id', $classIds))->where('value', 'AB')->whereNull('catchup_exam_id')->count(),
            ],
            'upcoming' => (clone $examQuery)->whereDate('exam_date', '>=', now())->orderBy('exam_date')->orderBy('start_time')->limit(12)->get(),
        ]);
    }
}
