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
use Illuminate\Http\Exceptions\HttpResponseException;
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

    public function create(Request $request)
    {
        $this->coordinator();

        if ($request->boolean('reset')) {
            $request->session()->forget('exam_planning_draft');
        }

        return view('exams.wizard-class', [
            'classes' => SchoolClass::where('school_year_id', $this->activeYear()?->id)->orderBy('name')->get(),
            'draft' => $this->draft($request),
        ]);
    }

    public function storeClassStep(Request $request)
    {
        $this->coordinator();
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ]);

        $this->mergeDraft($request, [
            'school_class_id' => (int) $data['school_class_id'],
        ]);

        return redirect()->route('exams.wizard.type');
    }

    public function type(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['school_class_id']);
        $class = SchoolClass::findOrFail($this->draft($request)['school_class_id']);
        $languageIds = $class->language_ids ?: [];

        return view('exams.wizard-type', [
            'class' => $class,
            'languages' => Language::where('is_active', true)
                ->when($languageIds !== [], fn ($query) => $query->whereIn('id', $languageIds))
                ->orderBy('sort_order')
                ->get(),
            'draft' => $this->draft($request),
        ]);
    }

    public function storeTypeStep(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['school_class_id']);

        $data = $request->validate([
            'type' => ['required', 'in:ecrit,oral'],
            'language_id' => ['required', 'exists:languages,id'],
        ]);

        $this->mergeDraft($request, [
            'type' => $data['type'],
            'language_id' => (int) $data['language_id'],
        ]);

        return redirect()->route('exams.wizard.schedule');
    }

    public function schedule(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['school_class_id', 'type', 'language_id']);

        return view('exams.wizard-schedule', [
            'draft' => $this->draft($request),
            'class' => SchoolClass::findOrFail($this->draft($request)['school_class_id']),
            'language' => Language::findOrFail($this->draft($request)['language_id']),
            'teachers' => User::where('role', 'enseignant')->where('is_active', true)->orderBy('display_name')->get(),
        ]);
    }

    public function storeScheduleStep(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['school_class_id', 'type', 'language_id']);

        $data = $request->validate([
            'exam_date' => ['required', 'date'],
            'start_time' => ['required'],
            'room' => ['required', 'max:120'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'supervisor_name' => ['nullable', 'max:160'],
        ]);

        $this->mergeDraft($request, $data);

        return redirect()->route('exams.wizard.students');
    }

    public function students(Request $request, OralScheduleBuilder $scheduleBuilder, PlanningWarningService $warningService)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['school_class_id', 'type', 'language_id', 'exam_date', 'start_time', 'room']);
        $draft = $this->draft($request);
        $class = SchoolClass::with('students.languages')->findOrFail($draft['school_class_id']);
        $language = Language::findOrFail($draft['language_id']);
        $students = $this->eligibleStudents($class, (int) $draft['language_id'])->get();
        $slots = $this->buildDraftSlots($students, $draft, $scheduleBuilder);

        $this->mergeDraft($request, [
            'school_year_id' => $this->activeYear()->id,
            'breaks' => $this->schoolSetting()->oralBreaks(),
            'slots' => $slots,
        ]);
        $draft = $this->draft($request);

        return view('exams.wizard-students', [
            'draft' => $draft,
            'class' => $class,
            'language' => $language,
            'teacher' => !empty($draft['teacher_id']) ? User::find($draft['teacher_id']) : null,
            'slots' => $slots,
            'warnings' => $warningService->warnings($draft, $slots, $this->schoolSetting()),
        ]);
    }

    public function preview(Request $request)
    {
        return redirect()->route('exams.wizard.students');
    }

    public function confirm(Request $request)
    {
        $this->coordinator();
        $draft = $this->draft($request);
        $this->ensureDraftHas($request, ['type', 'school_class_id', 'language_id', 'exam_date', 'start_time', 'room', 'slots']);

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
        return $this->storeClassStep($request);
    }

    public function show(Exam $exam)
    {
        abort_unless(in_array($exam->school_class_id, $this->visibleClassIds(), true), 403);

        return view('exams.show', ['exam' => $exam->load(['schoolClass', 'language', 'teacher', 'slots.student', 'grades'])]);
    }

    public function catchup(Request $request, Exam $exam)
    {
        $this->coordinator();

        $data = $request->validate([
            'exam_date' => ['required', 'date'],
            'start_time' => ['required'],
            'room' => ['required'],
        ]);

        $absentStudentIds = Grade::where('exam_id', $exam->id)
            ->where('value', 'AB')
            ->whereNull('catchup_exam_id')
            ->pluck('student_id');

        if ($absentStudentIds->isEmpty()) {
            return back()->withErrors(['catchup' => 'Aucun élève AB sans rattrapage n’est disponible pour cette épreuve.']);
        }

        $catchup = Exam::create($exam->only([
            'school_year_id',
            'school_class_id',
            'language_id',
            'type',
            'teacher_id',
            'supervisor_name',
        ]) + $data + [
            'is_catchup' => true,
            'initial_exam_id' => $exam->id,
        ]);

        $this->buildSlots($catchup, $exam);

        Grade::where('exam_id', $exam->id)
            ->whereIn('student_id', $absentStudentIds)
            ->update(['catchup_exam_id' => $catchup->id]);

        return redirect()->route('exams.show', $catchup)->with('success', 'Rattrapage créé.');
    }

    private function buildSlots(Exam $exam, ?Exam $initial = null): void
    {
        $students = $initial
            ? Grade::where('exam_id', $initial->id)->where('value', 'AB')->whereNull('catchup_exam_id')->pluck('student_id')
            : $exam->schoolClass->students()
                ->whereHas('languages', fn ($q) => $q->where('languages.id', $exam->language_id))
                ->orderBy('last_name')
                ->pluck('id');

        foreach ($students->values() as $index => $studentId) {
            $passTime = $exam->type === 'oral'
                ? now()->setTimeFromTimeString($exam->start_time)->addMinutes($index * 15)->format('H:i:s')
                : null;

            ExamSlot::firstOrCreate(['exam_id' => $exam->id, 'student_id' => $studentId], ['pass_time' => $passTime]);
            Grade::firstOrCreate(['exam_id' => $exam->id, 'student_id' => $studentId]);
        }
    }

    private function eligibleStudents(SchoolClass $class, int $languageId)
    {
        return $class->students()
            ->whereHas('languages', fn ($query) => $query->where('languages.id', $languageId))
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    private function buildDraftSlots($students, array $draft, OralScheduleBuilder $scheduleBuilder): array
    {
        return $draft['type'] === 'oral'
            ? $scheduleBuilder->build($students, $draft['exam_date'], $draft['start_time'], $this->schoolSetting()->oralBreaks())
            : $students->map(fn ($student) => [
                'student_id' => $student->id,
                'last_name' => $student->last_name,
                'first_name' => $student->first_name,
                'extra_time' => $student->extra_time,
                'pass_time' => null,
            ])->all();
    }

    private function draft(Request $request): array
    {
        return $request->session()->get('exam_planning_draft', []);
    }

    private function mergeDraft(Request $request, array $data): void
    {
        $request->session()->put('exam_planning_draft', array_replace($this->draft($request), $data));
    }

    private function ensureDraftHas(Request $request, array $keys): void
    {
        $draft = $this->draft($request);

        foreach ($keys as $key) {
            if (!array_key_exists($key, $draft)) {
                throw new HttpResponseException(redirect()->route('exams.create'));
            }
        }
    }
}
