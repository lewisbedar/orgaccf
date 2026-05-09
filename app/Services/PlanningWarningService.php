<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\SchoolSetting;
use Carbon\CarbonImmutable;

class PlanningWarningService
{
    public function warnings(array $draft, array $slots, SchoolSetting $school): array
    {
        return array_merge(
            $this->calendarWarnings($draft['exam_date'], $school->academic_zone ?? 'C'),
            $this->conflictWarnings($draft, $slots)
        );
    }

    private function calendarWarnings(string $date, string $zone): array
    {
        $warnings = [];
        $day = CarbonImmutable::parse($date)->startOfDay();
        $isoDate = $day->toDateString();

        if ($holiday = $this->publicHolidays((int) $day->format('Y'))[$isoDate] ?? null) {
            $warnings[] = [
                'type' => 'calendar',
                'title' => 'Jour férié',
                'message' => $day->format('d/m/Y') . ' est un jour férié : ' . $holiday . '.',
            ];
        }

        foreach ($this->schoolBreaks($zone) as $period) {
            $start = CarbonImmutable::parse($period['start'])->startOfDay();
            $end = CarbonImmutable::parse($period['end'])->startOfDay();
            if ($day->greaterThanOrEqualTo($start) && $day->lessThan($end)) {
                $warnings[] = [
                    'type' => 'calendar',
                    'title' => 'Vacances scolaires',
                    'message' => $day->format('d/m/Y') . ' tombe pendant ' . $period['name'] . ' en zone ' . $zone . '.',
                ];
            }
        }

        foreach ($this->schoolClosures() as $closure) {
            if ($isoDate === $closure['date']) {
                $warnings[] = [
                    'type' => 'calendar',
                    'title' => 'Journée sans classe',
                    'message' => $day->format('d/m/Y') . ' est signalé comme journée sans classe : ' . $closure['name'] . '.',
                ];
            }
        }

        return $warnings;
    }

    private function conflictWarnings(array $draft, array $slots): array
    {
        $warnings = [];
        $proposedIntervals = $this->draftIntervals($draft, $slots);
        $exams = Exam::with('slots')
            ->where('school_year_id', $draft['school_year_id'] ?? null)
            ->whereDate('exam_date', $draft['exam_date'])
            ->get();

        foreach ($exams as $exam) {
            $existingIntervals = $this->examIntervals($exam);
            if (!$this->overlaps($proposedIntervals, $existingIntervals)) {
                continue;
            }

            if (mb_strtolower(trim($exam->room)) === mb_strtolower(trim($draft['room']))) {
                $warnings[] = $this->conflict('Salle déjà occupée', 'La salle ' . $draft['room'] . ' est déjà utilisée sur un créneau qui se chevauche.');
            }

            if ((int) $exam->school_class_id === (int) $draft['school_class_id']) {
                $warnings[] = $this->conflict('Classe déjà planifiée', 'Cette classe a déjà une épreuve sur un créneau qui se chevauche.');
            }

            if (!empty($draft['teacher_id']) && (int) $exam->teacher_id === (int) $draft['teacher_id']) {
                $warnings[] = $this->conflict('Jury déjà occupé', 'Cet examinateur a déjà une épreuve sur un créneau qui se chevauche.');
            }
        }

        return collect($warnings)->unique(fn (array $warning) => $warning['title'] . $warning['message'])->values()->all();
    }

    private function draftIntervals(array $draft, array $slots): array
    {
        if ($draft['type'] === 'ecrit') {
            $start = CarbonImmutable::parse($draft['exam_date'] . ' ' . $draft['start_time']);
            return [['start' => $start, 'end' => $start->addHour()]];
        }

        return collect($slots)
            ->filter(fn (array $slot) => !empty($slot['pass_time']))
            ->map(fn (array $slot) => [
                'start' => CarbonImmutable::parse($draft['exam_date'] . ' ' . $slot['pass_time']),
                'end' => CarbonImmutable::parse($draft['exam_date'] . ' ' . $slot['pass_time'])->addMinutes(15),
            ])
            ->all();
    }

