@extends('layouts.app', ['title' => 'Saisie des notes'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Saisie des notes</h1>
        <p class="muted">
            {{ $exam->type === 'oral' ? 'Oral' : 'Écrit' }} sur {{ $maxScore }} points ·
            {{ $exam->schoolClass->name }} · {{ $exam->language->label() }}
        </p>
    </div>
</section>

<form method="post" action="{{ route('grades.update', $exam) }}" class="panel stack">
    @csrf

    <p class="muted">
        Saisissez une note entre 0 et {{ $maxScore }}, ou AB pour une absence.
        Une absence injustifiée restant après rattrapage rend le candidat éliminatoire.
    </p>

    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                @if($exam->type === 'ecrit')
                    <th>Tiers-temps</th>
                @endif
                <th>Note / {{ $maxScore }}</th>
                <th>Absence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exam->slots as $slot)
                @php($grade = $exam->grades->firstWhere('student_id', $slot->student_id))
                <tr>
                    <td>{{ $slot->student->last_name }}</td>
                    <td>{{ $slot->student->first_name }}</td>
                    @if($exam->type === 'ecrit')
                        <td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td>
                    @endif
                    <td>
                        <input
                            class="grade-input"
                            name="grades[{{ $slot->student_id }}]"
                            value="{{ old("grades.$slot->student_id", $grade?->value) }}"
                            placeholder="Ex. {{ $maxScore - 2 }} ou AB"
                        >
                    </td>
                    <td>
                        <select name="absence_reasons[{{ $slot->student_id }}]">
                            <option value="">-</option>
                            <option value="justifiee" @selected(old("absence_reasons.$slot->student_id", $grade?->absence_reason) === 'justifiee')>Justifiée</option>
                            <option value="injustifiee" @selected(old("absence_reasons.$slot->student_id", $grade?->absence_reason) === 'injustifiee')>Injustifiée</option>
                        </select>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <button>Enregistrer</button>
</form>
@endsection
