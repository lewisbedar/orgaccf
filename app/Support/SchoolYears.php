<?php

namespace App\Support;

use Carbon\CarbonInterface;

class SchoolYears
{
    public static function proposed(?CarbonInterface $date = null): string
    {
        $date ??= now();
        $start = $date->month >= 8 ? $date->year : $date->year - 1;
        return $start . '-' . ($start + 1);
    }

    public static function datesFor(string $label): array
    {
        $start = (int) substr($label, 0, 4);
        return [$start . '-09-01', ($start + 1) . '-08-31'];
    }
}
