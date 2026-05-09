@extends('layouts.app', ['title' => 'Notes'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Notes</h1>
        <p class="muted">Sélectionnez une épreuve pour saisir ou consulter les notes des élèves concernés.</p>
    </div>
</section>

<form method="get" action="{{ route('grades.summary') }}" class="panel form-grid">
    <label>Épreuve
        <select name="exam_id" required>
            <option value="">Choisir une épreuve</option>
            @foreach($exams as $exam)
                <option value="{{ $exam->id }}" @selected($selectedExam?->id === $exam->id)>
                    {{ $exam->exam_date->format('d/m/Y') }} {{ substr($exam->start_time, 0, 5) }} ·
                    {{ $exam->type === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}{{ $exam->is_catchup ? ' rattrapage' : '' }} ·
                    {{ $exam->schoolClass->name }} · {{ $exam->language->label() }}
                </option>
            @endforeach
        </select>
    </label>
    <div class="form-actions actions">
        <button>Afficher</button>
        @if($selectedExam)
            <a class="button" href="{{ route('grades.edit', $selectedExam) }}">Saisir les notes</a>
            <a class="button" target="_blank" href="{{ route('documents.grades', ['exam_id' => $selectedExam->id]) }}">Version imprimable</a>
        @endif
    </div>
</form>

@if($selectedExam)
    <section class="panel meta-grid">
        <div><span>Type</span><strong>{{ $selectedExam->type === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}{{ $selectedExam->is_catchup ? ' de rattrapage' : '' }}</strong></div>
        <div><span>Classe</span><strong><span class="class-pill" style="--class-color: {{ $selectedExam->schoolClass->displayColor() }}">{{ $selectedExam->schoolClass->name }}</span></strong></div>
        <div><span>Langue</span><strong>{{ $selectedExam->language->label() }}</strong></div>
        <div><span>Date</span><strong>{{ $selectedExam->exam_date->format('d/m/Y') }} {{ substr($selectedExam->start_time, 0, 5) }}</strong></div>
        <div><span>Barème</span><strong>/ {{ $maxScore }}</strong></div>
    </section>

    <table>
        <thead>
            <tr>
                @if($selectedExam->type === 'oral')<th>Horaire</th>@endif
                <th>Nom</th>
                <th>Prénom</th>
                @if($selectedExam->type === 'ecrit')<th>Tiers-temps</th>@endif
                <th>Note / {{ $maxScore }}</th>
                <th>Absence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($selectedExam->slots as $slot)
                @php($grade = $selectedExam->grades->firstWhere('student_id', $slot->student_id))
                <tr>
                    @if($selectedExam->type === 'oral')<td>{{ substr($slot->pass_time, 0, 5) }}</td>@endif
                    <td>{{ $slot->student->last_name }}</td>
                    <td>{{ $slot->student->first_name }}</td>
                    @if($selectedExam->type === 'ecrit')<td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td>@endif
                    <td>{{ $grade?->value ?: '-' }}</td>
                    <td>
                        @if($grade?->value === 'AB')
                            {{ $grade->absence_reason === 'injustifiee' ? 'Injustifiée' : ($grade->absence_reason === 'justifiee' ? 'Justifiée' : '-') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <section class="empty-state">
        Choisissez une épreuve pour afficher les élèves et accéder à la saisie.
    </section>
@endif
@endsection
