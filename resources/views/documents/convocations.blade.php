@extends('layouts.print', ['title' => 'Convocations'])

@section('content')
<style>
    .print-page{background:#fff;padding:28px;color:#111;font-family:DejaVu Sans,Arial,sans-serif}
    .print-header{display:flex;gap:18px;border-bottom:2px solid #111;padding-bottom:14px;margin-bottom:20px}
    .print-header img{width:90px;max-height:90px;object-fit:contain}
    .print-header h1{margin:0 0 6px;font-size:20px}
    .print-header p{margin:2px 0;font-size:12px}
    .convocation-page{min-height:270mm;page-break-after:always}
    .convocation-page:last-child{page-break-after:auto}
    .convocation-title{text-align:center;margin:18px 0 22px}
    .convocation-title h1{margin:0;font-size:26px;letter-spacing:1px}
    .convocation-title p{margin:6px 0 0;font-size:16px}
    .convocation-exam{margin:0 0 28px}
    .convocation-student{margin:0 0 22px;font-size:15px}
    .convocation-student strong{display:block;font-size:17px;margin-bottom:6px}
    .convocation-text{line-height:1.5;margin:18px 0;font-size:13px}
    .convocation-exams{margin:22px 0 30px}
    .convocation-details{margin:0 0 22px;page-break-inside:avoid}
    .convocation-details h2{font-size:17px;margin:0 0 8px}
    .convocation-details h2 span{font-size:12px;border:1px solid #111;padding:2px 6px;margin-left:8px}
    .convocation-details p{margin:5px 0;font-size:14px}
    .convocation-notice{display:inline-block;border:2px solid #111;padding:6px 9px;font-weight:bold;margin:4px 0 0}
    .convocation-warning{margin-top:42px}
    .convocation-warning h2{text-align:center;font-size:15px;margin:0 0 10px}
    .convocation-warning p{font-weight:bold;line-height:1.42;margin:8px 0;font-size:13px}
</style>

@foreach($convocations as $convocation)
    @php
        $student = $convocation['student'];
        $hasOralExam = $convocation['exams']->contains('type', 'oral');
    @endphp

    <article class="convocation-page">
        @include('partials.print-header')

        <section class="convocation-title">
            <h1>CONVOCATION</h1>
            <p>CCF Langues Vivantes</p>
        </section>

        <p class="convocation-exam"><strong>Examen :</strong> Contrôle en cours de formation - Langues vivantes</p>

        <section class="convocation-student">
            <strong>{{ mb_strtoupper($student->last_name) }}, {{ $student->first_name }}</strong>
            <span>Classe de {{ $exam->schoolClass->name }}</span>
        </section>

        <p class="convocation-text">
            Je vous prie de vous présenter, muni(e) de la présente convocation et d’une pièce d’identité,
            15 minutes avant le début de chaque épreuve, dont les salles, dates et horaires sont indiqués ci-dessous.
        </p>

        <section class="convocation-exams">
            @foreach($convocation['exams'] as $studentExam)
                @php
                    $isOral = $studentExam->type === 'oral';
                    $studentSlot = $studentExam->studentSlot;
                    $timeLabel = $isOral ? substr($studentSlot?->pass_time, 0, 5) : substr($studentExam->start_time, 0, 5);
                    $dateLabel = $studentExam->exam_date->copy()->locale('fr')->translatedFormat('l j F Y');
                    $speakerLabel = $isOral ? 'Examinateur / jury' : 'Surveillant';
                    $speakerName = $studentExam->teacher?->display_name ?: $studentExam->supervisor_name;
                @endphp

                <div class="convocation-details">
                    <h2>
                        {{ mb_strtoupper($studentExam->language->label()) }}
                        {{ $isOral ? 'ORAL' : 'ÉCRIT' }}
                        @if($studentExam->is_catchup)
                            <span>RATTRAPAGE</span>
                        @endif
                    </h2>
                    <p>Durée {{ $isOral ? '00h15' : '01h00' }}</p>
                    <p>Le {{ $dateLabel }} à {{ $timeLabel }}</p>
                    <p>Salle {{ $studentExam->room }}</p>
                    @if($speakerName)
                        <p>{{ $speakerLabel }} : {{ $speakerName }}</p>
                    @endif
                    @if(!$isOral && $student->extra_time)
                        <p class="convocation-notice">Tiers-temps accordé pour l’épreuve écrite.</p>
                    @endif
                </div>
            @endforeach
        </section>

        @if($hasOralExam)
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
