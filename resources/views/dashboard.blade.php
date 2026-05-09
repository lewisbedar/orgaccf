@extends('layouts.app', ['title' => 'Tableau de bord'])

@section('content')
<section class="page-heading">
    <h1>Tableau de bord</h1>
    <a class="button" href="{{ route('exams.create') }}">Nouvelle Ã©preuve</a>
</section>

<section class="stat-grid">
@foreach(['classes' => 'Classes', 'students' => 'Ã‰lÃ¨ves', 'planned' => 'Ã‰preuves prÃ©vues', 'finished' => 'Ã‰preuves terminÃ©es', 'absents' => 'Absents Ã  rattraper'] as $key => $label)
    <article><strong>{{ $stats[$key] }}</strong><span>{{ $label }}</span></article>
@endforeach
</section>

<section class="panel">
    <div class="section-title">
        <h2>Prochaines Ã©preuves</h2>
        <a href="{{ route('exams.index') }}">Voir toutes les Ã©preuves</a>
    </div>

    <div class="agenda agenda-rich">
        @forelse($upcoming->groupBy(fn ($exam) => $exam->exam_date->format('Y-m-d')) as $date => $exams)
            <section class="agenda-day">
                <h3>{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}</h3>
                @foreach($exams as $exam)
                    <article style="--class-color: {{ $exam->schoolClass->displayColor() }}">
                        <time>{{ substr($exam->start_time, 0, 5) }}</time>
                        <div class="agenda-main">
                            <strong>{{ $exam->type === 'oral' ? 'Ã‰preuve orale' : 'Ã‰preuve Ã©crite' }}{{ $exam->is_catchup ? ' de rattrapage' : '' }}</strong>
                            <span><span class="class-chip" style="--class-color: {{ $exam->schoolClass->displayColor() }}">{{ $exam->schoolClass->name }}</span> {{ $exam->language->label() }}</span>
                        </div>
                        <div class="agenda-meta">
                            <span>Salle {{ $exam->room }}</span>
                            <span>{{ $exam->type === 'oral' ? 'Jury' : 'Surveillant' }} : {{ $exam->teacher?->display_name ?: $exam->supervisor_name ?: 'Non renseignÃ©' }}</span>
                        </div>
                    </article>
                @endforeach
            </section>
        @empty
            <p class="muted">Aucune Ã©preuve Ã  venir.</p>
        @endforelse
    </div>
</section>
@endsection
