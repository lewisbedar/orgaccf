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
        return SchoolYear::active();
    }

    protected function schoolSetting(): SchoolSetting
    {
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
