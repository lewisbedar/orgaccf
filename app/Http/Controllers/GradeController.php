<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Grade;
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
                    $errors->add("grades.$studentId", 'La note doit etre numerique ou egale a AB.');
                    continue;
                }

                $numeric = (float) $normalizedValue;

                if ($numeric < 0 || $numeric > $maxScore) {
                    $errors->add("grades.$studentId", "La note doit etre comprise entre 0 et $maxScore.");
                    continue;
                }

                $value = number_format($numeric, 2, '.', '');
            } elseif ($value === 'AB') {
                $reason = $request->input("absence_reasons.$studentId");
                $absenceReason = in_array($reason, ['justifiee', 'injustifiee'], true) ? $reason : null;
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

        return redirect()->route('exams.show', $exam)->with('success', 'Notes enregistrees.');
    }

    public function summary(GradeSummaryService $summaryService)
    {
        return view('grades.summary', [
            'summaries' => $summaryService->forYear($this->activeYear(), $this->visibleClassIds()),
        ]);
    }
}
