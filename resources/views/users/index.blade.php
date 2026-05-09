@extends('layouts.app', ['title' => 'Utilisateurs'])
@section('content')
<section class="page-heading"><h1>Utilisateurs</h1><a class="button" href="{{ route('users.create') }}">Ajouter</a></section>
<table><thead><tr><th>Identifiant</th><th>Nom affiché</th><th>Rôle</th><th>Langues</th><th>Classes</th><th></th></tr></thead><tbody>
@foreach($users as $user)<tr class="{{ $user->is_active ? '' : 'muted-row' }}"><td>{{ $user->username }}</td><td>{{ $user->display_name }}</td><td>{{ $user->role }}</td><td>{{ $user->languages_text }}</td><td>{{ $user->classes_text }}</td><td class="actions"><a href="{{ route('users.edit',$user) }}">Modifier</a><form method="post" action="{{ route('users.destroy',$user) }}">@csrf @method('DELETE')<button class="link danger-text">Désactiver</button></form></td></tr>@endforeach
</tbody></table>
@endsection
