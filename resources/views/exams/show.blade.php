@extends('layouts.app', ['title' => 'Épreuve'])

@section('content')
<section class="page-heading">
    <h1>{{ $exam->type === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}{{ $exam->is_catchup ? ' de rattrapage' : '' }}</h1>
    <a class="button" href="{{ route('grades.edit', $exam) }}">Saisir les notes</a>
</section>

<section class="panel meta-grid">
    <div><span>Classe</span><strong><span class="class-pill" style="--class-color: {{ $exam->schoolClass->displayColor() }}">{{ $exam->schoolClass->name }}</span></strong></div>
    <div><span>Langue</span><strong>{{ $exam->language->label() }}</strong></div>
    <div><span>Date</span><strong>{{ $exam->exam_date->format('d/m/Y') }} {{ substr($exam->start_time, 0, 5) }}</strong></div>
    <div><span>Salle</span><strong>{{ $exam->room }}</strong></div>
    <div><span>Intervenant</span><strong>{{ $exam->teacher?->display_name ?: $exam->supervisor_name }}</strong></div>
</section>

<nav class="doc-links">
    <a target="_blank" href="{{ route('documents.convocations', $exam) }}">Convocations</a>
    <a target="_blank" href="{{ route('documents.attendance', $exam) }}">Émargement</a>
    @if($exam->type === 'oral')
        <a target="_blank" href="{{ route('documents.oral-list', $exam) }}">Liste de passage</a>
    @endif
</nav>

<table>
    <thead>
    <tr>
        @if($exam->type === 'oral')<th>Horaire</th>@endif
        <th>Nom</th>
        <th>Prénom</th>
        @if($exam->type === 'ecrit')<th>Tiers-temps</th>@endif
        <th>Note</th>
    </tr>
    </thead>
    <tbody>
    @foreach($exam->slots as $slot)
        <tr>
            @if($exam->type === 'oral')<td>{{ substr($slot->pass_time, 0, 5) }}</td>@endif
            <td>{{ $slot->student->last_name }}</td>
            <td>{{ $slot->student->first_name }}</td>
            @if($exam->type === 'ecrit')<td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td>@endif
            <td>{{ $exam->grades->firstWhere('student_id', $slot->student_id)?->value }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

@unless($exam->is_catchup)
    <section class="panel narrow">
        <h2>Créer un rattrapage</h2>
        <form method="post" action="{{ route('exams.catchup', $exam) }}" class="form-grid">
            @csrf
            <label>Date <input type="date" name="exam_date" required></label>
            <label>Heure <input type="time" name="start_time" required></label>
            <label>Salle <input name="room" value="{{ $exam->room }}" required></label>
            <button>Proposer les élèves AB</button>
        </form>
    </section>
@endunless
@endsection
