<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function edit(Exam $exam)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);
        return view('grades.edit', ['exam' => $exam->load(['schoolClass', 'language', 'slots.student', 'grades'])]);
    }

    public function update(Request $request, Exam $exam)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);
        foreach ($request->input('grades', []) as $studentId => $value) {
            $value = strtoupper(trim((string) $value));
            $numeric = null;
            if ($value !== '' && $value !== 'AB') {
                $numeric = (float) str_replace(',', '.', $value);
                abort_unless($numeric >= 0 && $numeric <= 20, 422);
                $value = number_format($numeric, 2, '.', '');
            }
            Grade::updateOrCreate(['exam_id' => $exam->id, 'student_id' => $studentId], ['value' => $value ?: null, 'numeric_value' => $numeric, 'updated_by' => Auth::id()]);
        }
        $exam->update(['status' => 'terminee']);
        return redirect()->route('exams.show', $exam)->with('success', 'Notes enregistrées.');
    }

    public function summary()
    {
        return view('grades.summary', ['students' => Student::with(['schoolClass', 'languages'])->whereIn('school_class_id', $this->visibleClassIds())->orderBy('last_name')->get()]);
    }
}
