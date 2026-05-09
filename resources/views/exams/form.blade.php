@extends('layouts.app', ['title' => 'Nouvelle épreuve'])
@section('content')
<section class="page-heading">
    <h1>Planifier une épreuve</h1>
</section>

<form method="post" action="{{ route('exams.preview') }}" class="panel form-grid">
    @csrf
    <label>Type
        <select name="type">
            <option value="ecrit">Écrit · 1h</option>
            <option value="oral">Oral · 10 min + 5 min pause</option>
        </select>
    </label>
    <label>Classe
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
        <input name="supervisor_name">
    </label>

    <fieldset class="planning-breaks">
        <legend>Pauses à respecter pour les oraux</legend>
        <p class="muted">Ces pauses ne s’appliquent qu’aux oraux. Elles bloquent la récréation et la pause midi avant validation de la liste de passage.</p>
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
