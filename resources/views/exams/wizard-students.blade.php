@extends('layouts.app', ['title' => 'Nouvelle épreuve'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Sélection des élèves</h1>
        <p class="muted">Étape 4 sur 4 : classe entière, demi-groupe ou sélection personnalisée.</p>
    </div>
    <a href="{{ route('exams.create', ['reset' => 1]) }}">Recommencer</a>
</section>

<form method="post" action="{{ route('exams.confirm') }}" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span>1. Classe</span>
        <span>2. Épreuve</span>
        <span>3. Organisation</span>
        <span class="active">4. Élèves</span>
    </div>

    <section class="meta-grid">
        <div><span>Classe</span><strong>{{ $class->name }}</strong></div>
        <div><span>Type</span><strong>{{ $draft['type'] === 'oral' ? 'Épreuve orale' : 'Épreuve écrite' }}</strong></div>
        <div><span>Langue</span><strong>{{ $language->label() }}</strong></div>
        <div><span>Date</span><strong>{{ \Carbon\Carbon::parse($draft['exam_date'])->format('d/m/Y') }} {{ substr($draft['start_time'], 0, 5) }}</strong></div>
        <div><span>Salle</span><strong>{{ $draft['room'] }}</strong></div>
    </section>

    @if($draft['type'] === 'oral' && count($draft['breaks'] ?? []))
        <section class="break-summary">
            @foreach($draft['breaks'] as $break)
                <span>{{ $break['label'] }} · {{ $break['start'] }}-{{ $break['end'] }}</span>
            @endforeach
        </section>
    @endif

    @if(count($warnings))
        <section class="planning-warnings">
            @foreach($warnings as $warning)
                <article>
                    <strong>{{ $warning['title'] }}</strong>
                    <span>{{ $warning['message'] }}</span>
                </article>
            @endforeach
        </section>
    @endif

    <div class="selection-toolbar">
        <button type="button" class="ghost-button" data-select-all-students>Classe entière</button>
        <button type="button" class="ghost-button" data-select-first-half>Demi-groupe 1</button>
        <button type="button" class="ghost-button" data-select-second-half>Demi-groupe 2</button>
        <span class="muted">Vous pouvez aussi cocher les élèves un par un.</span>
    </div>

    <div class="table-scroll">
        <table class="import-table" data-student-selection-table>
            <thead>
            <tr>
                <th>Inclure</th>
                @if($draft['type'] === 'oral')<th>Horaire</th>@endif
                <th>Nom</th>
                <th>Prénom</th>
                @if($draft['type'] === 'ecrit')<th>Tiers-temps</th>@endif
            </tr>
            </thead>
            <tbody>
            @forelse($slots as $index => $slot)
                <tr>
                    <td>
                        <input type="hidden" name="slots[{{ $index }}][include]" value="0">
                        <input type="checkbox" name="slots[{{ $index }}][include]" value="1" checked data-student-include>
                        <input type="hidden" name="slots[{{ $index }}][student_id]" value="{{ $slot['student_id'] }}">
                    </td>
                    @if($draft['type'] === 'oral')
                        <td><input type="time" name="slots[{{ $index }}][pass_time]" value="{{ substr($slot['pass_time'], 0, 5) }}"></td>
                    @else
                        <input type="hidden" name="slots[{{ $index }}][pass_time]" value="">
                    @endif
                    <td>{{ $slot['last_name'] }}</td>
                    <td>{{ $slot['first_name'] }}</td>
                    @if($draft['type'] === 'ecrit')<td>{{ $slot['extra_time'] ? 'Oui' : 'Non' }}</td>@endif
                </tr>
            @empty
                <tr><td colspan="{{ $draft['type'] === 'oral' ? 4 : 5 }}">Aucun élève ne correspond à cette classe et cette langue.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ route('exams.wizard.schedule') }}">Précédent</a>
        <button>Créer l’épreuve</button>
    </div>
</form>
@endsection
