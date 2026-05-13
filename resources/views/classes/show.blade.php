@extends('layouts.app', ['title' => 'Classe '.$class->name])

@section('content')
<section class="page-heading">
    <div>
        <h1>{{ $class->name }}</h1>
        <p class="muted">{{ $class->students->count() }} élève(s) · {{ $class->schoolYear?->label }}</p>
    </div>
    <a class="button" href="{{ route('students.create') }}">Ajouter un élève</a>
</section>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Naissance</th>
            <th>Langues</th>
            <th>Tiers-temps</th>
        </tr>
    </thead>
    <tbody>
        @forelse($class->students->sortBy(fn ($student) => $student->last_name . ' ' . $student->first_name) as $student)
            <tr>
                <td>{{ $student->last_name }}</td>
                <td>{{ $student->first_name }}</td>
                <td>{{ $student->birth_date?->format('d/m/Y') }}</td>
                <td>{{ $student->languages->map->label()->implode(', ') }}</td>
                <td>{{ $student->extra_time ? 'Oui' : 'Non' }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Aucun élève dans cette classe.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
