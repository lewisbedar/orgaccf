@extends('layouts.print', ['title' => 'Bilan des notes'])

@section('content')
<style>
    .print-page{background:#fff;padding:22px;color:#111;font-family:DejaVu Sans,Arial,sans-serif}
    .print-header{display:flex;gap:18px;border-bottom:2px solid #111;padding-bottom:10px;margin-bottom:16px}
    .print-header img{width:72px;max-height:72px;object-fit:contain}
    .print-header h1{margin:0 0 5px;font-size:18px}
    .print-header p{margin:2px 0;font-size:11px}
    .doc-title{text-align:center;margin:10px 0 14px}
    .doc-title h1{margin:0;font-size:21px}
    .doc-title p{margin:6px 0 0;font-size:12px}
    .final-table{width:100%;border-collapse:collapse;table-layout:fixed}
    .final-table th,.final-table td{border:1px solid #111;padding:6px 5px;font-size:10px;vertical-align:middle}
    .final-table th{background:#f1f1f1;text-align:left}
    .col-class{width:78px}.col-score{width:70px}.col-status{width:86px}
</style>

@include('partials.print-header')

<section class="doc-title">
    <h1>Bilan des notes</h1>
    <p>{{ $year?->label }}</p>
</section>

<table class="final-table">
    <thead>
        <tr>
            <th class="col-class">Classe</th>
            <th>Élève</th>
            <th>Langue</th>
            <th class="col-score">Écrit / 12</th>
            <th class="col-score">Oral / 8</th>
            <th class="col-score">Total / 20</th>
            <th class="col-status">Statut</th>
        </tr>
    </thead>
    <tbody>
        @forelse($summaries as $summary)
            <tr>
                <td>{{ $summary['student']->schoolClass->name }}</td>
                <td>{{ mb_strtoupper($summary['student']->last_name) }} {{ $summary['student']->first_name }}</td>
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
