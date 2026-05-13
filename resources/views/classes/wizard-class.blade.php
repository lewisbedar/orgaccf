@extends('layouts.app', ['title' => 'Assistant classe'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Créer une classe</h1>
        <p class="muted">Étape 1 sur 4 : nom de la classe et langue(s) concernée(s).</p>
    </div>
    @include('partials.help-popover', [
        'helpTitle' => 'Aide classe',
        'helpItems' => [
            ['title' => 'Nom', 'text' => 'utilisez le nom habituel de la classe, par exemple T AGOrA.'],
            ['title' => 'Langues', 'text' => 'cochez toutes les langues qui pourront concerner les élèves.'],
        ],
    ])
</section>

<form method="post" action="{{ route('classes.wizard.class') }}" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span class="active">1. Classe</span>
        <span>2. Méthode</span>
        <span>3. Élèves</span>
        <span>4. Vérification</span>
    </div>

    <section class="wizard-section">
        <h2>Nom de la classe</h2>
        <label>Nom
            <input name="name" value="{{ old('name', $draft['name'] ?? '') }}" placeholder="Ex. T AGOrA" required autofocus>
        </label>
        <p class="muted">Année scolaire : <strong>{{ $year?->label ?? 'à configurer' }}</strong></p>
    </section>

    <fieldset class="language-picker">
        <legend>Langue(s) concernée(s)</legend>
        <div class="language-grid">
            @foreach($languages as $language)
                <label class="language-choice">
                    <input type="checkbox" name="language_ids[]" value="{{ $language->id }}" @checked(in_array($language->id, old('language_ids', $draft['language_ids'] ?? [])))>
                    <span>
                        @if($language->icon_path)<img class="flag" src="{{ $language->icon_path }}" alt="">@endif
                        {{ $language->label() }}
                    </span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ route('classes.index') }}">Annuler</a>
        <button>Suivant</button>
    </div>
</form>
@endsection
