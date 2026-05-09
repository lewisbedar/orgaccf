@extends('layouts.app', ['title' => $user->exists ? 'Modifier un utilisateur' : 'Nouvel utilisateur'])
@section('content')
<section class="page-heading"><h1>{{ $user->exists ? 'Modifier un utilisateur' : 'Nouvel utilisateur' }}</h1></section>
<form method="post" action="{{ $user->exists ? route('users.update',$user) : route('users.store') }}" class="panel form-grid">@csrf @if($user->exists)@method('PUT')@endif
    <label>Identifiant <input name="username" value="{{ old('username',$user->username) }}" required></label>
    <label>Nom affiché <input name="display_name" value="{{ old('display_name',$user->display_name) }}" required></label>
    <label>Email <input type="email" name="email" value="{{ old('email',$user->email) }}"></label>
    <label>Mot de passe <input type="password" name="password" {{ $user->exists ? '' : 'required' }}></label>
    <label>Rôle <select name="role"><option value="coordinateur" @selected(old('role',$user->role)==='coordinateur')>Coordinateur</option><option value="enseignant" @selected(old('role',$user->role)==='enseignant')>Enseignant</option></select></label>
    <fieldset><legend>Langues enseignées</legend>@foreach($languages as $language)<label class="check"><input type="checkbox" name="languages[]" value="{{ $language->id }}" @checked(in_array($language->id,$selectedLanguages))> {{ $language->label() }}</label>@endforeach</fieldset>
    <fieldset><legend>Classes associées</legend>@foreach($classes as $class)<label class="check"><input type="checkbox" name="classes[]" value="{{ $class->id }}" @checked(in_array($class->id,$selectedClasses))> {{ $class->name }}</label>@endforeach</fieldset>
    <div class="form-actions"><button>Enregistrer</button></div>
</form>
@endsection
