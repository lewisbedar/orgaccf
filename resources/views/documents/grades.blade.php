@extends('layouts.print', ['title' => 'Récapitulatif des notes'])

@section('content')
<style>
    .print-page{background:#fff;padding:24px;color:#111;font-family:DejaVu Sans,Arial,sans-serif}
    .print-header{display:flex;gap:18px;border-bottom:2px solid #111;padding-bottom:12px;margin-bottom:18px}
    .print-header img{width:82px;max-height:82px;object-fit:contain}
    .print-header h1{margin:0 0 6px;font-size:20px}
    .print-header p{margin:2px 0;font-size:12px}
    .doc-title{text-align:center;margin:12px 0 16px}
    .doc-title h1{margin:0;font-size:22px}
    .doc-title p{margin:6px 0 0;font-size:13px}
    .grades-table{width:100%;border-collapse:collapse;table-layout:fixed}
    .grades-table th,.grades-table td{border:1px solid #111;padding:7px 6px;font-size:11px;vertical-align:middle}
    .grades-table th{background:#f1f1f1;text-align:left}
    .col-time{width:62px}.col-small{width:72px}.col-grade{width:80px}.col-absence{width:96px}
</style>

@include('partials.print-header')

@if($exam)
    @php
        $isOral = $exam->type === 'oral';
        $dateLabel = $exam->exam_date->copy()->locale('fr')->translatedFormat('l j F Y');
    @endphp

    <section class="doc-title">
        <h1>Récapitulatif des notes{{ $exam->is_catchup ? ' - rattrapage' : '' }}</h1>
        <p>
            {{ $year?->label }} - {{ $exam->schoolClass->name }} - {{ $exam->language->label() }} -
            {{ $isOral ? 'Épreuve orale' : 'Épreuve écrite' }} - {{ $dateLabel }}
        </p>
    </section>

    <table class="grades-table">
        <thead>
            <tr>
                @if($isOral)<th class="col-time">Horaire</th>@endif
                <th>Nom</th>
                <th>Prénom</th>
                @if(!$isOral)<th class="col-small">Tiers-temps</th>@endif
                <th class="col-grade">Note / {{ $maxScore }}</th>
                <th class="col-absence">Absence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exam->slots->sortBy(fn ($slot) => $isOral ? $slot->pass_time : $slot->student->last_name . ' ' . $slot->student->first_name) as $slot)
                @php($grade = $exam->grades->firstWhere('student_id', $slot->student_id))
                <tr>
                    @if($isOral)<td>{{ substr($slot->pass_time, 0, 5) }}</td>@endif
                    <td>{{ mb_strtoupper($slot->student->last_name) }}</td>
                    <td>{{ $slot->student->first_name }}</td>
                    @if(!$isOral)<td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td>@endif
                    <td>{{ $grade?->value ?: '-' }}</td>
                    <td>
                        @if($grade?->value === 'AB')
                            {{ $grade->absence_reason === 'injustifiee' ? 'Injustifiée' : ($grade->absence_reason === 'justifiee' ? 'Justifiée' : '-') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <section class="doc-title">
        <h1>Récapitulatif des notes</h1>
        <p>Aucune épreuve sélectionnée.</p>
    </section>
@endif
@endsection
