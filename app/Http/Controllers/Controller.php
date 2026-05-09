<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function coordinator(): void
    {
        abort_unless(Auth::user()?->isCoordinator(), 403);
    }

    protected function activeYear(): ?SchoolYear
    {
        if (session()->has('school_year_id')) {
            $selectedYear = SchoolYear::find(session('school_year_id'));

            if ($selectedYear) {
                return $selectedYear;
            }
        }

        return SchoolYear::active();
    }

    protected function schoolSetting(): SchoolSetting
    {
        if (session()->has('school_setting_id')) {
            $selectedSchool = SchoolSetting::find(session('school_setting_id'));

            if ($selectedSchool) {
                return $selectedSchool;
            }
        }

        return SchoolSetting::current();
    }

    protected function visibleClassIds(): array
    {
        $year = $this->activeYear();
        if (!$year) {
            return [];
        }

        if (Auth::user()?->isCoordinator()) {
            return SchoolClass::where('school_year_id', $year->id)->pluck('id')->all();
        }

        return Auth::user()->assignments()->pluck('school_class_id')->unique()->values()->all();
    }
}
