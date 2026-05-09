@extends('layouts.print', ['title' => 'Convocations'])
@section('content')
@include('partials.print-header')
<h2>Convocations élèves{{ $exam->is_catchup ? ' · rattrapage' : '' }}</h2>
@foreach($exam->slots as $slot)<section class="print-card"><h3>{{ $slot->student->last_name }} {{ $slot->student->first_name }}</h3><p><strong>Classe :</strong> {{ $exam->schoolClass->name }} · <strong>Langue :</strong> {{ $exam->language->label() }}</p><p><strong>Épreuve :</strong> {{ $exam->type }}{{ $exam->is_catchup ? ' de rattrapage' : '' }}</p><p><strong>Date :</strong> {{ $exam->exam_date->format('d/m/Y') }} · <strong>Salle :</strong> {{ $exam->room }}</p>@if($exam->type==='oral')<p><strong>Horaire de passage :</strong> {{ substr($slot->pass_time,0,5) }}</p>@else<p><strong>Heure de début :</strong> {{ substr($exam->start_time,0,5) }}</p>@endif<p><strong>{{ $exam->type==='oral' ? 'Examinateur / jury' : 'Surveillant' }} :</strong> {{ $exam->teacher?->display_name ?: $exam->supervisor_name }}</p>@if($slot->student->extra_time)<p class="notice">Tiers-temps accordé</p>@endif</section>@endforeach
@endsection
