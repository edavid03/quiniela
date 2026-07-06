<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Partido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_matches_only_shows_matches_that_have_not_started_yet(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $local = Equipo::create(['id' => 1, 'name' => 'Local FC', 'code' => 'LOC', 'grupo' => 'A']);
        $visitante = Equipo::create(['id' => 2, 'name' => 'Visitante FC', 'code' => 'VIS', 'grupo' => 'A']);

        $partidoPasado = Partido::create([
            'local_id' => $local->id,
            'visitante_id' => $visitante->id,
            'fecha_utc' => now()->utc()->subDays(2)->format('Y-m-d H:i:s'),
            'estadio' => 'Estadio Viejo',
            'fase' => 'Grupos',
            'goles_local' => 1,
            'goles_visitante' => 0,
        ]);

        $partidoFuturo = Partido::create([
            'local_id' => $visitante->id,
            'visitante_id' => $local->id,
            'fecha_utc' => now()->utc()->addDays(2)->format('Y-m-d H:i:s'),
            'estadio' => 'Estadio Futuro',
            'fase' => 'Grupos',
            'goles_local' => null,
            'goles_visitante' => null,
        ]);

        $response = $this->actingAs($user)
            ->get(route('liga.dashboard', $liga))
            ->assertOk();

        $nextMatches = $response->viewData('nextMatches');

        $this->assertTrue($nextMatches->contains('id', $partidoFuturo->id));
        $this->assertFalse($nextMatches->contains('id', $partidoPasado->id));
    }
}
