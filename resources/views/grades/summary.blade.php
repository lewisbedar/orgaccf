@extends('layouts.app', ['title' => 'Récapitulatif des notes'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Récapitulatif des notes</h1>
        <p class="muted">Calcul automatique : écrit sur 12 points, oral sur 8 points.</p>
    </div>
    <a class="button" target="_blank" href="{{ route('documents.grades') }}">Version imprimable</a>
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
                <td>{{ $summary['student']->schoolClass->name }}</td>
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
            <tr><td colspan="7">Aucun élève à afficher.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
