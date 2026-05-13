@extends('layouts.app', ['title' => 'Assistant classe'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Importer depuis Pronote</h1>
        <p class="muted">Étape 3 sur 4 : déposez le fichier CSV exporté depuis Pronote.</p>
    </div>
</section>

<form method="post" action="{{ route('classes.wizard.pronote.store') }}" enctype="multipart/form-data" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span>1. Classe</span>
        <span>2. Méthode</span>
        <span class="active">3. Élèves</span>
        <span>4. Vérification</span>
    </div>

    <section class="wizard-section">
        <h2>Procédure Pronote</h2>
        <ol class="procedure-list">
            <li>Dans Pronote, exportez la liste des élèves de la classe au format CSV.</li>
            <li>Les colonnes utiles sont : Élèves, Nom, Prénom, naissance, email et options si disponibles.</li>
            <li>Déposez le fichier ci-dessous. Vous corrigerez ensuite la liste avant création.</li>
        </ol>
    </section>

    <label>Fichier CSV Pronote
        <input type="file" name="pronote_csv" accept=".csv,text/csv" required>
    </label>

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ route('classes.wizard.method') }}">Précédent</a>
        <button>Suivant</button>
    </div>
</form>
@endsection
