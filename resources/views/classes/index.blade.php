@extends('layouts.app', ['title' => 'Classes'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Classes</h1>
        <p class="muted">Créez une classe, importez ses élèves ou consultez les listes existantes.</p>
    </div>
    <a class="button" href="{{ route('classes.create') }}">Créer une classe</a>
</section>

<div class="list-grid">
    @forelse($classes as $class)
        <article class="card class-card" style="--class-color: {{ $class->displayColor() }}">
            <h2><a href="{{ route('classes.show', $class) }}">{{ $class->name }}</a></h2>
            <p>{{ $class->students_count }} élève(s)</p>
        </article>
    @empty
        <section class="empty-state">Aucune classe pour le moment.</section>
    @endforelse
</div>
@endsection
