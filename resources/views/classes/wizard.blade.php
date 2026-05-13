@extends('layouts.app', ['title' => 'Assistant classe'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Assistant de création de classe</h1>
        <p class="muted">Créez la classe, choisissez le mode d’ajout des élèves, puis vérifiez la liste avant validation.</p>
    </div>
    @include('partials.help-popover', [
        'helpTitle' => 'Aide classe',
        'helpItems' => [
            ['title' => 'Pronote', 'text' => 'importez un CSV puis corrigez les élèves détectés à l’étape suivante.'],
            ['title' => 'Saisie manuelle', 'text' => 'remplissez le tableau comme dans un tableur.'],
            ['title' => 'Validation', 'text' => 'rien n’est enregistré avant l’écran de vérification.'],
        ],
    ])
</section>

<form method="post" action="{{ route('classes.preview') }}" enctype="multipart/form-data" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span class="active">1. Classe</span>
        <span class="active">2. Élèves</span>
        <span>3. Vérification</span>
        <span>4. Validation</span>
    </div>

    <section class="wizard-section">
        <h2>Nom de la classe</h2>
        <label>Nom
            <input name="name" value="{{ old('name') }}" placeholder="Ex. T AGOrA" required>
        </label>
        <p class="muted">Année scolaire : <strong>{{ $year?->label ?? 'à configurer' }}</strong></p>
    </section>

    <fieldset class="language-picker">
        <legend>Langue(s) concernée(s)</legend>
        <div class="language-grid">
            @foreach($languages as $language)
                <label class="language-choice">
                    <input type="checkbox" name="language_ids[]" value="{{ $language->id }}" @checked(in_array($language->id, old('language_ids', [])))>
                    <span>
                        @if($language->icon_path)<img class="flag" src="{{ $language->icon_path }}" alt="">@endif
                        {{ $language->label() }}
                    </span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <section class="wizard-section">
        <h2>Comment voulez-vous créer les élèves ?</h2>
        <div class="choice-grid">
            <label class="choice-card">
                <input type="radio" name="creation_method" value="pronote" data-class-source @checked(old('creation_method', 'pronote') === 'pronote')>
                <span>
                    <strong>Importer depuis Pronote</strong>
                    <small>Le plus rapide si vous avez un export CSV.</small>
                </span>
            </label>
            <label class="choice-card">
                <input type="radio" name="creation_method" value="manual" data-class-source @checked(old('creation_method') === 'manual')>
                <span>
                    <strong>Saisie manuelle</strong>
                    <small>Pratique pour une petite classe ou une correction rapide.</small>
                </span>
            </label>
        </div>
    </section>

    <section class="wizard-section source-panel" data-source-panel="pronote">
        <h2>Import Pronote</h2>
        <ol class="procedure-list">
            <li>Exportez la liste des élèves au format CSV depuis Pronote.</li>
            <li>Gardez les colonnes utiles : Élèves, Nom, Prénom, naissance, email et options si disponibles.</li>
            <li>Déposez le fichier ici. Vous pourrez corriger chaque ligne avant l’enregistrement.</li>
        </ol>
        <label>Fichier CSV Pronote
            <input type="file" name="pronote_csv" accept=".csv,text/csv">
        </label>
    </section>

    <section class="wizard-section source-panel" data-source-panel="manual" hidden>
        <div class="section-title">
            <h2>Saisie manuelle</h2>
            <button type="button" class="ghost-button" data-add-manual-row>Ajouter une ligne</button>
        </div>
        <div class="table-scroll">
            <table class="manual-entry-table" data-manual-table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Naissance</th>
                        <th>Email</th>
                        <th>Tiers-temps</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @for($index = 0; $index < 10; $index++)
                        @php($row = old("manual_students.$index", []))
                        <tr>
                            <td><input name="manual_students[{{ $index }}][last_name]" value="{{ $row['last_name'] ?? '' }}"></td>
                            <td><input name="manual_students[{{ $index }}][first_name]" value="{{ $row['first_name'] ?? '' }}"></td>
                            <td><input type="date" name="manual_students[{{ $index }}][birth_date]" value="{{ $row['birth_date'] ?? '' }}"></td>
                            <td><input type="email" name="manual_students[{{ $index }}][email]" value="{{ $row['email'] ?? '' }}"></td>
                            <td>
                                <input type="hidden" name="manual_students[{{ $index }}][extra_time]" value="0">
                                <input type="checkbox" name="manual_students[{{ $index }}][extra_time]" value="1" @checked(!empty($row['extra_time']))>
                            </td>
                            <td><button type="button" class="icon-action danger-text" data-remove-row title="Supprimer la ligne">×</button></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </section>

    <div class="form-actions">
        <button>Continuer vers la vérification</button>
    </div>
</form>
@endsection
