<header class="print-header">
    @if($school->logo_path)
        <img src="{{ $school->logo_path }}" alt="Logo lycée">
    @endif
    <div>
        <h1>{{ $school->school_name }}</h1>
        @if($school->address)
            <p>{!! nl2br(e($school->address)) !!}</p>
        @endif
        @if($school->phone || $school->email)
            <p>{{ trim($school->phone . ' ' . $school->email) }}</p>
        @endif
    </div>
</header>
