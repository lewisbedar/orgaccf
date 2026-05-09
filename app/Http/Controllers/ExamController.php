<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSlot;
use App\Models\Grade;
use App\Models\Language;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        return view('exams.index', ['exams' => Exam::with(['schoolClass', 'language', 'teacher'])->where('school_year_id', $this->activeYear()?->id)->whereIn('school_class_id', $this->visibleClassIds())->orderByDesc('exam_date')->get()]);
    }

    public function create()
    {
        $this->coordinator();
        return view('exams.form', [
            'classes' => SchoolClass::where('school_year_id', $this->activeYear()?->id)->orderBy('name')->get(),
            'languages' => Language::where('is_active', true)->orderBy('sort_order')->get(),
            'teachers' => User::where('role', 'enseignant')->where('is_active', true)->orderBy('display_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->coordinator();
        $data = $request->validate([
            'type' => ['required', 'in:ecrit,oral'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'language_id' => ['required', 'exists:languages,id'],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required'],
            'room' => ['required', 'max:120'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'supervisor_name' => ['nullable', 'max:160'],
        ]);
        $exam = Exam::create($data + ['school_year_id' => $this->activeYear()->id]);
        $this->buildSlots($exam);
        return redirect()->route('exams.show', $exam)->with('success', 'Épreuve créée.');
    }

    public function show(Exam $exam)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);
        return view('exams.show', ['exam' => $exam->load(['schoolClass', 'language', 'teacher', 'slots.student', 'grades'])]);
    }

    public function catchup(Request $request, Exam $exam)
    {
        $this->coordinator();
        $data = $request->validate(['exam_date' => ['required', 'date'], 'start_time' => ['required'], 'room' => ['required']]);
        $catchup = Exam::create($exam->only(['school_year_id', 'school_class_id', 'language_id', 'type', 'teacher_id', 'supervisor_name']) + $data + ['is_catchup' => true, 'initial_exam_id' => $exam->id]);
        $this->buildSlots($catchup, $exam);
        Grade::where('exam_id', $exam->id)->where('value', 'AB')->update(['catchup_exam_id' => $catchup->id]);
        return redirect()->route('exams.show', $catchup)->with('success', 'Rattrapage créé.');
    }

    private function buildSlots(Exam $exam, ?Exam $initial = null): void
    {
        $students = $initial
            ? Grade::where('exam_id', $initial->id)->where('value', 'AB')->pluck('student_id')
            : $exam->schoolClass->students()->whereHas('languages', fn ($q) => $q->where('languages.id', $exam->language_id))->orderBy('last_name')->pluck('id');

        foreach ($students->values() as $index => $studentId) {
            $passTime = $exam->type === 'oral' ? now()->setTimeFromTimeString($exam->start_time)->addMinutes($index * 15)->format('H:i:s') : null;
            ExamSlot::firstOrCreate(['exam_id' => $exam->id, 'student_id' => $studentId], ['pass_time' => $passTime]);
            Grade::firstOrCreate(['exam_id' => $exam->id, 'student_id' => $studentId]);
        }
    }
}
