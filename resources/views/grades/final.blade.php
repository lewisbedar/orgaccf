@extends('layouts.app', ['title' => 'Bilan des notes'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Bilan des notes</h1>
        <p class="muted">Vue finale par élève et par langue : écrit /12, oral /8, total /20 et statut.</p>
    </div>
    <a class="button" target="_blank" href="{{ route('documents.final-grades', request()->query()) }}">Version imprimable</a>
</section>

<form method="get" action="{{ route('grades.final') }}" class="panel form-grid">
    <label>Classe
        <select name="class_id">
            <option value="">Toutes les classes</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(($filters['class_id'] ?? '') == $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
    </label>
    <label>Langue
        <select name="language_id">
            <option value="">Toutes les langues</option>
            @foreach($languages as $language)
                <option value="{{ $language->id }}" @selected(($filters['language_id'] ?? '') == $language->id)>{{ $language->label() }}</option>
            @endforeach
        </select>
    </label>
    <label>Statut
        <select name="status">
            <option value="">Tous les statuts</option>
            <option value="complet" @selected(($filters['status'] ?? '') === 'complet')>Complet</option>
            <option value="incomplet" @selected(($filters['status'] ?? '') === 'incomplet')>Incomplet</option>
            <option value="eliminatoire" @selected(($filters['status'] ?? '') === 'eliminatoire')>Éliminatoire</option>
        </select>
    </label>
    <div class="form-actions actions">
        <button>Filtrer</button>
        <a class="button ghost-button" href="{{ route('grades.final') }}">Réinitialiser</a>
    </div>
</form>

<section class="stat-grid">
    <article><strong>{{ $summaries->count() }}</strong><span>Lignes</span></article>
    <article><strong>{{ $summaries->where('status', 'complet')->count() }}</strong><span>Complets</span></article>
    <article><strong>{{ $summaries->where('status', 'incomplet')->count() }}</strong><span>Incomplets</span></article>
    <article><strong>{{ $summaries->where('status', 'eliminatoire')->count() }}</strong><span>Éliminatoires</span></article>
    <article><strong>{{ $summaries->whereNotNull('total')->count() }}</strong><span>Totaux calculés</span></article>
</section>

<table>
    <thead>
        <tr>
            <th>Classe</th>
            <th>Élève</th>
            <th>Langue</th>
            <th>Écrit / 12</th>
            <th>Oral / 8</th>
            <th>Total / 20</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @forelse($summaries as $summary)
            <tr>
                <td><span class="class-pill" style="--class-color: {{ $summary['student']->schoolClass->displayColor() }}">{{ $summary['student']->schoolClass->name }}</span></td>
                <td>{{ $summary['student']->last_name }} {{ $summary['student']->first_name }}</td>
                <td>{{ $summary['language']->label() }}</td>
                <td>{{ $summary['written']['label'] }}</td>
                <td>{{ $summary['oral']['label'] }}</td>
                <td>{{ $summary['total'] !== null ? app(\App\Services\GradeSummaryService::class)->formatScore($summary['total']) : '-' }}</td>
                <td>
                    <span class="badge badge-{{ $summary['status'] }}">
                        @if($summary['status'] === 'eliminatoire')
                            Éliminatoire
                        @elseif($summary['status'] === 'complet')
                            Complet
                        @else
                            Incomplet
                        @endif
                    </span>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Aucun résultat avec ces filtres.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
