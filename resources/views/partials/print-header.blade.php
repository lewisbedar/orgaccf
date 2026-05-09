<header class="print-header">
    @if($school->logo_path)<img src="{{ $school->logo_path }}" alt="Logo lycée">@endif
    <div><h1>{{ $school->school_name }}</h1><p>{!! nl2br(e($school->address)) !!}</p><p>{{ $school->phone }} {{ $school->email }}</p></div>
</header>
