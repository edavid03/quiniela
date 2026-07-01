<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Partido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaseAgrupadaTest extends TestCase
{
    use RefreshDatabase;

    public function test_upcoming_results_appear_in_chronological_order(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $local = Equipo::create(['id' => 1, 'name' => 'Local FC', 'code' => 'LOC', 'grupo' => 'A']);
        $visitante = Equipo::create(['id' => 2, 'name' => 'Visitante FC', 'code' => 'VIS', 'grupo' => 'A']);

        // El partido con estadio E1 es cronologicamente anterior al de E2.
        Partido::create([
            'local_id' => $local->id, 'visitante_id' => $visitante->id,
            'fecha_utc' => now()->utc()->addDays(1)->format('Y-m-d H:i:s'),
            'estadio' => 'E1', 'fase' => 'Fase de grupos', 'goles_local' => null, 'goles_visitante' => null,
        ]);
        Partido::create([
            'local_id' => $local->id, 'visitante_id' => $visitante->id,
            'fecha_utc' => now()->utc()->addDays(20)->format('Y-m-d H:i:s'),
            'estadio' => 'E2', 'fase' => 'Octavos de final', 'goles_local' => null, 'goles_visitante' => null,
        ]);

        $this->actingAs($user)
            ->get(route('liga.resultados.index', $liga))
            ->assertOk()
            ->assertSeeInOrder(['E1', 'E2']);
    }
}
