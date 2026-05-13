@extends('layouts.app', ['title' => 'Épreuves'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Épreuves</h1>
        <p class="muted">Retrouvez les épreuves prévues, les documents à imprimer et les notes à saisir.</p>
    </div>
    <div class="heading-actions">
        @include('partials.help-popover', [
            'helpTitle' => 'Aide épreuves',
            'helpItems' => [
                ['title' => 'À venir', 'text' => 'l’épreuve est planifiée mais pas encore passée.'],
                ['title' => 'Notes à saisir', 'text' => 'l’épreuve est passée et attend une saisie.'],
                ['title' => 'Terminée', 'text' => 'les notes ont été enregistrées.'],
            ],
        ])
        <a class="button" href="{{ route('exams.create', ['reset' => 1]) }}">Planifier</a>
    </div>
</section>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Classe</th>
            <th>Langue</th>
            <th>Salle</th>
            <th>Statut</th>
            <th>Prochaine action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($exams as $exam)
            @php
                $isPast = $exam->exam_date->isPast() || $exam->exam_date->isToday();
                $needsGrades = $exam->status !== 'terminee' && $isPast;
            @endphp
            <tr>
                <td>{{ $exam->exam_date->format('d/m/Y') }} {{ substr($exam->start_time, 0, 5) }}</td>
                <td>{{ $exam->type === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}{{ $exam->is_catchup ? ' · rattrapage' : '' }}</td>
                <td><span class="class-chip" style="--class-color: {{ $exam->schoolClass->displayColor() }}">{{ $exam->schoolClass->name }}</span></td>
                <td>{{ $exam->language->label() }}</td>
                <td>{{ $exam->room }}</td>
                <td>
                    @if($exam->status === 'terminee')
                        <span class="status-pill status-success">Terminée</span>
                    @elseif($needsGrades)
                        <span class="status-pill status-warning">Notes à saisir</span>
                    @else
                        <span class="status-pill">À venir</span>
                    @endif
                </td>
                <td class="actions">
                    @if($needsGrades)
                        <a href="{{ route('grades.edit', $exam) }}">Saisir les notes</a>
                    @else
                        <a href="{{ route('exams.show', $exam) }}">Ouvrir</a>
                    @endif
                    <a href="{{ route('grades.summary', ['exam_id' => $exam->id]) }}">Consulter</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Aucune épreuve pour le moment.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
