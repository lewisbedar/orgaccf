@extends('layouts.app', ['title' => 'Assistant classe'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Saisie manuelle</h1>
        <p class="muted">Étape 3 sur 4 : renseignez les élèves dans le tableau.</p>
    </div>
    @include('partials.help-popover', [
        'helpTitle' => 'Aide saisie',
        'helpItems' => [
            ['title' => 'Lignes vides', 'text' => 'elles seront ignorées automatiquement.'],
            ['title' => 'Tiers-temps', 'text' => 'cochez uniquement les élèves concernés.'],
            ['title' => 'Correction', 'text' => 'vous pourrez encore modifier la liste à l’étape suivante.'],
        ],
    ])
</section>

<form method="post" action="{{ route('classes.wizard.manual.store') }}" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span>1. Classe</span>
        <span>2. Méthode</span>
        <span class="active">3. Élèves</span>
        <span>4. Vérification</span>
    </div>

    <div class="section-title">
        <h2>{{ $draft['name'] }}</h2>
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
                    @php($row = old("manual_students.$index", $draft['students'][$index] ?? []))
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

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ route('classes.wizard.method') }}">Précédent</a>
        <button>Suivant</button>
    </div>
</form>
@endsection
