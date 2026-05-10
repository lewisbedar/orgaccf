@extends('layouts.app', ['title' => 'Notes'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Notes</h1>
        <p class="muted">Sélectionnez une épreuve pour saisir ou consulter les notes des élèves concernés.</p>
    </div>
    @include('partials.help-popover', [
        'helpTitle' => 'Aide notes',
        'helpItems' => [
            ['title' => 'Filtrer', 'text' => 'choisissez une épreuve dans la liste pour afficher les élèves.'],
            ['title' => 'Saisir', 'text' => 'le bouton ouvre la page de saisie de l’épreuve sélectionnée.'],
            ['title' => 'PDF', 'text' => 'le récapitulatif reprend les notes et absences de cette épreuve.'],
        ],
    ])
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
            <a class="button" target="_blank" href="{{ route('documents.grades', ['exam_id' => $selectedExam->id]) }}">Récapitulatif PDF</a>
        @endif
    </div>
</form>

@if($selectedExam)
    @php($isOral = $selectedExam->type === 'oral')
    <section class="panel meta-grid">
        <div><span>Type</span><strong>{{ $isOral ? 'Épreuve orale' : 'Épreuve écrite' }}{{ $selectedExam->is_catchup ? ' de rattrapage' : '' }}</strong></div>
        <div><span>Classe</span><strong><span class="class-chip" style="--class-color: {{ $selectedExam->schoolClass->displayColor() }}">{{ $selectedExam->schoolClass->name }}</span></strong></div>
        <div><span>Langue</span><strong>{{ $selectedExam->language->label() }}</strong></div>
        <div><span>Date</span><strong>{{ $selectedExam->exam_date->format('d/m/Y') }} {{ substr($selectedExam->start_time, 0, 5) }}</strong></div>
        <div><span>Barème</span><strong>/ {{ $maxScore }}</strong></div>
    </section>

    <table>
        <thead>
            <tr>
                @if($isOral)<th>Horaire</th>@endif
                <th>Nom</th>
                <th>Prénom</th>
                @if(!$isOral)<th>Tiers-temps</th>@endif
                <th>Note / {{ $maxScore }}</th>
                <th>Absence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($selectedExam->slots->sortBy(fn ($slot) => $isOral ? $slot->pass_time : $slot->student->last_name . ' ' . $slot->student->first_name) as $slot)
                @php($grade = $selectedExam->grades->firstWhere('student_id', $slot->student_id))
                <tr>
                    @if($isOral)<td>{{ substr($slot->pass_time, 0, 5) }}</td>@endif
                    <td>{{ $slot->student->last_name }}</td>
                    <td>{{ $slot->student->first_name }}</td>
                    @if(!$isOral)<td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td>@endif
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
