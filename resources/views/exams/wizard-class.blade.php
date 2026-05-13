@extends('layouts.app', ['title' => 'Nouvelle épreuve'])

@section('content')
<section class="page-heading">
    <div>
        <h1>Planifier une épreuve</h1>
        <p class="muted">Étape 1 sur 4 : choisissez la classe concernée.</p>
    </div>
</section>

<form method="post" action="{{ route('exams.wizard.class') }}" class="panel stack">
    @csrf
    <div class="wizard-steps">
        <span class="active">1. Classe</span>
        <span>2. Épreuve</span>
        <span>3. Organisation</span>
        <span>4. Élèves</span>
    </div>

    <label>Classe concernée
        <select name="school_class_id" required autofocus>
            <option value="">Choisir une classe</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(old('school_class_id', $draft['school_class_id'] ?? '') == $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
    </label>

    <div class="form-actions wizard-actions">
        <a class="button ghost-button" href="{{ route('exams.index') }}">Annuler</a>
        <button>Suivant</button>
    </div>
</form>
@endsection
