@extends('layouts.print', ['title' => 'Récapitulatif des notes'])

@section('content')
@include('partials.print-header')

@if($exam)
    <h2>Récapitulatif des notes · {{ $exam->type === 'oral' ? 'Oral' : 'Écrit' }}{{ $exam->is_catchup ? ' de rattrapage' : '' }}</h2>
    <p>
        {{ $year?->label }} · {{ $exam->schoolClass->name }} · {{ $exam->language->label() }} ·
        {{ $exam->exam_date->format('d/m/Y') }} {{ substr($exam->start_time, 0, 5) }} · Note / {{ $maxScore }}
    </p>

    <table>
        <thead>
            <tr>
                @if($exam->type === 'oral')<th>Horaire</th>@endif
                <th>Nom</th>
                <th>Prénom</th>
                @if($exam->type === 'ecrit')<th>Tiers-temps</th>@endif
                <th>Note / {{ $maxScore }}</th>
                <th>Absence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exam->slots as $slot)
                @php($grade = $exam->grades->firstWhere('student_id', $slot->student_id))
                <tr>
                    @if($exam->type === 'oral')<td>{{ substr($slot->pass_time, 0, 5) }}</td>@endif
                    <td>{{ $slot->student->last_name }}</td>
                    <td>{{ $slot->student->first_name }}</td>
                    @if($exam->type === 'ecrit')<td>{{ $slot->student->extra_time ? 'Oui' : 'Non' }}</td>@endif
                    <td>{{ $grade?->value ?: '-' }}</td>
                    <td>
                        @if($grade?->value === 'AB')
                            {{ $grade->absence_reason === 'injustifiee' ? 'Injustifiée' : ($grade->absence_reason === 'justifiee' ? 'Justifiée' : '-') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <h2>Récapitulatif des notes</h2>
    <p>Aucune épreuve sélectionnée.</p>
@endif
@endsection
