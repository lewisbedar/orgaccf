<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Services\GradeSummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function convocations(Exam $exam)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);

        $exam->load(['schoolClass.schoolYear', 'language', 'teacher', 'slots.student']);
        $studentIds = $exam->slots->pluck('student_id')->map(fn ($id) => (int) $id)->all();

        $relatedExams = Exam::with(['schoolClass.schoolYear', 'language', 'teacher', 'slots.student'])
            ->where('school_year_id', $exam->school_year_id)
            ->where('school_class_id', $exam->school_class_id)
            ->where('language_id', $exam->language_id)
            ->whereHas('slots', fn ($query) => $query->whereIn('student_id', $studentIds))
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        $convocations = $exam->slots
            ->sortBy(fn ($slot) => $slot->student->last_name . ' ' . $slot->student->first_name)
            ->map(function ($slot) use ($relatedExams) {
                $student = $slot->student;

                return [
                    'student' => $student,
                    'exams' => $relatedExams
                        ->filter(fn (Exam $relatedExam) => $relatedExam->slots->contains('student_id', $student->id))
                        ->map(function (Exam $relatedExam) use ($student) {
                            $studentSlot = $relatedExam->slots->firstWhere('student_id', $student->id);
                            $relatedExam->setRelation('studentSlot', $studentSlot);

                            return $relatedExam;
                        })
                        ->values(),
                ];
            })
            ->values();

        $pdf = Pdf::loadView('documents.convocations', [
            'title' => 'Convocations eleves',
            'school' => $this->schoolSetting(),
            'exam' => $exam,
            'convocations' => $convocations,
        ])->setPaper('a4');

        $filename = sprintf(
            'convocations-%s-%s.pdf',
            str($exam->schoolClass->name)->slug(),
            str($exam->language->label())->slug()
        );

        return $pdf->stream($filename);
    }

    public function attendance(Exam $exam)
    {
        return $this->examPdf('documents.attendance', $exam, 'feuille-emargement');
    }

    public function oralList(Exam $exam)
    {
        return $this->examPdf('documents.oral-list', $exam, 'liste-passage-oral');
    }

    public function grades(Request $request)
    {
        $exam = null;
        if ($request->filled('exam_id')) {
            $exam = Exam::with(['schoolClass.schoolYear', 'language', 'teacher', 'slots.student', 'grades'])
                ->where('school_year_id', $this->activeYear()?->id)
                ->whereIn('school_class_id', $this->visibleClassIds())
                ->findOrFail($request->integer('exam_id'));
        }

        $viewData = [
            'school' => $this->schoolSetting(),
            'year' => $this->activeYear(),
            'exam' => $exam,
            'maxScore' => $exam ? GradeSummaryService::maxForType($exam->type) : null,
        ];

        $filename = $exam
            ? sprintf('recapitulatif-notes-%s-%s.pdf', str($exam->schoolClass->name)->slug(), str($exam->language->label())->slug())
            : 'recapitulatif-notes.pdf';

        return Pdf::loadView('documents.grades', $viewData)->setPaper('a4')->stream($filename);
    }

    public function finalGrades(Request $request, GradeSummaryService $summaryService)
    {
        $classIds = $this->visibleClassIds();
        $summaries = $summaryService->forYear($this->activeYear(), $classIds);

        if ($request->filled('class_id')) {
            $classId = (int) $request->integer('class_id');
            abort_unless(in_array($classId, $classIds, true), 403);
            $summaries = $summaries->filter(fn ($summary) => $summary['student']->school_class_id === $classId);
        }

        if ($request->filled('language_id')) {
            $languageId = (int) $request->integer('language_id');
            $summaries = $summaries->filter(fn ($summary) => $summary['language']->id === $languageId);
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            abort_unless(in_array($status, ['complet', 'incomplet', 'eliminatoire'], true), 422);
            $summaries = $summaries->filter(fn ($summary) => $summary['status'] === $status);
        }

        return Pdf::loadView('documents.final-grades', [
            'school' => $this->schoolSetting(),
            'year' => $this->activeYear(),
            'summaries' => $summaries->values(),
        ])->setPaper('a4', 'landscape')->stream('bilan-notes.pdf');
    }

    private function document(string $view, Exam $exam, string $title)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);

        return view($view, [
            'title' => $title,
            'school' => $this->schoolSetting(),
            'exam' => $exam->load(['schoolClass.schoolYear', 'language', 'teacher', 'slots.student']),
        ]);
    }

    private function examPdf(string $view, Exam $exam, string $prefix)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);

        $exam->load(['schoolClass.schoolYear', 'language', 'teacher', 'slots.student']);

        $filename = sprintf(
            '%s-%s-%s.pdf',
            $prefix,
            str($exam->schoolClass->name)->slug(),
            str($exam->language->label())->slug()
        );

        return Pdf::loadView($view, [
            'title' => $prefix,
            'school' => $this->schoolSetting(),
            'exam' => $exam,
        ])->setPaper('a4')->stream($filename);
    }
}
