@extends('layouts.app', ['title' => 'Tableau de bord'])
@section('content')
<section class="page-heading"><h1>Tableau de bord</h1><a class="button" href="{{ route('exams.create') }}">Nouvelle épreuve</a></section>
<section class="stat-grid">
@foreach(['classes'=>'Classes','students'=>'Élèves','planned'=>'Épreuves prévues','finished'=>'Épreuves terminées','absents'=>'Absents à rattraper'] as $key=>$label)
    <article><strong>{{ $stats[$key] }}</strong><span>{{ $label }}</span></article>
@endforeach
</section>
<section class="panel"><h2>Prochaines épreuves</h2><div class="agenda">
@forelse($upcoming as $exam)
    <article><time>{{ $exam->exam_date->format('d/m/Y') }} {{ substr($exam->start_time,0,5) }}</time><div><strong>{{ $exam->type === 'oral' ? 'Oral' : 'Écrit' }}{{ $exam->is_catchup ? ' de rattrapage' : '' }}</strong><span>{{ $exam->schoolClass->name }} · {{ $exam->language->label() }}</span><small>Salle {{ $exam->room }} · {{ $exam->teacher?->display_name ?: $exam->supervisor_name }}</small></div></article>
@empty <p class="muted">Aucune épreuve à venir.</p>
@endforelse
</div></section>
@endsection
