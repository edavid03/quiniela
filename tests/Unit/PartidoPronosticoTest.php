<?php

namespace Tests\Unit;

use App\Models\Partido;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartidoPronosticoTest extends TestCase
{
    use RefreshDatabase;

    public function test_fecha_cierre_pronosticos_is_thirty_minutes_before_fecha_utc(): void
    {
        $partido = $this->crearPartido(1, 2, '2026-06-11 19:00:00');

        $this->assertTrue(
            Carbon::parse('2026-06-11 18:30:00', 'UTC')->equalTo($partido->fechaCierrePronosticosUtc())
        );
    }

    public function test_partido_admite_pronosticos_only_before_the_thirty_minute_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 18:29:59', 'UTC'));
        $partido = $this->crearPartido(1, 2, '2026-06-11 19:00:00');

        $this->assertTrue($partido->admitePronosticos());

        Carbon::setTestNow(Carbon::parse('2026-06-11 18:30:00', 'UTC'));
        $this->assertFalse($partido->admitePronosticos());

        Carbon::setTestNow(Carbon::parse('2026-06-11 18:31:00', 'UTC'));
        $this->assertFalse($partido->admitePronosticos());
    }

    public function test_con_pronosticos_abiertos_scope_returns_only_open_partidos(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 14:00:00', 'UTC'));

        $partidoAbierto = $this->crearPartido(1, 2, now()->utc()->addMinutes(31)->format('Y-m-d H:i:s'));
        $this->crearPartido(3, 4, now()->utc()->addMinutes(30)->format('Y-m-d H:i:s'));
        $this->crearPartido(5, 6, now()->utc()->subMinute()->format('Y-m-d H:i:s'));

        $partidos = Partido::query()->conPronosticosAbiertos()->pluck('id');

        $this->assertTrue($partidos->contains($partidoAbierto->id));
        $this->assertCount(1, $partidos);
    }

    public function test_proximo_cierre_pronosticos_returns_earliest_open_match_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 14:00:00', 'UTC'));

        $this->crearPartido(1, 2, now()->utc()->addMinutes(30)->format('Y-m-d H:i:s'));
        $this->crearPartido(3, 4, now()->utc()->addMinutes(90)->format('Y-m-d H:i:s'));
        $this->crearPartido(5, 6, now()->utc()->addMinutes(60)->format('Y-m-d H:i:s'));

        $this->assertTrue(
            Carbon::parse('2026-06-11 14:30:00', 'UTC')->equalTo(Partido::proximoCierrePronosticosUtc())
        );
    }
}
