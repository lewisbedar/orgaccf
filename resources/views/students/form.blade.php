@extends('layouts.app', ['title' => $student->exists ? 'Modifier un élève' : 'Nouvel élève'])
@section('content')
<section class="page-heading"><h1>{{ $student->exists ? 'Modifier un élève' : 'Nouvel élève' }}</h1></section>
<form method="post" action="{{ $student->exists ? route('students.update',$student) : route('students.store') }}" class="panel form-grid">@csrf @if($student->exists)@method('PUT')@endif
    <label>Classe <select name="school_class_id">@foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('school_class_id',$student->school_class_id)==$class->id)>{{ $class->name }}</option>@endforeach</select></label>
    <label>Nom <input name="last_name" value="{{ old('last_name',$student->last_name) }}" required></label>
    <label>Prénom <input name="first_name" value="{{ old('first_name',$student->first_name) }}" required></label>
    <label>Date de naissance <input type="date" name="birth_date" value="{{ old('birth_date',$student->birth_date?->format('Y-m-d')) }}"></label>
    <label>Email <input type="email" name="email" value="{{ old('email',$student->email) }}"></label>
    <label class="check"><input type="checkbox" name="extra_time" value="1" @checked(old('extra_time',$student->extra_time))> Tiers-temps</label>
    <fieldset><legend>Langues</legend>@foreach($languages as $language)<label class="check"><input type="checkbox" name="language_ids[]" value="{{ $language->id }}" @checked(in_array($language->id,$selectedLanguages))> <img class="flag" src="{{ $language->icon_path }}" alt=""> {{ $language->label() }}</label>@endforeach</fieldset>
    <div class="form-actions"><button>Enregistrer</button></div>
</form>
@endsection
