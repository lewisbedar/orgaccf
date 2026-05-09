@extends('layouts.print', ['title' => 'Liste de passage oral'])
@section('content')
@include('partials.print-header')
<h2>Liste de passage oral{{ $exam->is_catchup ? ' · rattrapage' : '' }}</h2>
<table><thead><tr><th>Horaire</th><th>Nom</th><th>Prénom</th><th>Tiers-temps</th><th>Observation</th></tr></thead><tbody>
@foreach($exam->slots as $slot)<tr><td>{{ substr($slot->pass_time,0,5) }}</td><td>{{ $slot->student->last_name }}</td><td>{{ $slot->student->first_name }}</td><td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td><td></td></tr>@endforeach
</tbody></table>
@endsection
