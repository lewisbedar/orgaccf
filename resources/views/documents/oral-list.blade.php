@extends('layouts.print', ['title' => 'Liste de passage oral'])

@section('content')
@php
    $dateLabel = $exam->exam_date->copy()->locale('fr')->translatedFormat('l j F Y');
@endphp

<style>
    .print-page{background:#fff;padding:30px;color:#111;font-family:DejaVu Sans,Arial,sans-serif}
    .door-planning{max-width:720px;margin:0 auto}
    .door-planning h1,.door-planning h2,.door-planning h3{text-align:center;margin:0}
    .door-planning h1{font-size:20px;letter-spacing:.5px}
    .door-planning h2{font-size:22px;margin-top:10px}
    .door-planning h3{font-size:18px;margin-top:8px;font-weight:normal}
    .door-meta{display:grid;text-align:center;gap:6px;margin:24px 0 26px;font-size:16px}
    .door-table{width:100%;border-collapse:collapse}
    .door-table th{border-bottom:2px solid #111;padding:8px;font-size:14px;text-align:left}
    .door-table th:last-child{text-align:right}
    .door-table td{padding:6px 8px;font-size:14px;border-bottom:0}
    .door-table td:last-child{text-align:right;width:180px}
    .catchup{display:inline-block;border:2px solid #111;padding:4px 10px;margin-top:10px;font-weight:bold;text-transform:uppercase}
</style>

<section class="door-planning">
    <h1>BACCALAURÉAT PROFESSIONNEL</h1>
    <h2>CCF {{ mb_strtoupper($exam->language->name) }} {{ $exam->language->level }}</h2>
    <h3>Épreuve orale</h3>
    @if($exam->is_catchup)
        <p style="text-align:center"><span class="catchup">Rattrapage</span></p>
    @endif

    <div class="door-meta">
        <strong>{{ $exam->schoolClass->name }}</strong>
        <span>{{ $dateLabel }}</span>
        <span>Salle {{ $exam->room }}</span>
    </div>

    <table class="door-table">
        <thead>
            <tr>
                <th>NOM Prénom</th>
                <th>Heure de passage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exam->slots->sortBy('pass_time') as $slot)
                <tr>
                    <td>{{ mb_strtoupper($slot->student->last_name) }} {{ $slot->student->first_name }}</td>
                    <td>{{ str_replace(':', 'h', substr($slot->pass_time, 0, 5)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endsection
