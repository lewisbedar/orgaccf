<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSlot;
use App\Models\Grade;
use App\Models\Language;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\OralScheduleBuilder;
use App\Services\PlanningWarningService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        return view('exams.index', [
            'exams' => Exam::with(['schoolClass', 'language', 'teacher'])
                ->where('school_year_id', $this->activeYear()?->id)
                ->whereIn('school_class_id', $this->visibleClassIds())
                ->orderByDesc('exam_date')
                ->get(),
        ]);
    }

    public function create()
    {
        $this->coordinator();

        return view('exams.form', [
            'classes' => SchoolClass::where('school_year_id', $this->activeYear()?->id)->orderBy('name')->get(),
            'languages' => Language::where('is_active', true)->orderBy('sort_order')->get(),
            'teachers' => User::where('role', 'enseignant')->where('is_active', true)->orderBy('display_name')->get(),
            'defaultBreaks' => $this->defaultBreaks(),
        ]);
    }

    public function preview(Request $request, OralScheduleBuilder $scheduleBuilder, PlanningWarningService $warningService)
    {
        $this->coordinator();
        $data = $this->validatedExam($request);

        $class = SchoolClass::with('students.languages')->findOrFail($data['school_class_id']);
        $language = Language::findOrFail($data['language_id']);
        $teacher = !empty($data['teacher_id']) ? User::find($data['teacher_id']) : null;
        $students = $class->students()
            ->whereHas('languages', fn ($query) => $query->where('languages.id', $data['language_id']))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $breaks = $this->cleanBreaks($request->input('breaks', []));
        $slots = $data['type'] === 'oral'
            ? $scheduleBuilder->build($students, $data['exam_date'], $data['start_time'], $breaks)
            : $students->map(fn ($student) => [
                'student_id' => $student->id,
                'last_name' => $student->last_name,
                'first_name' => $student->first_name,
                'extra_time' => $student->extra_time,
                'pass_time' => null,
            ])->all();

        $draft = $data + [
            'school_year_id' => $this->activeYear()->id,
            'breaks' => $breaks,
            'slots' => $slots,
        ];
        $request->session()->put('exam_planning_draft', $draft);

        return view('exams.preview', [
            'draft' => $draft,
            'class' => $class,
            'language' => $language,
            'teacher' => $teacher,
            'slots' => $slots,
            'warnings' => $warningService->warnings($draft, $slots, $this->schoolSetting()),
        ]);
    }

    public function confirm(Request $request)
    {
        $this->coordinator();
        $draft = $request->session()->get('exam_planning_draft');
        abort_unless($draft, 419);

        $data = $request->validate([
            'slots' => ['array'],
            'slots.*.include' => ['nullable', 'boolean'],
            'slots.*.student_id' => ['required', 'exists:students,id'],
            'slots.*.pass_time' => ['nullable'],
        ]);

        $exam = Exam::create(collect($draft)->only([
            'type',
            'school_class_id',
            'language_id',
            'exam_date',
            'start_time',
            'room',
            'teacher_id',
            'supervisor_name',
        ])->all() + ['school_year_id' => $this->activeYear()->id]);

        foreach ($data['slots'] ?? [] as $slot) {
            if (empty($slot['include'])) {
                continue;
            }

            ExamSlot::firstOrCreate(
                ['exam_id' => $exam->id, 'student_id' => $slot['student_id']],
                ['pass_time' => $exam->type === 'oral' ? $slot['pass_time'] : null]
            );
            Grade::firstOrCreate(['exam_id' => $exam->id, 'student_id' => $slot['student_id']]);
        }

        $request->session()->forget('exam_planning_draft');

        return redirect()->route('exams.show', $exam)->with('success', 'Épreuve créée.');
    }

    public function store(Request $request)
    {
        $this->coordinator();
        $data = $this->validatedExam($request);
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

    private function validatedExam(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:ecrit,oral'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'language_id' => ['required', 'exists:languages,id'],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required'],
            'room' => ['required', 'max:120'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'supervisor_name' => ['nullable', 'max:160'],
        ]);
    }

    private function defaultBreaks(): array
    {
        return [
            ['label' => 'Récréation matin', 'start' => '10:00', 'end' => '10:15'],
            ['label' => 'Pause midi', 'start' => '12:15', 'end' => '13:05'],
            ['label' => 'Récréation après-midi', 'start' => '15:55', 'end' => '16:20'],
        ];
    }

    private function cleanBreaks(array $breaks): array
    {
        return collect($breaks)
            ->map(fn (array $break) => [
                'label' => trim((string) ($break['label'] ?? 'Pause')) ?: 'Pause',
                'start' => $break['start'] ?? null,
                'end' => $break['end'] ?? null,
            ])
            ->filter(fn (array $break) => $break['start'] && $break['end'] && $break['start'] < $break['end'])
            ->values()
            ->all();
    }
}
