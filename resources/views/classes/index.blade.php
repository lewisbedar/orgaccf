@extends('layouts.app', ['title' => 'Classes'])

@section('content')
<section class="page-heading">
    <h1>Classes</h1>
    <a class="button" href="{{ route('classes.create') }}">Créer une classe</a>
</section>

<div class="list-grid">
    @foreach($classes as $class)
        <article class="card class-card" style="--class-color: {{ $class->displayColor() }}">
            <span class="class-color-dot"></span>
            <h2><a href="{{ route('classes.show', $class) }}">{{ $class->name }}</a></h2>
            <p>{{ $class->students_count }} élève(s)</p>
        </article>
    @endforeach
</div>
@endsection
