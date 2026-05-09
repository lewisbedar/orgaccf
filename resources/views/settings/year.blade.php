@extends('layouts.app', ['title' => 'Premier démarrage'])
@section('content')
<section class="panel narrow"><h1>Premier démarrage</h1><p>Configurez l’année scolaire active.</p>
<form method="post" action="{{ route('setup.year.store') }}" class="stack">@csrf
    <label>Année scolaire <input name="label" value="{{ $proposal }}" pattern="\d{4}-\d{4}" required></label>
    <button>Créer l’année active</button>
</form></section>
@endsection
