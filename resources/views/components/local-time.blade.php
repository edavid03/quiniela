@props(['date'])

@php
    $dateUtc = \Carbon\Carbon::parse($date, 'UTC')->utc();
@endphp

<time datetime="{{ $dateUtc->toIso8601String() }}" data-local-time="{{ $dateUtc->toIso8601String() }}">--/--/---- --:--</time>
