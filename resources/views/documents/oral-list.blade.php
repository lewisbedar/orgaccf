@extends('layouts.print', ['title' => 'Liste de passage oral'])
@section('content')
<section class="door-planning">
    <h1>BACCALAUREAT PROFESSIONNEL</h1>
    <h2>CCF {{ mb_strtoupper($exam->language->name) }} {{ $exam->language->level }}</h2>
    <h3>Épreuve Orale{{ $exam->is_catchup ? ' · rattrapage' : '' }}</h3>

    <div class="door-meta">
        <strong>{{ $exam->schoolClass->name }}</strong>
        <span>{{ $exam->exam_date->translatedFormat('l d F') }}</span>
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
        @foreach($exam->slots as $slot)
            <tr>
                <td>{{ $slot->student->last_name }} {{ $slot->student->first_name }}</td>
                <td>{{ str_replace(':', 'h', substr($slot->pass_time, 0, 5)) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
@endsection
