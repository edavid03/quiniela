@props([
    'href',
    'label',
    'active' => false,
])

<a href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    @class([
        'flex flex-col items-center justify-center gap-1 py-1 no-underline transition-colors',
        'text-[var(--app-secondary)]' => $active,
        'text-[var(--app-muted)]' => ! $active,
    ])>
    <span @class([
        'grid h-7 w-12 place-items-center rounded-full transition-colors',
        'bg-[var(--app-panel-soft)]' => $active,
    ])>
        {{ $slot }}
    </span>
    <span class="text-[10px] font-extrabold leading-none">{{ $label }}</span>
</a>
