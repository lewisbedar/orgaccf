<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Language;
use App\Models\SchoolClass;
use App\Services\GradeSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

class GradeController extends Controller
{
    public function edit(Exam $exam)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);

        return view('grades.edit', [
            'exam' => $exam->load(['schoolClass', 'language', 'slots.student', 'grades']),
            'maxScore' => GradeSummaryService::maxForType($exam->type),
        ]);
    }

    public function update(Request $request, Exam $exam)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);

        $maxScore = GradeSummaryService::maxForType($exam->type);
        $errors = new MessageBag();
        $updates = [];
        $slotStudentIds = $exam->slots()->pluck('student_id')->map(fn ($id) => (int) $id)->all();

        foreach ($request->input('grades', []) as $studentId => $value) {
            if (!in_array((int) $studentId, $slotStudentIds, true)) {
                continue;
            }

            $value = strtoupper(trim((string) $value));
            $numeric = null;
            $absenceReason = null;

            if ($value !== '' && $value !== 'AB') {
                $normalizedValue = str_replace(',', '.', $value);

                if (!is_numeric($normalizedValue)) {
                    $errors->add("grades.$studentId", 'La note doit être numérique ou égale à AB.');
                    continue;
                }

                $numeric = (float) $normalizedValue;

                if ($numeric < 0 || $numeric > $maxScore) {
                    $errors->add("grades.$studentId", "La note doit être comprise entre 0 et $maxScore.");
                    continue;
                }

                $value = number_format($numeric, 2, '.', '');
            } elseif ($value === 'AB') {
                $reason = $request->input("absence_reasons.$studentId");
                if (!in_array($reason, ['justifiee', 'injustifiee'], true)) {
                    $errors->add("absence_reasons.$studentId", 'Le motif d’absence est obligatoire pour une absence.');
                    continue;
                }

                $absenceReason = $reason;
            }

            $updates[(int) $studentId] = [
                'value' => $value ?: null,
                'absence_reason' => $absenceReason,
                'numeric_value' => $numeric,
                'updated_by' => Auth::id(),
            ];
        }

        if ($errors->isNotEmpty()) {
            return back()->withErrors($errors)->withInput();
        }

        foreach ($updates as $studentId => $payload) {
            Grade::updateOrCreate(['exam_id' => $exam->id, 'student_id' => $studentId], $payload);
        }

        $exam->update(['status' => 'terminee']);

        return redirect()->route('exams.show', $exam)->with('success', 'Notes enregistrées.');
    }

    public function summary(Request $request)
    {
        $exams = Exam::with(['schoolClass', 'language', 'teacher'])
            ->where('school_year_id', $this->activeYear()?->id)
            ->whereIn('school_class_id', $this->visibleClassIds())
            ->orderByDesc('exam_date')
            ->orderByDesc('start_time')
            ->get();

        $selectedExam = null;
        if ($request->filled('exam_id')) {
            $selectedExam = $exams->firstWhere('id', (int) $request->integer('exam_id'));
            abort_unless($selectedExam, 403);
            $selectedExam->load(['schoolClass', 'language', 'slots.student', 'grades']);
        }

        return view('grades.summary', [
            'exams' => $exams,
            'selectedExam' => $selectedExam,
            'maxScore' => $selectedExam ? GradeSummaryService::maxForType($selectedExam->type) : null,
        ]);
    }

    public function final(Request $request, GradeSummaryService $summaryService)
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

        return view('grades.final', [
            'summaries' => $summaries->values(),
            'classes' => SchoolClass::whereIn('id', $classIds)->orderBy('name')->get(),
            'languages' => Language::where('is_active', true)->orderBy('sort_order')->get(),
            'filters' => $request->only(['class_id', 'language_id', 'status']),
        ]);
    }
}
