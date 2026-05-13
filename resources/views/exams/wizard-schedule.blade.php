@extends('layouts.app', ['title' => 'Nouvelle épreuve'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Organisation</h1>
        <p class="muted">Étape 3 sur 4 : indiquez quand et où aura lieu l’épreuve.</p>
    </div>
    @include('partials.help-popover', [
        'helpTitle' => 'Aide organisation',
        'helpItems' => [
            ['title' => 'Écrit', 'text' => 'renseignez le surveillant. Tous les élèves auront le même horaire.'],
            ['title' => 'Oral', 'text' => 'renseignez le jury. Les horaires individuels seront générés à l’étape suivante.'],
            ['title' => 'Pauses', 'text' => 'les pauses orales sont gérées dans Paramétrages > Établissement.'],
        ],
    ])
</section>

<form method="post" action="{{ route('exams.wizard.schedule.store') }}" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span>1. Classe</span>
        <span>2. Épreuve</span>
        <span class="active">3. Organisation</span>
        <span>4. Élèves</span>
    </div>

    <section class="meta-grid">
        <div><span>Classe</span><strong>{{ $class->name }}</strong></div>
        <div><span>Type</span><strong>{{ $draft['type'] === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}</strong></div>
        <div><span>Langue</span><strong>{{ $language->label() }}</strong></div>
        <div><span>Durée</span><strong>{{ $draft['type'] === 'oral' ? '10 min + 5 min' : '1h' }}</strong></div>
        <div><span>Étape</span><strong>Organisation</strong></div>
    </section>

    <section class="wizard-section">
        <h2>Date, salle et intervenant</h2>
        <div class="form-grid">
            <label>Date
                <input type="date" name="exam_date" value="{{ old('exam_date', $draft['exam_date'] ?? '') }}" required>
            </label>
            <label>Heure de début
                <input type="time" name="start_time" value="{{ old('start_time', isset($draft['start_time']) ? substr($draft['start_time'], 0, 5) : '') }}" required>
            </label>
            <label>Salle
                <input name="room" value="{{ old('room', $draft['room'] ?? '') }}" required>
            </label>

            @if($draft['type'] === 'oral')
                <label>Examinateur / jury
                    <select name="teacher_id">
                        <option value="">Non renseigné</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id', $draft['teacher_id'] ?? '') == $teacher->id)>{{ $teacher->display_name }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <label>Surveillant
                    <input name="supervisor_name" value="{{ old('supervisor_name', $draft['supervisor_name'] ?? '') }}" placeholder="Nom du surveillant">
                </label>
            @endif
        </div>
    </section>

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ route('exams.wizard.type') }}">Précédent</a>
        <button>Suivant</button>
    </div>
</form>
@endsection
