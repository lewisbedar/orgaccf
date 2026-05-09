<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\SchoolYear;
use App\Support\SchoolYears;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function firstYear()
    {
        $this->coordinator();
        return view('settings.year', ['proposal' => SchoolYears::proposed()]);
    }

    public function storeFirstYear(Request $request)
    {
        $this->coordinator();
        $request->validate(['label' => ['required', 'regex:/^\d{4}-\d{4}$/']]);
        $this->openYear($request->string('label')->toString());
        return redirect()->route('dashboard')->with('success', 'Année scolaire active créée.');
    }

    public function school()
    {
        $this->coordinator();
        return view('settings.school', ['school' => $this->schoolSetting(), 'year' => $this->activeYear(), 'proposal' => SchoolYears::proposed()]);
    }

    public function updateSchool(Request $request)
    {
        $this->coordinator();
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg'],
        ]);
        if ($request->hasFile('logo')) {
            $data['logo_path'] = '/storage/' . $request->file('logo')->store('logos', 'public');
        }
        SchoolSetting::current()->update($data);
        return back()->with('success', 'Paramètres établissement enregistrés.');
    }

    public function closeYear()
    {
        $this->coordinator();
        $this->activeYear()?->update(['is_active' => false, 'closed_at' => now()]);
        return back()->with('success', 'Année scolaire clôturée.');
    }

    public function newYear(Request $request)
    {
        $this->coordinator();
        $request->validate(['label' => ['required', 'regex:/^\d{4}-\d{4}$/']]);
        $old = $this->activeYear();
        $new = $this->openYear($request->string('label')->toString());

        if ($request->boolean('duplicate_classes') && $old) {
            SchoolClass::where('school_year_id', $old->id)->get()->each(fn ($class) => SchoolClass::create([
                'school_year_id' => $new->id,
                'name' => $class->name,
                'language_ids' => $class->language_ids,
            ]));
        }
        return back()->with('success', 'Nouvelle année scolaire ouverte.');
    }

    private function openYear(string $label): SchoolYear
    {
        [$starts, $ends] = SchoolYears::datesFor($label);
        SchoolYear::query()->update(['is_active' => false]);
        $year = SchoolYear::updateOrCreate(['label' => $label], ['starts_on' => $starts, 'ends_on' => $ends, 'is_active' => true, 'closed_at' => null]);
        SchoolSetting::current()->update(['active_school_year_id' => $year->id]);
        return $year;
    }
}
