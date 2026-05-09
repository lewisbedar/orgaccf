@extends('layouts.app', ['title' => 'Saisie des notes'])
@section('content')
<section class="page-heading"><h1>Saisie des notes</h1></section>
<form method="post" action="{{ route('grades.update',$exam) }}" class="panel stack">@csrf
<table><thead><tr><th>Nom</th><th>Prénom</th><th>Tiers-temps</th><th>Note ou AB</th></tr></thead><tbody>
@foreach($exam->slots as $slot)<tr><td>{{ $slot->student->last_name }}</td><td>{{ $slot->student->first_name }}</td><td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td><td><input class="grade-input" name="grades[{{ $slot->student_id }}]" value="{{ $exam->grades->firstWhere('student_id',$slot->student_id)?->value }}"></td></tr>@endforeach
</tbody></table><button>Enregistrer</button></form>
@endsection
