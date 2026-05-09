@extends('layouts.auth')
@section('content')
<img src="/images/orgaccf-square.png" class="login-logo" alt="OrgaCCF">
<h1>Connexion</h1>
<form method="post" action="{{ route('login.store') }}" class="stack">@csrf
    <label>Identifiant <input name="username" value="{{ old('username') }}" required autofocus></label>
    <label>Mot de passe <input type="password" name="password" required></label>
    <button>Se connecter</button>
</form>
<p class="muted">Compte initial : <code>admin</code> / <code>admin123</code></p>
@endsection
