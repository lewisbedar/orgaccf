@extends('layouts.app', ['title' => 'Établissement'])
@section('content')
<section class="page-heading">
    <h1>Établissement</h1>
</section>

<section class="grid-two">
    <form method="post" action="{{ route('settings.school.update') }}" enctype="multipart/form-data" class="panel stack">
        @csrf
        <label>Nom du lycée
            <input name="school_name" value="{{ old('school_name', $school->school_name) }}" required>
        </label>
        <label>Adresse
            <textarea name="address">{{ old('address', $school->address) }}</textarea>
        </label>
        <label>Téléphone
            <input name="phone" value="{{ old('phone', $school->phone) }}">
        </label>
        <label>Email
            <input type="email" name="email" value="{{ old('email', $school->email) }}">
        </label>
        <label>Zone académique
            <select name="academic_zone" required>
                @foreach(['A' => 'Zone A', 'B' => 'Zone B', 'C' => 'Zone C'] as $zone => $label)
                    <option value="{{ $zone }}" @selected(old('academic_zone', $school->academic_zone ?? 'C') === $zone)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>Logo du lycée
            <input type="file" name="logo" accept="image/*">
        </label>
        <button>Enregistrer</button>
    </form>

    <div class="panel stack">
        <h2>Année scolaire</h2>
        <p>Active : <strong>{{ $year?->label ?? 'aucune' }}</strong></p>
        @if($year)
            <form method="post" action="{{ route('settings.year.close') }}">
                @csrf
                <button class="danger">Clôturer l’année</button>
            </form>
        @endif
        <form method="post" action="{{ route('settings.year.open') }}" class="stack">
            @csrf
            <label>Nouvelle année
                <input name="label" value="{{ $proposal }}" required>
            </label>
            <label class="check"><input type="checkbox" name="duplicate_classes" value="1"> Dupliquer les classes sans les notes</label>
            <button>Ouvrir une année</button>
        </form>
    </div>
</section>
@endsection
