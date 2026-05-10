@extends('layouts.print', ['title' => 'Convocations'])

@section('content')
@php
    $isOral = $exam->type === 'oral';
    $examTypeLabel = $isOral ? 'ORAL' : 'ÉCRIT';
    $durationLabel = $isOral ? '00h15' : '01h00';
    $dateLabel = $exam->exam_date->copy()->locale('fr')->translatedFormat('l j F Y');
    $speakerLabel = $isOral ? 'Examinateur / jury' : 'Surveillant';
    $speakerName = $exam->teacher?->display_name ?: $exam->supervisor_name;
@endphp

@foreach($exam->slots->sortBy(fn ($slot) => $slot->student->last_name . ' ' . $slot->student->first_name) as $slot)
    @php
        $student = $slot->student;
        $timeLabel = $isOral ? substr($slot->pass_time, 0, 5) : substr($exam->start_time, 0, 5);
    @endphp

    <article class="convocation-page">
        @include('partials.print-header')

        <section class="convocation-title">
            <h1>CONVOCATION</h1>
            <p>CCF Langues Vivantes</p>
            @if($exam->is_catchup)
                <strong>Rattrapage</strong>
            @endif
        </section>

        <p class="convocation-exam"><strong>Examen :</strong> Contrôle en cours de formation - Langues vivantes</p>

        <section class="convocation-student">
            <strong>{{ mb_strtoupper($student->last_name) }}, {{ $student->first_name }}</strong>
            <span>Classe de {{ $exam->schoolClass->name }}</span>
        </section>

        <p class="convocation-text">
            Je vous prie de vous présenter, muni(e) de la présente convocation et d’une pièce d’identité,
            15 minutes avant le début de l’épreuve, dont la salle, la date et l’horaire sont indiqués ci-dessous.
        </p>

        <section class="convocation-details">
            <h2>{{ mb_strtoupper($exam->language->label()) }} {{ $examTypeLabel }}</h2>
            <p>Durée {{ $durationLabel }}</p>
            <p>Le {{ $dateLabel }} à {{ $timeLabel }}</p>
            <p>Salle {{ $exam->room }}</p>
            @if($speakerName)
                <p>{{ $speakerLabel }} : {{ $speakerName }}</p>
            @endif
        </section>

        @if(!$isOral && $student->extra_time)
            <p class="convocation-notice">Tiers-temps accordé pour l’épreuve écrite.</p>
        @endif

        @if($isOral)
            <p class="convocation-text">
                Pour les épreuves orales, une tolérance est accordée afin que vous puissiez vous rendre à votre épreuve.
                Si votre épreuve se déroule lorsque vous avez cours, vous devez impérativement retourner en cours à la fin de votre épreuve.
            </p>
        @endif

        <section class="convocation-warning">
            <h2>ATTENTION</h2>
            <p>
                Les téléphones portables doivent impérativement être éteints durant les épreuves, et rangés dans les sacs
                ou remis aux surveillants.
            </p>
            <p>
                Un candidat en possession d’un téléphone portable en salle d’examen, ou lors d’une sortie aux toilettes,
                sera considéré en situation de fraude.
            </p>
            <p>Les résultats seront connus lors de la remise du bulletin de notes.</p>
        </section>
    </article>
@endforeach
@endsection
