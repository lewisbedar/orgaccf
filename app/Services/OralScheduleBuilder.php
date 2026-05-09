<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class OralScheduleBuilder
{
    public function build(Collection $students, string $date, string $startTime, array $breaks = []): array
    {
        $current = CarbonImmutable::parse($date . ' ' . $startTime);
        $breaks = $this->normalizeBreaks($date, $breaks);

        return $students->values()->map(function ($student) use (&$current, $breaks) {
            $current = $this->skipBreaks($current, $breaks);
            $slot = [
                'student_id' => $student->id,
                'last_name' => $student->last_name,
                'first_name' => $student->first_name,
                'extra_time' => $student->extra_time,
                'pass_time' => $current->format('H:i:s'),
            ];

            $current = $this->skipBreaks($current->addMinutes(15), $breaks);

            return $slot;
        })->all();
    }

    private function normalizeBreaks(string $date, array $breaks): array
    {
        return collect($breaks)
            ->filter(fn (array $break) => !empty($break['start']) && !empty($break['end']) && $break['start'] < $break['end'])
            ->map(fn (array $break) => [
                'label' => $break['label'] ?? 'Pause',
                'start' => CarbonImmutable::parse($date . ' ' . $break['start']),
                'end' => CarbonImmutable::parse($date . ' ' . $break['end']),
            ])
            ->sortBy('start')
            ->values()
            ->all();
    }

    private function skipBreaks(CarbonImmutable $time, array $breaks): CarbonImmutable
    {
        foreach ($breaks as $break) {
            if ($time->greaterThanOrEqualTo($break['start']) && $time->lessThan($break['end'])) {
                return $this->skipBreaks($break['end'], $breaks);
            }
        }

        return $time;
    }
}
