@extends('layouts.print', ['title' => 'Émargement'])
@section('content')
@include('partials.print-header')
<h2>Feuille d’émargement{{ $exam->is_catchup ? ' · rattrapage' : '' }}</h2>
<p>{{ $exam->schoolClass->name }} · {{ $exam->language->label() }} · {{ $exam->exam_date->format('d/m/Y') }} · Salle {{ $exam->room }}</p>
<table><thead><tr>@if($exam->type==='oral')<th>Horaire</th>@endif<th>Nom</th><th>Prénom</th><th>Langue / niveau</th><th>Tiers-temps</th><th>Signature</th><th>Observation</th></tr></thead><tbody>
@foreach($exam->slots as $slot)<tr>@if($exam->type==='oral')<td>{{ substr($slot->pass_time,0,5) }}</td>@endif<td>{{ $slot->student->last_name }}</td><td>{{ $slot->student->first_name }}</td><td>{{ $exam->language->label() }}</td><td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td><td></td><td></td></tr>@endforeach
</tbody></table>
@endsection
