@php
    $helpTitle = $helpTitle ?? 'Aide';
    $helpItems = $helpItems ?? [];
@endphp

<span class="help-popover">
    <button type="button" class="help-trigger" aria-label="{{ $helpTitle }}">?</button>
    <span class="help-bubble" role="tooltip">
        <strong>{{ $helpTitle }}</strong>
        @foreach($helpItems as $item)
            <span>
                <b>{{ $item['title'] }}</b>
                {{ $item['text'] }}
            </span>
        @endforeach
    </span>
</span>
