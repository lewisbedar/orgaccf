@extends('layouts.app', ['title' => 'Nouvelle épreuve'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Planifier une épreuve</h1>
        <p class="muted">Renseignez les informations principales. Vous pourrez vérifier la liste des élèves avant validation.</p>
    </div>
    @include('partials.help-popover', [
        'helpTitle' => 'Aide planification',
        'helpItems' => [
            ['title' => 'Écrit', 'text' => 'toute la classe passe au même horaire, pendant 1 heure.'],
            ['title' => 'Oral', 'text' => 'les horaires individuels sont générés automatiquement.'],
            ['title' => 'Pauses', 'text' => 'elles apparaissent uniquement pour les oraux.'],
        ],
    ])
</section>

<form method="post" action="{{ route('exams.preview') }}" class="panel form-grid">
    @csrf
    <label>Type d’épreuve
        <select name="type" data-exam-type>
            <option value="ecrit">Épreuve écrite · 1h pour toute la classe</option>
            <option value="oral">Épreuve orale · horaires individuels</option>
        </select>
    </label>
    <label>Classe concernée
        <select name="school_class_id">
            @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
    </label>
    <label>Langue / niveau
        <select name="language_id">
            @foreach($languages as $language)
                <option value="{{ $language->id }}">{{ $language->label() }}</option>
            @endforeach
        </select>
    </label>
    <label>Date
        <input type="date" name="exam_date" required>
    </label>
    <label>Heure de début
        <input type="time" name="start_time" required>
    </label>
    <label>Salle
        <input name="room" required>
    </label>
    <label>Examinateur / jury
        <select name="teacher_id">
            <option value="">Non renseigné</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}">{{ $teacher->display_name }}</option>
            @endforeach
        </select>
    </label>
    <label>Surveillant écrit
        <input name="supervisor_name" placeholder="À remplir uniquement pour l’écrit si besoin">
    </label>

    <fieldset class="planning-breaks" data-oral-breaks hidden>
        <legend>Pauses à respecter pour les oraux</legend>
        <p class="muted">Ces pauses bloquent la récréation et la pause midi avant validation de la liste de passage.</p>
        @foreach($defaultBreaks as $index => $break)
            <div class="break-row">
                <label>Libellé <input name="breaks[{{ $index }}][label]" value="{{ $break['label'] }}"></label>
                <label>Début <input type="time" name="breaks[{{ $index }}][start]" value="{{ $break['start'] }}"></label>
                <label>Fin <input type="time" name="breaks[{{ $index }}][end]" value="{{ $break['end'] }}"></label>
            </div>
        @endforeach
    </fieldset>

    <div class="form-actions">
        <button>Prévisualiser la planification</button>
    </div>
</form>
@endsection
