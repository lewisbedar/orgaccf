@extends('layouts.print', ['title' => 'Récapitulatif des notes'])
@section('content')
@include('partials.print-header')
<h2>Récapitulatif des notes · {{ $year?->label }}</h2>
<table><thead><tr><th>Classe</th><th>Nom</th><th>Prénom</th><th>Épreuve</th><th>Langue</th><th>Note</th><th>Tiers-temps</th></tr></thead><tbody>
@foreach($grades as $grade)<tr><td>{{ $grade->student->schoolClass->name }}</td><td>{{ $grade->student->last_name }}</td><td>{{ $grade->student->first_name }}</td><td>{{ $grade->exam->type }} du {{ $grade->exam->exam_date->format('d/m/Y') }}</td><td>{{ $grade->exam->language->label() }}</td><td>{{ $grade->value }}</td><td>{{ $grade->student->extra_time ? 'Oui' : 'Non' }}</td></tr>@endforeach
</tbody></table>
@endsection