    private function examIntervals(Exam $exam): array
    {
        if ($exam->type === 'ecrit') {
            $start = CarbonImmutable::parse($exam->exam_date->format('Y-m-d') . ' ' . $exam->start_time);
            return [['start' => $start, 'end' => $start->addHour()]];
        }

        return $exam->slots
            ->filter(fn ($slot) => $slot->pass_time)
            ->map(fn ($slot) => [
                'start' => CarbonImmutable::parse($exam->exam_date->format('Y-m-d') . ' ' . $slot->pass_time),
                'end' => CarbonImmutable::parse($exam->exam_date->format('Y-m-d') . ' ' . $slot->pass_time)->addMinutes(15),
            ])
            ->all();
    }

    private function overlaps(array $left, array $right): bool
    {
        foreach ($left as $a) {
            foreach ($right as $b) {
                if ($a['start']->lessThan($b['end']) && $b['start']->lessThan($a['end'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function conflict(string $title, string $message): array
    {
        return ['type' => 'conflict', 'title' => $title, 'message' => $message];
    }

    private function publicHolidays(int $year): array
    {
        $easter = $this->easterSunday($year);

        return [
            "$year-01-01" => "Jour de l'An",
            $easter->addDay()->toDateString() => 'Lundi de Pâques',
            "$year-05-01" => 'Fête du Travail',
            "$year-05-08" => 'Victoire 1945',
            $easter->addDays(39)->toDateString() => 'Ascension',
            $easter->addDays(50)->toDateString() => 'Lundi de Pentecôte',
            "$year-07-14" => 'Fête nationale',
            "$year-08-15" => 'Assomption',
            "$year-11-01" => 'Toussaint',
            "$year-11-11" => 'Armistice 1918',
            "$year-12-25" => 'Noël',
        ];
    }

    private function easterSunday(int $year): CarbonImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return CarbonImmutable::create($year, $month, $day)->startOfDay();
    }

    private function schoolBreaks(string $zone): array
    {
        $common = [
            ['name' => 'les vacances de la Toussaint 2025', 'start' => '2025-10-18', 'end' => '2025-11-03'],
            ['name' => 'les vacances de Noël 2025', 'start' => '2025-12-20', 'end' => '2026-01-05'],
            ['name' => 'les vacances d’été 2026', 'start' => '2026-07-04', 'end' => '2026-09-01'],
            ['name' => 'les vacances de la Toussaint 2026', 'start' => '2026-10-17', 'end' => '2026-11-02'],
            ['name' => 'les vacances de Noël 2026', 'start' => '2026-12-19', 'end' => '2027-01-04'],
            ['name' => 'les vacances d’été 2027', 'start' => '2027-07-03', 'end' => '2027-09-01'],
        ];

        $byZone = [
            'A' => [
                ['name' => "les vacances d'hiver 2026", 'start' => '2026-02-07', 'end' => '2026-02-23'],
                ['name' => 'les vacances de printemps 2026', 'start' => '2026-04-04', 'end' => '2026-04-20'],
                ['name' => "les vacances d'hiver 2027", 'start' => '2027-02-13', 'end' => '2027-03-01'],
                ['name' => 'les vacances de printemps 2027', 'start' => '2027-04-10', 'end' => '2027-04-26'],
            ],
            'B' => [
                ['name' => "les vacances d'hiver 2026", 'start' => '2026-02-14', 'end' => '2026-03-02'],
                ['name' => 'les vacances de printemps 2026', 'start' => '2026-04-11', 'end' => '2026-04-27'],
                ['name' => "les vacances d'hiver 2027", 'start' => '2027-02-20', 'end' => '2027-03-08'],
                ['name' => 'les vacances de printemps 2027', 'start' => '2027-04-17', 'end' => '2027-05-03'],
            ],
            'C' => [
                ['name' => "les vacances d'hiver 2026", 'start' => '2026-02-21', 'end' => '2026-03-09'],
                ['name' => 'les vacances de printemps 2026', 'start' => '2026-04-18', 'end' => '2026-05-04'],
                ['name' => "les vacances d'hiver 2027", 'start' => '2027-02-06', 'end' => '2027-02-22'],
                ['name' => 'les vacances de printemps 2027', 'start' => '2027-04-03', 'end' => '2027-04-19'],
            ],
        ];

        return array_merge($common, $byZone[$zone] ?? $byZone['C']);
    }

    private function schoolClosures(): array
    {
        return [
            ['date' => '2026-05-15', 'name' => "pont de l'Ascension"],
            ['date' => '2026-05-16', 'name' => "pont de l'Ascension"],
            ['date' => '2027-05-07', 'name' => "pont de l'Ascension"],
        ];
    }
}
