@props(['name'])

@if($name === 'edit')
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4.8L19.3 9.5a2.2 2.2 0 0 0 0-3.1l-1.7-1.7a2.2 2.2 0 0 0-3.1 0L4 15.2V20Zm2-2v-2l9.9-9.9 2 2L8 18H6Z"/></svg>
@elseif($name === 'delete')
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 21a2 2 0 0 1-2-2V8H4V6h4V4h8v2h4v2h-1v11a2 2 0 0 1-2 2H7Zm0-13v11h10V8H7Zm3 9h2v-7h-2v7Zm4 0h2v-7h-2v7Z"/></svg>
@endif
