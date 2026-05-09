@extends('layouts.app', ['title' => 'Épreuves'])

@section('content')
<section class="page-heading">
    <h1>Épreuves</h1>
    <a class="button" href="{{ route('exams.create') }}">Planifier</a>
</section>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Classe</th>
            <th>Langue</th>
            <th>Salle</th>
            <th>Intervenant</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($exams as $exam)
            <tr>
                <td>{{ $exam->exam_date->format('d/m/Y') }} {{ substr($exam->start_time, 0, 5) }}</td>
                <td>{{ $exam->type === 'oral' ? 'Oral' : 'Écrit' }}{{ $exam->is_catchup ? ' · rattrapage' : '' }}</td>
                <td>{{ $exam->schoolClass->name }}</td>
                <td>{{ $exam->language->label() }}</td>
                <td>{{ $exam->room }}</td>
                <td>{{ $exam->teacher?->display_name ?: $exam->supervisor_name }}</td>
                <td class="actions">
                    <a href="{{ route('exams.show', $exam) }}">Ouvrir</a>
                    <a href="{{ route('grades.edit', $exam) }}">Saisir</a>
                    <a href="{{ route('grades.summary', ['exam_id' => $exam->id]) }}">Notes</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
