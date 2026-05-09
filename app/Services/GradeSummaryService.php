<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Support\Collection;

class GradeSummaryService
{
    public const WRITTEN_MAX = 12;
    public const ORAL_MAX = 8;

    public static function maxForType(string $type): int
    {
        return $type === 'oral' ? self::ORAL_MAX : self::WRITTEN_MAX;
    }

    public function forYear(?SchoolYear $year, array $classIds): Collection
    {
        if (!$year || $classIds === []) {
            return collect();
        }

        $students = Student::with(['schoolClass', 'languages'])
            ->whereIn('school_class_id', $classIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $gradesByStudent = Grade::with(['exam.language'])
            ->whereIn('student_id', $students->pluck('id'))
            ->whereHas('exam', fn ($query) => $query->where('school_year_id', $year->id))
            ->get()
            ->groupBy('student_id');

        return $students->flatMap(function (Student $student) use ($gradesByStudent) {
            $studentGrades = $gradesByStudent->get($student->id, collect());

            return $student->languages->map(function ($language) use ($student, $studentGrades) {
                $languageGrades = $studentGrades
                    ->filter(fn (Grade $grade) => $grade->exam?->language_id === $language->id
                        && $grade->exam?->school_class_id === $student->school_class_id);

                $written = $this->component($languageGrades, 'ecrit');
                $oral = $this->component($languageGrades, 'oral');
                $isEliminatory = $written['eliminatory'] || $oral['eliminatory'];
                $isComplete = !$written['missing'] && !$oral['missing'];

                return [
                    'student' => $student,
                    'language' => $language,
                    'written' => $written,
                    'oral' => $oral,
                    'total' => $isComplete && !$isEliminatory ? $written['score'] + $oral['score'] : null,
                    'status' => $isEliminatory ? 'eliminatoire' : ($isComplete ? 'complet' : 'incomplet'),
                ];
            });
        })->values();
    }

    private function component(Collection $grades, string $type): array
    {
        $componentGrades = $grades
            ->filter(fn (Grade $grade) => $grade->exam?->type === $type)
            ->sortBy(fn (Grade $grade) => sprintf(
                '%d-%s-%010d',
                $grade->exam?->is_catchup ? 1 : 0,
                $grade->exam?->exam_date?->format('Y-m-d') ?? '',
                $grade->id
            ))
            ->values();

        $regularGrade = $componentGrades->first(fn (Grade $grade) => !$grade->exam?->is_catchup);

        if ($regularGrade?->value === 'AB' && $regularGrade->catchup_exam_id) {
            $catchupGrade = $componentGrades->first(fn (Grade $grade) => $grade->exam_id === $regularGrade->catchup_exam_id);

            if ($catchupGrade) {
                return $this->stateFromGrade($catchupGrade);
            }
        }

        if ($regularGrade) {
            return $this->stateFromGrade($regularGrade);
        }

        $catchupOnlyGrade = $componentGrades->first(fn (Grade $grade) => $grade->exam?->is_catchup);

        return $catchupOnlyGrade
            ? $this->stateFromGrade($catchupOnlyGrade)
            : $this->emptyState();
    }

    private function stateFromGrade(Grade $grade): array
    {
        if ($grade->value === 'AB') {
            $reason = $grade->absence_reason === 'injustifiee'
                ? 'injustifiee'
                : ($grade->absence_reason === 'justifiee' ? 'justifiee' : null);

            return [
                'score' => null,
                'label' => match ($reason) {
                    'injustifiee' => 'AB injustifiée',
                    'justifiee' => 'AB justifiée',
                    default => 'AB',
                },
                'missing' => true,
                'eliminatory' => $reason === 'injustifiee',
            ];
        }

        if ($grade->numeric_value === null) {
            return $this->emptyState();
        }

        return [
            'score' => (float) $grade->numeric_value,
            'label' => $this->formatScore((float) $grade->numeric_value),
            'missing' => false,
            'eliminatory' => false,
        ];
    }

    private function emptyState(): array
    {
        return [
            'score' => null,
            'label' => '-',
            'missing' => true,
            'eliminatory' => false,
        ];
    }

    public function formatScore(float $score): string
    {
        return rtrim(rtrim(number_format($score, 2, ',', ' '), '0'), ',');
    }
}
