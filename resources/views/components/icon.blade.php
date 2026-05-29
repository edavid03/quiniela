@props(['name'])

@php
    $icons = [
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3.5 9h17M3.5 15h17"/><path d="M12 3a14.5 14.5 0 0 1 0 18 14.5 14.5 0 0 1 0-18Z"/>',
        'scale' => '<path d="M12 3v18"/><path d="M5 7h14"/><path d="m5 7-2.5 6a3 3 0 0 0 5 0L5 7Z"/><path d="m19 7-2.5 6a3 3 0 0 0 5 0L19 7Z"/><path d="M8 21h8"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="M7 15v3M12 10v8M17 6v12"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8"/><path d="M18.5 20a6 6 0 0 0-3-5.2"/>',
        'pencil' => '<path d="M16 4h2a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/>',
        'trophy' => '<path d="M8 4h8v4a4 4 0 0 1-8 0V4Z"/><path d="M8 5H5a2 2 0 0 0 0 4h1.2"/><path d="M16 5h3a2 2 0 0 1 0 4h-1.2"/><path d="M12 12v3.5"/><path d="M9.5 20h5"/><path d="M10 16h4l-.4 4h-3.2L10 16Z"/>',
        'bolt' => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
        'envelope' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5l3 2"/>',
        'check' => '<path d="m5 13 4 4L19 7"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'h-6 w-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $icons[$name] ?? '' !!}
</svg>
