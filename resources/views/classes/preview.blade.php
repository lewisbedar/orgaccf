@extends('layouts.app', ['title' => 'Vérification classe'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Vérifier la classe</h1>
        <p class="muted">
            Étape 4 sur 4 :
            {{ $draft['source'] === 'pronote' ? 'import Pronote' : 'saisie manuelle' }}
            @if($draft['file_name']) · fichier : {{ $draft['file_name'] }} @endif
        </p>
    </div>
    <a href="{{ route('classes.create', ['reset' => 1]) }}">Recommencer</a>
</section>

<form method="post" action="{{ route('classes.confirm') }}" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span>1. Classe</span>
        <span>2. Méthode</span>
        <span>3. Élèves</span>
        <span class="active">4. Vérification</span>
    </div>

    <div class="form-grid">
        <label>Nom de la classe
            <input name="name" value="{{ old('name', $draft['name']) }}" required>
        </label>
        <div>
            <strong>Langues sélectionnées</strong>
            <div class="inline-list">
                @foreach($selectedLanguages as $language)
                    <label class="check">
                        <input type="checkbox" name="language_ids[]" value="{{ $language->id }}" checked>
                        @if($language->icon_path)<img class="flag" src="{{ $language->icon_path }}" alt="">@endif
                        {{ $language->label() }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    @if(count($draft['students']) === 0)
        <div class="empty-state">
            <h2>Aucun élève détecté</h2>
            <p class="muted">Vous pouvez revenir en arrière pour ajouter des élèves, ou créer la classe vide et compléter plus tard.</p>
        </div>
    @else
        <div class="preview-toolbar">
            <strong>{{ count($draft['students']) }} élève(s) à vérifier</strong>
            <span class="muted">Modifiez une cellule ou supprimez une ligne avant validation.</span>
        </div>

        <div class="table-scroll">
            <table class="import-table">
                <thead>
                <tr>
                    <th>Conserver</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Naissance</th>
                    <th>Email</th>
                    <th>Tiers-temps</th>
                    <th>Options Pronote</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($draft['students'] as $index => $student)
                    @php($oldRow = old("students.$index", $student))
                    <tr>
                        <td>
                            <input type="hidden" name="students[{{ $index }}][include]" value="0">
                            <input type="checkbox" name="students[{{ $index }}][include]" value="1" @checked(($oldRow['include'] ?? true))>
                        </td>
                        <td><input name="students[{{ $index }}][last_name]" value="{{ $oldRow['last_name'] ?? '' }}" required></td>
                        <td><input name="students[{{ $index }}][first_name]" value="{{ $oldRow['first_name'] ?? '' }}" required></td>
                        <td><input type="date" name="students[{{ $index }}][birth_date]" value="{{ $oldRow['birth_date'] ?? '' }}"></td>
                        <td><input type="email" name="students[{{ $index }}][email]" value="{{ $oldRow['email'] ?? '' }}"></td>
                        <td>
                            <input type="hidden" name="students[{{ $index }}][extra_time]" value="0">
                            <input type="checkbox" name="students[{{ $index }}][extra_time]" value="1" @checked(!empty($oldRow['extra_time']))>
                        </td>
                        <td><input name="students[{{ $index }}][pronote_options]" value="{{ $oldRow['pronote_options'] ?? '' }}"></td>
                        <td><button type="button" class="icon-action danger-text" data-remove-import-row title="Supprimer la ligne">×</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ $draft['source'] === 'pronote' ? route('classes.wizard.pronote') : route('classes.wizard.manual') }}">Précédent</a>
        <button>Créer la classe</button>
    </div>
</form>
@endsection
