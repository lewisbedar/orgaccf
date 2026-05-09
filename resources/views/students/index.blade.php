@extends('layouts.app', ['title' => 'Élèves'])

@section('content')
<section class="page-heading">
    <h1>Élèves</h1>
    <a class="button" href="{{ route('students.create') }}">Ajouter</a>
</section>

<table>
    <thead>
        <tr><th>Classe</th><th>Nom</th><th>Prénom</th><th>Langues</th><th>Tiers-temps</th><th></th></tr>
    </thead>
    <tbody>
        @foreach($students as $student)
            <tr>
                <td><span class="class-chip" style="--class-color: {{ $student->schoolClass->displayColor() }}">{{ $student->schoolClass->name }}</span></td>
                <td>{{ $student->last_name }}</td>
                <td>{{ $student->first_name }}</td>
                <td>{{ $student->languages->map->label()->implode(', ') }}</td>
                <td>{{ $student->extra_time ? 'Oui' : 'Non' }}</td>
                <td class="actions">
                    <a class="icon-action" href="{{ route('students.edit', $student) }}" title="Modifier" aria-label="Modifier">@include('partials.icon', ['name' => 'edit'])</a>
                    <form method="post" action="{{ route('students.destroy', $student) }}">
                        @csrf
                        @method('DELETE')
                        <button class="icon-action danger-text" title="Supprimer" aria-label="Supprimer">@include('partials.icon', ['name' => 'delete'])</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
