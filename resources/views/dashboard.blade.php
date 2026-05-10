@extends('layouts.app', ['title' => 'Tableau de bord'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Tableau de bord</h1>
        <p class="muted">Les actions importantes de l’année scolaire en cours, au même endroit.</p>
    </div>
    @if(auth()->user()->isCoordinator())
        <a class="button" href="{{ route('exams.create') }}">Planifier une épreuve</a>
    @else
        <a class="button" href="{{ route('grades.summary') }}">Saisir les notes</a>
    @endif
</section>

<section class="quick-actions">
    <a class="action-card" href="{{ route('classes.index') }}">
        <strong>Voir mes classes</strong>
        <span>Consulter les élèves et les langues concernées.</span>
    </a>
    @if(auth()->user()->isCoordinator())
        <a class="action-card" href="{{ route('exams.create') }}">
            <strong>Planifier une épreuve</strong>
            <span>Choisir écrit ou oral, puis générer les horaires.</span>
        </a>
    @else
        <a class="action-card" href="{{ route('exams.index') }}">
            <strong>Voir mes épreuves</strong>
            <span>Retrouver les dates, salles et documents utiles.</span>
        </a>
    @endif
    <a class="action-card" href="{{ route('grades.summary') }}">
        <strong>Saisir les notes</strong>
        <span>Entrer une note ou AB pour les absences.</span>
    </a>
    <a class="action-card" href="{{ route('grades.final') }}">
        <strong>Voir le bilan</strong>
        <span>Contrôler écrit /12, oral /8 et total /20.</span>
    </a>
</section>

<section class="stat-grid">
@foreach([
    'classes' => 'Classes',
    'students' => 'Élèves',
    'planned' => 'Épreuves',
    'finished' => 'Terminées',
    'notes_to_enter' => 'Notes à saisir',
    'absents' => 'Rattrapages à prévoir',
] as $key => $label)
    <article><strong>{{ $stats[$key] }}</strong><span>{{ $label }}</span></article>
@endforeach
</section>

<section class="guide-grid">
    <article class="panel">
        <div class="section-title">
            <h2>À faire maintenant</h2>
        </div>

        <div class="todo-list">
            @forelse($todoExams as $exam)
                <a href="{{ route('grades.edit', $exam) }}" class="todo-item">
                    <span class="status-pill status-warning">Notes à saisir</span>
                    <strong>{{ $exam->type === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}</strong>
                    <span>{{ $exam->schoolClass->name }} · {{ $exam->language->label() }} · {{ $exam->exam_date->format('d/m/Y') }}</span>
                </a>
            @empty
                <p class="muted">Aucune épreuve passée en attente de notes.</p>
            @endforelse

            @foreach($catchupExams as $exam)
                <a href="{{ route('exams.show', $exam) }}" class="todo-item">
                    <span class="status-pill status-danger">Rattrapage</span>
                    <strong>{{ $exam->type === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}</strong>
                    <span>{{ $exam->schoolClass->name }} · {{ $exam->language->label() }}</span>
                </a>
            @endforeach
        </div>
    </article>

    <aside class="panel help-panel">
        <h2>Besoin d’aide ?</h2>
        <details open>
            <summary>Planifier un oral</summary>
            <p>Choisissez “Épreuve orale”. Les horaires individuels sont générés automatiquement avec 10 minutes d’oral et 5 minutes de pause.</p>
        </details>
        <details>
            <summary>Saisir une absence</summary>
            <p>Saisissez AB dans la note. Le motif apparaît ensuite et devient obligatoire.</p>
        </details>
        <details>
            <summary>Imprimer les documents</summary>
            <p>Ouvrez une épreuve, puis utilisez les boutons PDF : convocations, émargement et liste de passage oral.</p>
        </details>
    </aside>
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
