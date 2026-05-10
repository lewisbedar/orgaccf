@extends('layouts.app', ['title' => 'Saisie des notes'])

@section('content')
@php($isOral = $exam->type === 'oral')

<section class="page-heading">
    <div>
        <h1>Saisie des notes</h1>
        <p class="muted">
            {{ $isOral ? 'Épreuve orale' : 'Épreuve écrite' }} sur {{ $maxScore }} points ·
            {{ $exam->schoolClass->name }} · {{ $exam->language->label() }}
        </p>
    </div>
</section>

<form method="post" action="{{ route('grades.update', $exam) }}" class="panel stack">
    @csrf

    <p class="muted">
        Saisissez une note entre 0 et {{ $maxScore }}, ou AB pour une absence.
        Le motif d’absence apparaît uniquement pour les élèves marqués AB et devient alors obligatoire.
    </p>

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
            @foreach($exam->slots->sortBy(fn ($slot) => $isOral ? $slot->pass_time : $slot->student->last_name . ' ' . $slot->student->first_name) as $slot)
                @php($grade = $exam->grades->firstWhere('student_id', $slot->student_id))
                @php($currentValue = old("grades.$slot->student_id", $grade?->value))
                <tr>
                    @if($isOral)<td>{{ substr($slot->pass_time, 0, 5) }}</td>@endif
                    <td>{{ $slot->student->last_name }}</td>
                    <td>{{ $slot->student->first_name }}</td>
                    @if(!$isOral)
                        <td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td>
                    @endif
                    <td>
                        <input
                            class="grade-input"
                            data-grade-input
                            data-student-id="{{ $slot->student_id }}"
                            name="grades[{{ $slot->student_id }}]"
                            value="{{ $currentValue }}"
                            placeholder="Ex. {{ $maxScore - 2 }} ou AB"
                        >
                    </td>
                    <td>
                        <div class="absence-field" data-absence-field="{{ $slot->student_id }}" @if(strtoupper((string) $currentValue) !== 'AB') hidden @endif>
                            <select name="absence_reasons[{{ $slot->student_id }}]">
                                <option value="">Motif à préciser</option>
                                <option value="justifiee" @selected(old("absence_reasons.$slot->student_id", $grade?->absence_reason) === 'justifiee')>Justifiée</option>
                                <option value="injustifiee" @selected(old("absence_reasons.$slot->student_id", $grade?->absence_reason) === 'injustifiee')>Injustifiée</option>
                            </select>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <button>Enregistrer</button>
</form>
@endsection
