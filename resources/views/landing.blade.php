@extends('layouts.marketing')

@section('title', config('app.name', 'Quiniela Mundial').' · Tu quiniela privada del Mundial 2026')

@section('content')
    {{-- HERO --}}
    <section class="landing-hero">
        <div class="app-shell relative grid gap-10 py-16 md:py-24 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
            <div>
                <span class="inline-flex rounded-lg border border-current/20 bg-current/10 px-3 py-2 font-display text-[11px] font-black uppercase sm:text-xs">Quinielas privadas · #SOMOS26</span>
                <h1 class="mt-6 font-display text-4xl font-black leading-[1.02] sm:text-6xl md:text-7xl">Arma tu quiniela del Mundial 2026</h1>
                <p class="mt-5 max-w-xl text-base font-semibold leading-7 opacity-80 sm:text-lg">Tu propia liga privada con su admin, sus jugadores y su ranking. Puntuación atumatica. Sin planillas, sin complicaciones.</p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#contacto" class="btn btn-primary">Quiero mi liga</a>
                    <a href="#como-funciona" class="btn btn-secondary">Ver cómo funciona</a>
                </div>
            </div>

            <div class="grid gap-3">
                @foreach ([
                    ['globe', 'El Mundial completo, ya cargado', '48 selecciones y 104 partidos listos para pronosticar.'],
                    ['scale', 'Puntaje simple y justo', '3 puntos al marcador exacto, 1 punto si aciertas el ganador.'],
                    ['chart', 'Ranking en vivo', 'La tabla se reordena sola automaticamente.'],
                ] as [$icon, $title, $copy])
                    <div class="flex items-start gap-4 rounded-lg border border-current/15 bg-current/5 p-4 backdrop-blur-sm">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-current/10">
                            <x-icon :name="$icon" class="h-6 w-6 text-[var(--app-secondary)]" />
                        </span>
                        <div>
                            <span class="block font-display text-sm font-black sm:text-base">{{ $title }}</span>
                            <span class="mt-0.5 block text-xs font-semibold leading-5 opacity-75 sm:text-sm">{{ $copy }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- QUE OFRECEMOS --}}
    <section id="que-ofrecemos" class="scroll-mt-24 bg-[var(--app-bg)] py-16 md:py-20">
        <div class="app-shell">
            <div class="max-w-2xl">
                <span class="kicker">Qué ofrecemos</span>
                <h2 class="page-heading mt-3 md:text-5xl">Todo lo que tu grupo necesita para competir</h2>
                <p class="mt-3 text-base font-semibold leading-7 text-[var(--app-muted)]">Una plataforma pensada para que jugar entre amigos, la oficina o el club sea simple y justo.</p>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['users', 'Ligas privadas', 'Cada grupo tiene su propia liga con su admin, sus jugadores y su ranking aislado del resto.'],
                    ['pencil', 'Pronósticos por partido', 'Cada jugador carga su marcador antes del cierre. Después ya no se puede tocar: cero trampas.'],
                    ['trophy', 'Ranking automático', '3 puntos por marcador exacto, 1 por acertar el ganador. La tabla se ordena sola.'],
                    ['bolt', 'Resultados centralizados', 'Cargas el resultado oficial una sola vez y se puntúa a TODAS las ligas al instante.'],
                    ['envelope', 'Invitaciones por mail', 'Subes un Excel con tus jugadores y el sistema les envía el acceso por correo.'],
                    ['clock', 'Cierre por partido', 'Cada pronostico se bloquea 30 minutos antes del inicio de su partido.'],
                ] as [$icon, $title, $copy])
                    <article class="surface-strong p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-lg bg-[var(--app-panel-soft)] text-[var(--app-primary)]">
                            <x-icon :name="$icon" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-4 font-display text-lg font-black text-[var(--app-text)]">{{ $title }}</h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[var(--app-muted)]">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- COMO FUNCIONA --}}
    <section id="como-funciona" class="scroll-mt-24 bg-[var(--app-panel)] py-16 md:py-20">
        <div class="app-shell">
            <div class="max-w-2xl">
                <span class="kicker">Cómo funciona</span>
                <h2 class="page-heading mt-3 md:text-5xl">En tres pasos estás jugando</h2>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['1', 'Creamos tu liga', 'Nos contactas y armamos tu liga con su propio administrador y su dirección web.'],
                    ['2', 'Invitas a tu gente', 'Cargas a tus jugadores desde un Excel y reciben la invitación por correo para activar su cuenta.'],
                    ['3', 'A pronosticar', 'Cada uno carga sus marcadores, tú subes los resultados y el ranking se actualiza solo.'],
                ] as [$n, $title, $copy])
                    <article class="stat-tile" data-mark="{{ $n }}">
                        <span class="relative z-10 grid h-10 w-10 place-items-center rounded-lg bg-[var(--app-primary)] font-display text-base font-black text-white dark:text-[#170f2f]">{{ $n }}</span>
                        <h3 class="relative z-10 mt-4 font-display text-lg font-black text-[var(--app-text)]">{{ $title }}</h3>
                        <p class="relative z-10 mt-2 text-sm font-semibold leading-6 text-[var(--app-muted)]">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- DEMO --}}
    <section id="demo" class="scroll-mt-24 bg-[var(--app-bg)] py-16 md:py-20">
        <div class="app-shell grid gap-10 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
            <div>
                <span class="kicker">Probala ahora</span>
                <h2 class="page-heading mt-3 md:text-5xl">Entrá a la demo sin registrarte</h2>
                <p class="mt-3 text-base font-semibold leading-7 text-[var(--app-muted)]">Una liga de ejemplo con el Mundial 2026 ya cargado: pronósticos, cierre por partido y ranking en vivo. Entrá como jugador y mirá cómo se siente competir antes de armar la tuya.</p>

                <ul class="mt-6 grid gap-3">
                    @foreach ([
                        ['pencil', 'Cargá tus marcadores partido por partido.'],
                        ['trophy', 'Mirá cómo suma el ranking en tiempo real.'],
                        ['clock', 'Probá el cierre 30 minutos antes de cada partido.'],
                    ] as [$icon, $copy])
                        <li class="flex items-center gap-3 text-sm font-semibold text-[var(--app-text)]">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[var(--app-panel-soft)] text-[var(--app-secondary)]">
                                <x-icon :name="$icon" class="h-5 w-5" />
                            </span>
                            {{ $copy }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('liga.login', ['liga' => 'demo']) }}" class="btn btn-primary mt-8">Entrar a la demo</a>
            </div>

            <div class="surface-strong p-6 sm:p-8">
                <span class="kicker">Credenciales de jugador</span>
                <p class="mt-3 text-sm font-semibold leading-6 text-[var(--app-muted)]">Usá estos datos en la pantalla de acceso de la liga demo.</p>

                <div class="mt-5 grid gap-3">
                    @foreach ([
                        ['Usuario', 'jugador'],
                        ['Contraseña', 'Quiniela2026'],
                    ] as [$label, $value])
                        <div class="flex items-center justify-between gap-4 rounded-lg bg-[var(--app-panel-soft)] px-4 py-3">
                            <span class="text-xs font-black uppercase tracking-wide text-[var(--app-muted)]">{{ $label }}</span>
                            <code class="font-display text-base font-black text-[var(--app-text)]">{{ $value }}</code>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('liga.login', ['liga' => 'demo']) }}" class="btn btn-secondary mt-6 w-full">Ir al acceso de la demo</a>
            </div>
        </div>
    </section>

    {{-- CONTACTO --}}
    <section id="contacto" class="scroll-mt-24 bg-[var(--app-panel)] py-16 md:py-20">
        <div class="app-shell grid gap-10 lg:grid-cols-[.9fr_1.1fr] lg:items-center">
            <div>
                <span class="kicker">Contacto</span>
                <h2 class="page-heading mt-3 md:text-5xl">¿Quieres tu liga?</h2>
                <p class="mt-3 text-base font-semibold leading-7 text-[var(--app-muted)]">Déjanos tu mensaje y te ayudamos a armar la quiniela de tu grupo para el Mundial 2026.</p>

                @php
                    $igRaw = config('contact.instagram');
                    $waRaw = config('contact.whatsapp');
                    $igUrl = $igRaw ? (str_starts_with($igRaw, 'http') ? $igRaw : 'https://instagram.com/'.ltrim($igRaw, '@')) : null;
                    $waUrl = $waRaw ? 'https://wa.me/'.preg_replace('/\D+/', '', $waRaw) : null;
                    $contactRow = 'flex items-center gap-3 font-bold text-[var(--app-text)] no-underline';
                    $contactBadge = 'grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-[var(--app-panel-soft)] text-[var(--app-primary)]';
                @endphp
                <div class="mt-6 grid gap-3">
                    <a href="mailto:{{ config('contact.email') }}" class="{{ $contactRow }}">
                        <span class="{{ $contactBadge }}"><x-icon name="envelope" class="h-5 w-5" /></span>
                        {{ config('contact.email') }}
                    </a>
                    @if ($waUrl)
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="{{ $contactRow }}">
                            <span class="{{ $contactBadge }}"><x-social-icon name="whatsapp" class="h-5 w-5" /></span>
                            WhatsApp
                        </a>
                    @endif
                    @if ($igUrl)
                        <a href="{{ $igUrl }}" target="_blank" rel="noopener" class="{{ $contactRow }}">
                            <span class="{{ $contactBadge }}"><x-social-icon name="instagram" class="h-5 w-5" /></span>
                            {{ str_starts_with($igRaw, 'http') ? 'Instagram' : '@'.ltrim($igRaw, '@') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="surface-strong p-6 sm:p-8">
                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 font-bold text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert mb-5 border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('contacto.send') }}" class="grid gap-5 sm:grid-cols-2" data-contact-form>
                    @csrf

                    {{-- Feedback inline para el envio via fetch (sin recargar la pagina). --}}
                    <div data-contact-feedback class="hidden sm:col-span-2" role="status" aria-live="polite"></div>

                    {{-- Honeypot anti-bot: oculto para humanos --}}
                    <div class="hidden" aria-hidden="true">
                        <label>No completar
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </label>
                    </div>

                    <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="name">
                        Nombre
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="rounded-lg px-4 py-3" placeholder="Tu nombre">
                    </label>

                    <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="email">
                        Email
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="rounded-lg px-4 py-3" placeholder="tu@correo.com">
                    </label>

                    <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="phone">
                        Teléfono
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required class="rounded-lg px-4 py-3" placeholder="+58 412 1234567">
                    </label>

                    <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="plan">
                        ¿Qué plan te interesa?
                        <select id="plan" name="plan" required class="rounded-lg py-3 pl-4 pr-11">
                            <option value="" disabled @selected(! old('plan'))>Elige un plan</option>
                            @foreach (config('contact.plans') as $key => $label)
                                <option value="{{ $key }}" @selected(old('plan') === (string) $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)] sm:col-span-2" for="horario">
                        Mejor horario para contactarte
                        <select id="horario" name="horario" required class="rounded-lg py-3 pl-4 pr-11">
                            <option value="" disabled @selected(! old('horario'))>Elige un horario</option>
                            @foreach (config('contact.horarios') as $key => $label)
                                <option value="{{ $key }}" @selected(old('horario') === (string) $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)] sm:col-span-2" for="message">
                        Mensaje
                        <textarea id="message" name="message" rows="4" required class="rounded-lg px-4 py-3" placeholder="Cuéntanos de tu grupo">{{ old('message') }}</textarea>
                    </label>

                    <button type="submit" class="btn btn-primary w-full sm:col-span-2">Enviar mensaje</button>
                </form>
            </div>
        </div>
    </section>
@endsection
