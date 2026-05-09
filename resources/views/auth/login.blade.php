@extends('layouts.auth')

@section('content')
<main class="login-shell">
    <img src="/images/orgaccf-square.png" class="login-mark" alt="OrgaCCF">

    <form method="post" action="{{ route('login.store') }}" class="login-box">
        @csrf
        <header class="login-heading">
            <h1>Connexion</h1>
            <p>Aide à l'organisation des CCF en voie professionnelle</p>
        </header>

        <div class="login-row login-row-muted">
            <label for="school_setting_id">Établissement</label>
            <select id="school_setting_id" name="school_setting_id">
                @forelse($schools as $school)
                    <option value="{{ $school->id }}" @selected(old('school_setting_id') == $school->id)>{{ $school->school_name }}</option>
                @empty
                    <option value="">Établissement à configurer</option>
                @endforelse
            </select>
        </div>

        <div class="login-row login-row-muted">
            <label for="school_year_id">Année scolaire</label>
            <select id="school_year_id" name="school_year_id">
                @forelse($years as $year)
                    <option value="{{ $year->id }}" @selected(old('school_year_id', $activeYear?->id) == $year->id)>{{ $year->label }}</option>
                @empty
                    <option value="">Année à configurer</option>
                @endforelse
            </select>
        </div>

        <div class="login-row">
            <label for="username">Identifiant</label>
            <input id="username" name="username" value="{{ old('username') }}" required autofocus>
        </div>

        <div class="login-row">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required>
        </div>

        <button>Se connecter</button>
    </form>

    <p class="login-copyright">© {{ date('Y') }} OrgaCCF</p>
</main>
@endsection
