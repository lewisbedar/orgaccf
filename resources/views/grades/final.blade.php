@extends('layouts.app', ['title' => 'Bilan des notes'])

@section('content')
@php
    $statusCounts = [
        'complet' => $summaries->where('status', 'complet')->count(),
        'incomplet' => $summaries->where('status', 'incomplet')->count(),
        'eliminatoire' => $summaries->where('status', 'eliminatoire')->count(),
    ];
    $totalRows = max(1, $summaries->count());
    $classAverages = $summaries
        ->whereNotNull('total')
        ->groupBy(fn ($summary) => $summary['student']->schoolClass->name)
        ->map(fn ($rows) => [
            'class' => $rows->first()['student']->schoolClass,
            'average' => round($rows->avg('total'), 2),
            'count' => $rows->count(),
        ])
        ->sortBy(fn ($row) => $row['class']->name);
@endphp

<section class="page-heading">
    <div>
        <h1>Bilan des notes</h1>
        <p class="muted">Vue finale par élève et par langue : écrit /12, oral /8, total /20 et statut.</p>
    </div>
    <a class="button" target="_blank" href="{{ route('documents.final-grades', request()->query()) }}">Bilan PDF</a>
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
    <article><strong>{{ $statusCounts['complet'] }}</strong><span>Complets</span></article>
    <article><strong>{{ $statusCounts['incomplet'] }}</strong><span>Incomplets</span></article>
    <article><strong>{{ $statusCounts['eliminatoire'] }}</strong><span>Éliminatoires</span></article>
    <article><strong>{{ $summaries->whereNotNull('total')->count() }}</strong><span>Totaux calculés</span></article>
</section>

<section class="chart-grid">
    <article class="panel chart-panel">
        <h2>Répartition des statuts</h2>
        <div class="status-bars">
            @foreach(['complet' => 'Complet', 'incomplet' => 'Incomplet', 'eliminatoire' => 'Éliminatoire'] as $status => $label)
                <div class="bar-row">
                    <span>{{ $label }}</span>
                    <div class="bar-track"><span class="bar-fill bar-{{ $status }}" style="width: {{ round($statusCounts[$status] / $totalRows * 100) }}%"></span></div>
                    <strong>{{ $statusCounts[$status] }}</strong>
                </div>
            @endforeach
        </div>
    </article>

    <article class="panel chart-panel">
        <h2>Moyennes par classe</h2>
        <div class="status-bars">
            @forelse($classAverages as $row)
                <div class="bar-row">
                    <span><span class="class-chip" style="--class-color: {{ $row['class']->displayColor() }}">{{ $row['class']->name }}</span></span>
                    <div class="bar-track"><span class="bar-fill" style="--bar-color: {{ $row['class']->displayColor() }}; width: {{ round($row['average'] / 20 * 100) }}%"></span></div>
                    <strong>{{ app(\App\Services\GradeSummaryService::class)->formatScore($row['average']) }}/20</strong>
                </div>
            @empty
                <p class="muted">Aucune moyenne disponible tant que les totaux ne sont pas complets.</p>
            @endforelse
        </div>
    </article>
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
                <td><span class="class-chip" style="--class-color: {{ $summary['student']->schoolClass->displayColor() }}">{{ $summary['student']->schoolClass->name }}</span></td>
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
