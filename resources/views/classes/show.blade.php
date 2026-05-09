@extends('layouts.app', ['title' => 'Classe '.$class->name])
@section('content')
<section class="page-heading"><h1>{{ $class->name }}</h1><a class="button" href="{{ route('students.create') }}">Ajouter un élève</a></section>
<table><thead><tr><th>Nom</th><th>Prénom</th><th>Naissance</th><th>Langues</th><th>Tiers-temps</th></tr></thead><tbody>
@foreach($class->students as $student)<tr><td>{{ $student->last_name }}</td><td>{{ $student->first_name }}</td><td>{{ $student->birth_date?->format('d/m/Y') }}</td><td>{{ $student->languages->map->label()->implode(', ') }}</td><td>{{ $student->extra_time ? 'Oui' : 'Non' }}</td></tr>@endforeach
</tbody></table>
@endsection
