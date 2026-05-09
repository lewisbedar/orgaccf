@extends('layouts.app', ['title' => 'Aperçu planification'])
@section('content')
<section class="page-heading">
    <div>
        <h1>Aperçu de la planification</h1>
        <p class="muted">{{ $class->name }} · {{ $language->label() }} · {{ \Carbon\Carbon::parse($draft['exam_date'])->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('exams.create') }}">Recommencer</a>
</section>

<form method="post" action="{{ route('exams.confirm') }}" class="panel stack">
    @csrf
    <section class="meta-grid">
        <div><span>Type</span><strong>{{ $draft['type'] === 'oral' ? 'Oral' : 'Écrit' }}</strong></div>
        <div><span>Salle</span><strong>{{ $draft['room'] }}</strong></div>
        <div><span>Début</span><strong>{{ substr($draft['start_time'], 0, 5) }}</strong></div>
        <div><span>Intervenant</span><strong>{{ $teacher?->display_name ?: $draft['supervisor_name'] }}</strong></div>
        <div><span>Candidats</span><strong>{{ count($slots) }}</strong></div>
    </section>

    @if($draft['type'] === 'oral' && count($draft['breaks']))
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

    <div class="table-scroll">
        <table class="import-table">
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
                        <input type="checkbox" name="slots[{{ $index }}][include]" value="1" checked>
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

    <div class="form-actions">
        <button>Créer l’épreuve</button>
    </div>
</form>
@endsection
