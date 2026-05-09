@extends('layouts.app', ['title' => 'Récapitulatif des notes'])
@section('content')
<section class="page-heading"><h1>Récapitulatif des notes</h1><a class="button" target="_blank" href="{{ route('documents.grades') }}">Version imprimable</a></section>
<table><thead><tr><th>Classe</th><th>Nom</th><th>Prénom</th><th>Langues</th><th>Tiers-temps</th></tr></thead><tbody>
@foreach($students as $student)<tr><td>{{ $student->schoolClass->name }}</td><td>{{ $student->last_name }}</td><td>{{ $student->first_name }}</td><td>{{ $student->languages->map->label()->implode(', ') }}</td><td>{{ $student->extra_time ? 'Oui' : 'Non' }}</td></tr>@endforeach
</tbody></table>
@endsection
