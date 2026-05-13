@extends('layouts.app', ['title' => 'Nouvelle épreuve'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Type et langue</h1>
        <p class="muted">Étape 2 sur 4 : {{ $class->name }}.</p>
    </div>
    @include('partials.help-popover', [
        'helpTitle' => 'Aide épreuve',
        'helpItems' => [
            ['title' => 'Écrit', 'text' => 'toute la classe passe au même horaire, pendant 1 heure.'],
            ['title' => 'Oral', 'text' => 'les horaires individuels seront générés après la sélection des élèves.'],
        ],
    ])
</section>

<form method="post" action="{{ route('exams.wizard.type.store') }}" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span>1. Classe</span>
        <span class="active">2. Épreuve</span>
        <span>3. Organisation</span>
        <span>4. Élèves</span>
    </div>

    <section class="wizard-section">
        <h2>Quel type d’épreuve ?</h2>
        <div class="choice-grid">
            <label class="choice-card">
                <input type="radio" name="type" value="ecrit" @checked(old('type', $draft['type'] ?? 'ecrit') === 'ecrit')>
                <span>
                    <strong>Épreuve écrite</strong>
                    <small>1 heure, même horaire pour tous les élèves sélectionnés.</small>
                </span>
            </label>
            <label class="choice-card">
                <input type="radio" name="type" value="oral" @checked(old('type', $draft['type'] ?? null) === 'oral')>
                <span>
                    <strong>Épreuve orale</strong>
                    <small>10 minutes par candidat + 5 minutes de pause.</small>
                </span>
            </label>
        </div>
    </section>

    <label>Langue / niveau
        <select name="language_id" required>
            <option value="">Choisir une langue</option>
            @foreach($languages as $language)
                <option value="{{ $language->id }}" @selected(old('language_id', $draft['language_id'] ?? '') == $language->id)>{{ $language->label() }}</option>
            @endforeach
        </select>
    </label>

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ route('exams.create') }}">Précédent</a>
        <button>Suivant</button>
    </div>
</form>
@endsection
