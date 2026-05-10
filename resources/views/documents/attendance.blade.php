@extends('layouts.print', ['title' => 'Émargement'])

@section('content')
@php
    $isOral = $exam->type === 'oral';
    $dateLabel = $exam->exam_date->copy()->locale('fr')->translatedFormat('l j F Y');
    $speakerLabel = $isOral ? 'Examinateur / jury' : 'Surveillant';
    $speakerName = $exam->teacher?->display_name ?: $exam->supervisor_name;
@endphp

<style>
    .print-page{background:#fff;padding:24px;color:#111;font-family:DejaVu Sans,Arial,sans-serif}
    .print-header{display:flex;gap:18px;border-bottom:2px solid #111;padding-bottom:12px;margin-bottom:18px}
    .print-header img{width:82px;max-height:82px;object-fit:contain}
    .print-header h1{margin:0 0 6px;font-size:20px}
    .print-header p{margin:2px 0;font-size:12px}
    .doc-title{text-align:center;margin:12px 0 16px}
    .doc-title h1{margin:0;font-size:22px}
    .doc-title p{margin:6px 0 0;font-size:13px}
    .doc-meta{width:100%;border-collapse:collapse;margin:0 0 18px}
    .doc-meta td{border:1px solid #111;padding:7px 9px;font-size:12px}
    .attendance-table{width:100%;border-collapse:collapse;table-layout:fixed}
    .attendance-table th,.attendance-table td{border:1px solid #111;padding:7px 6px;font-size:11px;vertical-align:middle}
    .attendance-table th{background:#f1f1f1;text-align:left}
    .attendance-table td{height:24px}
    .col-time{width:62px}.col-small{width:72px}.col-sign{width:130px}.col-observation{width:150px}
</style>

@include('partials.print-header')

<section class="doc-title">
    <h1>Feuille d’émargement{{ $exam->is_catchup ? ' - rattrapage' : '' }}</h1>
    <p>{{ $isOral ? 'Épreuve orale' : 'Épreuve écrite' }} - {{ $exam->language->label() }}</p>
</section>

<table class="doc-meta">
    <tr>
        <td><strong>Classe :</strong> {{ $exam->schoolClass->name }}</td>
        <td><strong>Date :</strong> {{ $dateLabel }}</td>
        <td><strong>Salle :</strong> {{ $exam->room }}</td>
    </tr>
    <tr>
        <td><strong>Horaire :</strong> {{ $isOral ? 'horaires individuels' : substr($exam->start_time, 0, 5) }}</td>
        <td colspan="2"><strong>{{ $speakerLabel }} :</strong> {{ $speakerName ?: 'Non renseigné' }}</td>
    </tr>
</table>

<table class="attendance-table">
    <thead>
        <tr>
            @if($isOral)<th class="col-time">Horaire</th>@endif
            <th>Nom</th>
            <th>Prénom</th>
            <th>Langue / niveau</th>
            @if(!$isOral)<th class="col-small">Tiers-temps</th>@endif
            <th class="col-sign">Signature</th>
            <th class="col-observation">Observation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($exam->slots->sortBy(fn ($slot) => $isOral ? $slot->pass_time : $slot->student->last_name . ' ' . $slot->student->first_name) as $slot)
            <tr>
                @if($isOral)<td>{{ substr($slot->pass_time, 0, 5) }}</td>@endif
                <td>{{ mb_strtoupper($slot->student->last_name) }}</td>
                <td>{{ $slot->student->first_name }}</td>
                <td>{{ $exam->language->label() }}</td>
                @if(!$isOral)<td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td>@endif
                <td></td>
                <td></td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
