@extends('layouts.app', ['title' => 'Assistant classe'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Ajouter les élèves</h1>
        <p class="muted">Étape 2 sur 4 : choisissez la méthode de création des élèves.</p>
    </div>
</section>

<form method="post" action="{{ route('classes.wizard.method.store') }}" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span>1. Classe</span>
        <span class="active">2. Méthode</span>
        <span>3. Élèves</span>
        <span>4. Vérification</span>
    </div>

    <section class="wizard-section">
        <h2>{{ $draft['name'] }}</h2>
        <p class="muted">Choisissez la méthode la plus confortable. Rien n’est encore enregistré.</p>
        <div class="choice-grid">
            <label class="choice-card">
                <input type="radio" name="creation_method" value="pronote" @checked(old('creation_method', $draft['source'] ?? 'pronote') === 'pronote')>
                <span>
                    <strong>Importer depuis Pronote</strong>
                    <small>Idéal si vous disposez d’un export CSV de la classe.</small>
                </span>
            </label>
            <label class="choice-card">
                <input type="radio" name="creation_method" value="manual" @checked(old('creation_method', $draft['source'] ?? null) === 'manual')>
                <span>
                    <strong>Saisie manuelle</strong>
                    <small>Un tableau simple, proche d’un tableur.</small>
                </span>
            </label>
        </div>
    </section>

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ route('classes.create') }}">Précédent</a>
        <button>Suivant</button>
    </div>
</form>
@endsection
