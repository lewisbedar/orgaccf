<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\SchoolClass;
use App\Services\PronoteCsvImporter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassController extends Controller
{
    public function index()
    {
        return view('classes.index', [
            'classes' => SchoolClass::whereIn('id', $this->visibleClassIds())
                ->withCount('students')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->coordinator();

        if ($request->boolean('reset')) {
            $request->session()->forget('class_import_draft');
        }

        if (!$request->session()->has('class_import_draft')) {
            $request->session()->put('class_import_draft', ['students' => []]);
        }

        return view('classes.wizard-class', $this->wizardData() + [
            'draft' => $this->draft($request),
        ]);
    }

    public function storeClassStep(Request $request)
    {
        $this->coordinator();

        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'language_ids' => ['required', 'array', 'min:1'],
            'language_ids.*' => ['exists:languages,id'],
        ]);

        $this->mergeDraft($request, [
            'name' => $data['name'],
            'language_ids' => array_map('intval', $data['language_ids']),
        ]);

        return redirect()->route('classes.wizard.method');
    }

    public function method(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['name', 'language_ids']);

        return view('classes.wizard-method', [
            'draft' => $this->draft($request),
        ]);
    }

    public function storeMethodStep(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['name', 'language_ids']);

        $data = $request->validate([
            'creation_method' => ['required', 'in:pronote,manual'],
        ]);

        $this->mergeDraft($request, [
            'source' => $data['creation_method'],
            'students' => [],
            'file_name' => null,
        ]);

        return redirect()->route($data['creation_method'] === 'pronote'
            ? 'classes.wizard.pronote'
            : 'classes.wizard.manual');
    }

    public function pronote(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['name', 'language_ids', 'source']);

        return view('classes.wizard-pronote', [
            'draft' => $this->draft($request),
        ]);
    }

    public function storePronoteStep(Request $request, PronoteCsvImporter $importer)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['name', 'language_ids', 'source']);

        $data = $request->validate([
            'pronote_csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->mergeDraft($request, [
            'students' => $importer->parse($data['pronote_csv']->getRealPath()),
            'file_name' => $data['pronote_csv']->getClientOriginalName(),
        ]);

        return redirect()->route('classes.preview');
    }

    public function manual(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['name', 'language_ids', 'source']);

        return view('classes.wizard-manual', [
            'draft' => $this->draft($request),
        ]);
    }

    public function storeManualStep(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['name', 'language_ids', 'source']);

        $data = $request->validate([
            'manual_students' => ['array'],
            'manual_students.*.last_name' => ['nullable', 'max:120'],
            'manual_students.*.first_name' => ['nullable', 'max:120'],
            'manual_students.*.birth_date' => ['nullable', 'date'],
            'manual_students.*.email' => ['nullable', 'email'],
            'manual_students.*.extra_time' => ['nullable', 'boolean'],
        ]);

        $this->mergeDraft($request, [
            'students' => $this->manualRows($data['manual_students'] ?? []),
            'file_name' => null,
        ]);

        return redirect()->route('classes.preview');
    }

    public function preview(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['name', 'language_ids', 'source']);

        $draft = $this->draft($request);

        return view('classes.preview', $this->wizardData() + [
            'draft' => $draft,
            'selectedLanguages' => Language::whereIn('id', $draft['language_ids'])->orderBy('sort_order')->get(),
        ]);
    }

    public function confirm(Request $request)
    {
        $this->coordinator();
        $this->ensureDraftHas($request, ['name', 'language_ids', 'source']);

        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'language_ids' => ['required', 'array', 'min:1'],
            'language_ids.*' => ['exists:languages,id'],
            'students' => ['array'],
            'students.*.include' => ['nullable', 'boolean'],
            'students.*.last_name' => ['nullable', 'max:120'],
            'students.*.first_name' => ['nullable', 'max:120'],
            'students.*.birth_date' => ['nullable', 'date'],
            'students.*.email' => ['nullable', 'email'],
            'students.*.extra_time' => ['nullable', 'boolean'],
            'students.*.pronote_options' => ['nullable', 'max:255'],
        ]);

        $rows = collect($data['students'] ?? [])
            ->filter(fn (array $row) => !empty($row['include']))
            ->map(fn (array $row) => [
                'last_name' => mb_strtoupper(trim((string) ($row['last_name'] ?? '')), 'UTF-8'),
                'first_name' => trim((string) ($row['first_name'] ?? '')),
                'birth_date' => $row['birth_date'] ?? null,
                'email' => $row['email'] ?? null,
                'extra_time' => !empty($row['extra_time']),
                'pronote_options' => $row['pronote_options'] ?? null,
            ])
            ->filter(fn (array $row) => $row['last_name'] !== '' && $row['first_name'] !== '')
            ->values();

        Validator::make(['students' => $rows->all()], [
            'students.*.last_name' => ['required', 'max:120'],
            'students.*.first_name' => ['required', 'max:120'],
        ])->validate();

        $class = SchoolClass::create([
            'name' => $data['name'],
            'school_year_id' => $this->activeYear()->id,
            'color' => $this->nextClassColor(),
            'language_ids' => array_map('intval', $data['language_ids']),
        ]);

        foreach ($rows as $row) {
            $student = $class->students()->create($row);
            $student->languages()->sync($data['language_ids']);
        }

        $request->session()->forget('class_import_draft');

        return redirect()
            ->route('classes.show', $class)
            ->with('success', 'Classe créée avec ' . $rows->count() . ' élève(s).');
    }

    public function store(Request $request)
    {
        return $this->storeClassStep($request);
    }

    public function show(SchoolClass $class)
    {
        abort_unless(in_array($class->id, $this->visibleClassIds(), true), 403);

        return view('classes.show', ['class' => $class->load(['students.languages', 'schoolYear'])]);
    }

    private function wizardData(): array
    {
        return [
            'languages' => Language::where('is_active', true)->orderBy('sort_order')->get(),
            'year' => $this->activeYear(),
        ];
    }

    private function draft(Request $request): array
    {
        return $request->session()->get('class_import_draft', ['students' => []]);
    }

    private function mergeDraft(Request $request, array $data): void
    {
        $request->session()->put('class_import_draft', array_replace($this->draft($request), $data));
    }

    private function ensureDraftHas(Request $request, array $keys): void
    {
        $draft = $this->draft($request);

        foreach ($keys as $key) {
            if (!array_key_exists($key, $draft)) {
                throw new HttpResponseException(redirect()->route('classes.create'));
            }
        }
    }

    private function manualRows(array $rows): array
    {
        return collect($rows)
            ->map(fn (array $row) => [
                'include' => true,
                'last_name' => trim((string) ($row['last_name'] ?? '')),
                'first_name' => trim((string) ($row['first_name'] ?? '')),
                'birth_date' => $row['birth_date'] ?? null,
                'email' => $row['email'] ?? null,
                'extra_time' => !empty($row['extra_time']),
                'pronote_options' => null,
            ])
            ->filter(fn (array $row) => $row['last_name'] !== '' || $row['first_name'] !== '' || $row['email'] !== '')
            ->values()
            ->all();
    }

    private function nextClassColor(): string
    {
        $palette = ['#2563A6', '#7C3AED', '#2F855A', '#C76A1A', '#B83280', '#0F766E', '#4F46E5', '#B91C1C'];
        $count = SchoolClass::where('school_year_id', $this->activeYear()?->id)->count();

        return $palette[$count % count($palette)];
    }
}
