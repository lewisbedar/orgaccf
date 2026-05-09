<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\SchoolClass;
use App\Services\PronoteCsvImporter;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        return view('classes.index', ['classes' => SchoolClass::whereIn('id', $this->visibleClassIds())->withCount('students')->orderBy('name')->get()]);
    }

    public function create()
    {
        $this->coordinator();
        return view('classes.wizard', ['languages' => Language::where('is_active', true)->orderBy('sort_order')->get(), 'year' => $this->activeYear()]);
    }

    public function store(Request $request, PronoteCsvImporter $importer)
    {
        $this->coordinator();
        $data = $request->validate(['name' => ['required', 'max:120'], 'language_ids' => ['required', 'array'], 'pronote_csv' => ['nullable', 'file', 'mimes:csv,txt']]);
        $class = SchoolClass::create(['name' => $data['name'], 'school_year_id' => $this->activeYear()->id, 'language_ids' => array_map('intval', $data['language_ids'])]);

        if ($request->hasFile('pronote_csv')) {
            foreach ($importer->parse($request->file('pronote_csv')->getRealPath()) as $row) {
                $student = $class->students()->create($row);
                $student->languages()->sync($data['language_ids']);
            }
        }

        return redirect()->route('classes.show', $class)->with('success', 'Classe créée.');
    }

    public function show(SchoolClass $class)
    {
        abort_unless(in_array($class->id, $this->visibleClassIds(), true), 403);
        return view('classes.show', ['class' => $class->load(['students.languages', 'schoolYear'])]);
    }
}
