# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A World Cup 2026 prediction pool ("quiniela"). Users log in, submit score predictions (`pronósticos`) for matches, and earn points once an admin enters the real result. A ranking table aggregates points per user. Laravel 12, PHP 8.2+, server-rendered Blade views, Tailwind CSS v4 via Vite. No SPA, no API — plain web routes + Blade.

The domain language is **Spanish** (models, columns, routes, UI). Keep it that way: `Partido` (match), `Equipo` (team), `Prediccion` (prediction), `pronostico` (the user's bet), `goles_local`/`goles_visitante`, `fase` (stage).

## Commands

```bash
composer dev        # runs server + queue listener + vite concurrently (main dev loop)
composer test       # clears config cache, then runs the PHPUnit suite
composer setup      # first-time: install, .env, key, migrate, npm install, build
composer db:up      # docker compose up for local services

php artisan test --filter=SomeTest        # single test class/method
php artisan migrate:fresh --seed          # rebuild DB + seed teams/matches + admin user
./vendor/bin/pint                         # format (Laravel Pint) — the only linter here
npm run dev / npm run build               # vite (assets)
```

Tests run against **sqlite `:memory:`** (see `phpunit.xml`), independent of the dev DB. Default dev `DB_CONNECTION` is sqlite; queue default is `database`.

## Architecture & domain rules

The scoring and deadline logic lives in the **models**, not the controllers — controllers only validate and delegate. Read these two before touching anything prediction-related:

- **`Partido::fechaCierrePronosticosUtc()` / `Partido::admitePronosticos()`** — each match has its own betting deadline: `fecha_utc - 30 minutes`. A prediction is blocked when current UTC time is greater than or equal to that deadline.
- **`Prediccion::registrarApuesta()`** — the only sanctioned way to create/update a bet. It re-checks the deadline server-side, returns a `string` error message on failure or the model on success (callers do `is_string($resultado)` to detect errors). It uses `updateOrCreate` keyed on `(partido_id, usuario_id)`, so a user has at most one prediction per match. Saving resets `puntos`/`acertado`.
- **`Partido::finalizarPartido()`** → **`Prediccion::evaluarResultado()`** — the scoring engine. When an admin enters a result, the match computes the real "signo" (1 = local win, 2 = away win, 0 = draw) and each prediction is scored: **3 points for exact score, 1 point for correct signo, 0 otherwise**. `acertado` = exact-score hit.

`RankingController` does the leaderboard as a single grouped SQL aggregate (`SUM(puntos)`, exact-hit count, evaluated count) ordered by points → exactos → evaluados → name. Don't replace this with N+1 Eloquent loops.

Both `Partido` and `Prediccion` set `public $timestamps = false` and have explicit `$fillable` including `id` — keep that when adding columns.

## Routing & auth

All routes are in `routes/web.php`. Three access tiers via middleware groups:
- `guest` → login
- `auth` → dashboard, rankings, resultados, pronosticos (the player area)
- `auth` + `admin` → `/admin/resultados` (entering match results)

Auth is **username + password** (not email) — see `AuthController::login` and the `add_username_to_users_table` migration. The `admin` middleware alias maps to `EnsureUserIsAdmin` (registered in `bootstrap/app.php`) and checks `User->is_admin`.

The `/dashboard` route closure builds its own view data inline (counts + next 5 matches + deadline) rather than using a controller — that's intentional, mirror the existing pattern if extending it.

## Conventions specific to this repo

- Controllers return Blade `View`/`RedirectResponse`; flash messages use `status` for success and **`security_alert`** for blocked/suspicious actions (invalid match id, incomplete bet, closed deadline). The bulk update controllers (`PronosticoController`, `AdminPartidoResultadoController`) validate the FULL submitted array, reject if any referenced `partido` id doesn't exist, and skip rows where both goal fields are null. Preserve this all-or-nothing validation shape.
- Seed data (teams + 2026 fixtures) comes from `Mundial2026Seeder` (DESTRUCTIVE — deletes `partidos`/`equipos`, never run it on a populated prod DB). Demo accounts live in the idempotent `DemoSeeder`: liga demo + `admin` / `Quiniela2026` (`role` = liga_admin), `jugador` / `Quiniela2026`, and `superadmin` (password from `SUPERADMIN_PASSWORD` env, dev fallback only). `DatabaseSeeder` just calls both. On deploy, `RUN_SEED=true` runs ONLY `DemoSeeder` (see `docker/php/entrypoint.sh`).
- Times are stored/compared in **UTC** (`fecha_utc`, `now()->utc()`).

## Project-wide rules (from global config)

- **Conventional commits only. Never add Co-Authored-By / AI attribution.**
- **Never run a build after changes** unless asked.
- Format with Pint; there is no PHPStan/Larastan or JS linter configured.

## Deployment

Dockerized (PHP-FPM + nginx). See `Dockerfile`, `docker-compose.yml`, the Dokploy compose files, and `DEPLOY_DOCKER.md`. The container entrypoint is `docker/php/entrypoint.sh`; nginx vhost is `docker/nginx/default.conf`.
