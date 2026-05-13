<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\SchoolClass;
use App\Services\PronoteCsvImporter;
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

    public function create()
    {
        $this->coordinator();

        return view('classes.wizard', $this->wizardData());
    }

    public function preview(Request $request, PronoteCsvImporter $importer)
    {
        $this->coordinator();

        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'language_ids' => ['required', 'array', 'min:1'],
            'language_ids.*' => ['exists:languages,id'],
            'creation_method' => ['required', 'in:pronote,manual'],
            'pronote_csv' => ['nullable', 'file', 'mimes:csv,txt'],
            'manual_students' => ['array'],
            'manual_students.*.last_name' => ['nullable', 'max:120'],
            'manual_students.*.first_name' => ['nullable', 'max:120'],
            'manual_students.*.birth_date' => ['nullable', 'date'],
            'manual_students.*.email' => ['nullable', 'email'],
            'manual_students.*.extra_time' => ['nullable', 'boolean'],
        ]);

        if ($data['creation_method'] === 'pronote' && !$request->hasFile('pronote_csv')) {
            return back()->withErrors(['pronote_csv' => 'Déposez un fichier CSV Pronote pour continuer.'])->withInput();
        }

        $students = $data['creation_method'] === 'pronote'
            ? $importer->parse($request->file('pronote_csv')->getRealPath())
            : $this->manualRows($data['manual_students'] ?? []);

        $draft = [
            'name' => $data['name'],
            'language_ids' => array_map('intval', $data['language_ids']),
            'source' => $data['creation_method'],
            'students' => $students,
            'file_name' => $request->file('pronote_csv')?->getClientOriginalName(),
        ];

        $request->session()->put('class_import_draft', $draft);

        return view('classes.preview', $this->wizardData() + [
            'draft' => $draft,
            'selectedLanguages' => Language::whereIn('id', $draft['language_ids'])->orderBy('sort_order')->get(),
        ]);
    }

    public function confirm(Request $request)
    {
        $this->coordinator();

        abort_unless($request->session()->has('class_import_draft'), 419);

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
        $this->coordinator();

        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'language_ids' => ['required', 'array', 'min:1'],
            'language_ids.*' => ['exists:languages,id'],
        ]);

        $class = SchoolClass::create([
            'name' => $data['name'],
            'school_year_id' => $this->activeYear()->id,
            'color' => $this->nextClassColor(),
            'language_ids' => array_map('intval', $data['language_ids']),
        ]);

        return redirect()->route('classes.show', $class)->with('success', 'Classe créée.');
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
