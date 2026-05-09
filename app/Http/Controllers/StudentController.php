<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return view('students.index', ['students' => Student::with(['schoolClass', 'languages'])->whereIn('school_class_id', $this->visibleClassIds())->orderBy('last_name')->get()]);
    }

    public function create()
    {
        $this->coordinator();
        return $this->form(new Student());
    }

    public function edit(Student $student)
    {
        $this->coordinator();
        return $this->form($student);
    }

    public function store(Request $request)
    {
        $this->coordinator();
        $student = Student::create($this->validated($request));
        $student->languages()->sync($request->input('language_ids', []));
        return redirect()->route('students.index')->with('success', 'Élève ajouté.');
    }

    public function update(Request $request, Student $student)
    {
        $this->coordinator();
        $student->update($this->validated($request));
        $student->languages()->sync($request->input('language_ids', []));
        return redirect()->route('students.index')->with('success', 'Élève mis à jour.');
    }

    public function destroy(Student $student)
    {
        $this->coordinator();
        $student->delete();
        return back()->with('success', 'Élève supprimé.');
    }

    private function form(Student $student)
    {
        return view('students.form', [
            'student' => $student,
            'classes' => SchoolClass::where('school_year_id', $this->activeYear()?->id)->orderBy('name')->get(),
            'languages' => Language::where('is_active', true)->orderBy('sort_order')->get(),
            'selectedLanguages' => $student->exists ? $student->languages()->pluck('languages.id')->all() : [],
        ]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'last_name' => ['required', 'max:120'],
            'first_name' => ['required', 'max:120'],
            'birth_date' => ['nullable', 'date'],
            'email' => ['nullable', 'email'],
            'language_ids' => ['array'],
        ]);
        $data['last_name'] = mb_strtoupper($data['last_name'], 'UTF-8');
        $data['extra_time'] = $request->boolean('extra_time');
        unset($data['language_ids']);
        return $data;
    }
}
