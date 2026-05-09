@extends('layouts.app', ['title' => 'Assistant classe'])

@section('content')
<section class="page-heading">
    <h1>Assistant de création de classe</h1>
</section>

<form method="post" action="{{ route('classes.preview') }}" enctype="multipart/form-data" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span class="active">1. Classe</span>
        <span>2. Import Pronote</span>
        <span>3. Aperçu</span>
        <span>4. Validation</span>
    </div>

    <label>Nom de la classe
        <input name="name" value="{{ old('name') }}" placeholder="TLE MCV" required>
    </label>

    <p>Année scolaire : <strong>{{ $year?->label ?? 'à configurer' }}</strong></p>

    <fieldset class="language-picker">
        <legend>Langue(s) concernée(s)</legend>
        <div class="language-grid">
            @foreach($languages as $language)
                <label class="language-choice">
                    <input type="checkbox" name="language_ids[]" value="{{ $language->id }}" @checked(in_array($language->id, old('language_ids', [])))>
                    <span><img class="flag" src="{{ $language->icon_path }}" alt=""> {{ $language->label() }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <label>Import Pronote CSV facultatif
        <input type="file" name="pronote_csv" accept=".csv,text/csv">
    </label>

    <p class="muted">Le fichier sera analysé avant enregistrement. Vous pourrez exclure des lignes, corriger les noms, ajouter un email ou cocher le tiers-temps.</p>

    <button>Prévisualiser</button>
</form>
@endsection
