@extends('layouts.app', ['title' => 'Tableau de bord'])

@section('content')
<section class="page-heading">
    <h1>Tableau de bord</h1>
    <a class="button" href="{{ route('exams.create') }}">Nouvelle épreuve</a>
</section>

<section class="stat-grid">
@foreach(['classes' => 'Classes', 'students' => 'Élèves', 'planned' => 'Épreuves prévues', 'finished' => 'Épreuves terminées', 'absents' => 'Absents à rattraper'] as $key => $label)
    <article><strong>{{ $stats[$key] }}</strong><span>{{ $label }}</span></article>
@endforeach
</section>

<section class="panel">
    <div class="section-title">
        <h2>Prochaines épreuves</h2>
        <a href="{{ route('exams.index') }}">Voir toutes les épreuves</a>
    </div>

    <div class="agenda agenda-rich">
        @forelse($upcoming->groupBy(fn ($exam) => $exam->exam_date->format('Y-m-d')) as $date => $exams)
            <section class="agenda-day">
                <h3>{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}</h3>
                @foreach($exams as $exam)
                    <article style="--class-color: {{ $exam->schoolClass->displayColor() }}">
                        <time>{{ substr($exam->start_time, 0, 5) }}</time>
                        <div class="agenda-main">
                            <strong>{{ $exam->type === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}{{ $exam->is_catchup ? ' de rattrapage' : '' }}</strong>
                            <div class="agenda-tags">
                                <span class="class-chip" style="--class-color: {{ $exam->schoolClass->displayColor() }}">{{ $exam->schoolClass->name }}</span>
                                <span class="language-chip">
                                    @if($exam->language->icon_path)
                                        <img class="flag" src="{{ $exam->language->icon_path }}" alt="">
                                    @endif
                                    {{ $exam->language->label() }}
                                </span>
                            </div>
                        </div>
                        <div class="agenda-meta">
                            <span>Salle {{ $exam->room }}</span>
                            <span>{{ $exam->type === 'oral' ? 'Jury' : 'Surveillant' }} : {{ $exam->teacher?->display_name ?: $exam->supervisor_name ?: 'Non renseigné' }}</span>
                        </div>
                    </article>
                @endforeach
            </section>
        @empty
            <p class="muted">Aucune épreuve à venir.</p>
        @endforelse
    </div>
</section>
@endsection
