<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Language;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Services\GradeSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unjustified_regular_absence_is_not_eliminatory_before_catchup(): void
    {
        [$year, $class, $language, $student] = $this->makeStudentContext();

        $writtenExam = $this->makeExam($year, $class, $language, 'ecrit');
        $oralExam = $this->makeExam($year, $class, $language, 'oral');

        Grade::create([
            'exam_id' => $writtenExam->id,
            'student_id' => $student->id,
            'value' => 'AB',
            'absence_reason' => 'injustifiee',
        ]);
        Grade::create([
            'exam_id' => $oralExam->id,
            'student_id' => $student->id,
            'value' => '7.00',
            'numeric_value' => 7,
        ]);

        $summary = app(GradeSummaryService::class)->forYear($year, [$class->id])->first();

        $this->assertSame('incomplet', $summary['status']);
        $this->assertStringContainsString('rattrapage à prévoir', $summary['written']['label']);
    }

    public function test_unjustified_catchup_absence_is_eliminatory(): void
    {
        [$year, $class, $language, $student] = $this->makeStudentContext();

        $writtenExam = $this->makeExam($year, $class, $language, 'ecrit');
        $catchupExam = $this->makeExam($year, $class, $language, 'ecrit', true, $writtenExam->id);
        $oralExam = $this->makeExam($year, $class, $language, 'oral');

        Grade::create([
            'exam_id' => $writtenExam->id,
            'student_id' => $student->id,
            'value' => 'AB',
            'absence_reason' => 'justifiee',
            'catchup_exam_id' => $catchupExam->id,
        ]);
        Grade::create([
            'exam_id' => $catchupExam->id,
            'student_id' => $student->id,
            'value' => 'AB',
            'absence_reason' => 'injustifiee',
        ]);
        Grade::create([
            'exam_id' => $oralExam->id,
            'student_id' => $student->id,
            'value' => '7.00',
            'numeric_value' => 7,
        ]);

        $summary = app(GradeSummaryService::class)->forYear($year, [$class->id])->first();

        $this->assertSame('eliminatoire', $summary['status']);
        $this->assertSame('AB injustifiée', $summary['written']['label']);
        $this->assertNull($summary['total']);
    }

    private function makeStudentContext(): array
    {
        $year = SchoolYear::create([
            'label' => '2025-2026',
            'starts_on' => '2025-09-01',
            'ends_on' => '2026-08-31',
            'is_active' => true,
        ]);
        $class = SchoolClass::create([
            'school_year_id' => $year->id,
            'name' => 'T AGOrA',
        ]);
        $language = Language::create([
            'name' => 'Anglais',
            'level' => 'LVA',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $student = Student::create([
            'school_class_id' => $class->id,
            'last_name' => 'DUPONT',
            'first_name' => 'Camille',
        ]);
        $student->languages()->attach($language->id);

        return [$year, $class, $language, $student];
    }

    private function makeExam(SchoolYear $year, SchoolClass $class, Language $language, string $type, bool $catchup = false, ?int $initialExamId = null): Exam
    {
        return Exam::create([
            'school_year_id' => $year->id,
            'school_class_id' => $class->id,
            'language_id' => $language->id,
            'type' => $type,
            'exam_date' => '2026-05-12',
            'start_time' => '09:00',
            'room' => '101',
            'is_catchup' => $catchup,
            'initial_exam_id' => $initialExamId,
        ]);
    }
}
