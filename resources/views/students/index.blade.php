@extends('layouts.app', ['title' => 'Élèves'])
@section('content')
<section class="page-heading"><h1>Élèves</h1><a class="button" href="{{ route('students.create') }}">Ajouter</a></section>
<table><thead><tr><th>Classe</th><th>Nom</th><th>Prénom</th><th>Langues</th><th>Tiers-temps</th><th></th></tr></thead><tbody>
@foreach($students as $student)<tr><td>{{ $student->schoolClass->name }}</td><td>{{ $student->last_name }}</td><td>{{ $student->first_name }}</td><td>{{ $student->languages->map->label()->implode(', ') }}</td><td>{{ $student->extra_time ? 'Oui' : 'Non' }}</td><td><a href="{{ route('students.edit',$student) }}">Modifier</a></td></tr>@endforeach
</tbody></table>
@endsection
