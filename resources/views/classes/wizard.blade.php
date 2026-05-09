@extends('layouts.app', ['title' => 'Assistant classe'])
@section('content')
<section class="page-heading"><h1>Assistant de création de classe</h1></section>
<form method="post" action="{{ route('classes.store') }}" enctype="multipart/form-data" class="panel stack">@csrf
    <div class="wizard-steps"><span>1. Classe</span><span>2. Import Pronote</span><span>3. Ajustement</span><span>4. Validation</span></div>
    <label>Nom de la classe <input name="name" required></label>
    <p>Année scolaire : <strong>{{ $year?->label }}</strong></p>
    <fieldset><legend>Langue(s) concernée(s)</legend>@foreach($languages as $language)<label class="check"><input type="checkbox" name="language_ids[]" value="{{ $language->id }}"> <img class="flag" src="{{ $language->icon_path }}" alt=""> {{ $language->label() }}</label>@endforeach</fieldset>
    <label>Import Pronote CSV facultatif <input type="file" name="pronote_csv" accept=".csv,text/csv"></label>
    <p class="muted">Import tolérant : UTF-8 avec ou sans BOM, séparateur ; ou ,, colonnes accentuées, “Nom/Prénom” ou “Élèves”.</p>
    <button>Valider la classe</button>
</form>
@endsection
