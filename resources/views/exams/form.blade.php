@extends('layouts.app', ['title' => 'Nouvelle épreuve'])
@section('content')
<section class="page-heading"><h1>Planifier une épreuve</h1></section>
<form method="post" action="{{ route('exams.store') }}" class="panel form-grid">@csrf
    <label>Type <select name="type"><option value="ecrit">Écrit · 1h</option><option value="oral">Oral · 10 min + 5 min pause</option></select></label>
    <label>Classe <select name="school_class_id">@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
    <label>Langue / niveau <select name="language_id">@foreach($languages as $language)<option value="{{ $language->id }}">{{ $language->label() }}</option>@endforeach</select></label>
    <label>Date <input type="date" name="exam_date" required></label>
    <label>Heure de début <input type="time" name="start_time" required></label>
    <label>Salle <input name="room" required></label>
    <label>Examinateur / jury <select name="teacher_id"><option value="">Non renseigné</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->display_name }}</option>@endforeach</select></label>
    <label>Surveillant écrit <input name="supervisor_name"></label>
    <div class="form-actions"><button>Créer l’épreuve</button></div>
</form>
@endsection
