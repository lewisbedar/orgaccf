@extends('layouts.print', ['title' => 'Bilan des notes'])

@section('content')
@include('partials.print-header')

<h2>Bilan des notes · {{ $year?->label }}</h2>

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
                    @if($summary['status'] === 'eliminatoire')
                        Éliminatoire
                    @elseif($summary['status'] === 'complet')
                        Complet
                    @else
                        Incomplet
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Aucun résultat à afficher.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
