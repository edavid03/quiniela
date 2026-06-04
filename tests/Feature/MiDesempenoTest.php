<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Partido;
use App\Models\Prediccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiDesempenoTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_sees_their_performance_stats(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $local = Equipo::create(['id' => 1, 'name' => 'Local FC', 'code' => 'LOC', 'grupo' => 'A']);
        $visitante = Equipo::create(['id' => 2, 'name' => 'Visitante FC', 'code' => 'VIS', 'grupo' => 'A']);

        $exacto = Partido::create([
            'local_id' => $local->id, 'visitante_id' => $visitante->id,
            'fecha_utc' => now()->utc()->addDay()->format('Y-m-d H:i:s'),
            'estadio' => 'E1', 'fase' => 'Grupos', 'goles_local' => 2, 'goles_visitante' => 1,
        ]);
        $fallado = Partido::create([
            'local_id' => $local->id, 'visitante_id' => $visitante->id,
            'fecha_utc' => now()->utc()->addDays(2)->format('Y-m-d H:i:s'),
            'estadio' => 'E2', 'fase' => 'Grupos', 'goles_local' => 0, 'goles_visitante' => 3,
        ]);

        Prediccion::create([
            'liga_id' => $liga->id, 'usuario_id' => $user->id, 'partido_id' => $exacto->id,
            'goles_local' => 2, 'goles_visitante' => 1, 'acertado' => true, 'puntos' => 3,
        ]);
        Prediccion::create([
            'liga_id' => $liga->id, 'usuario_id' => $user->id, 'partido_id' => $fallado->id,
            'goles_local' => 1, 'goles_visitante' => 0, 'acertado' => false, 'puntos' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('liga.mi-desempeno', $liga))
            ->assertOk()
            ->assertSee('Mi desempeño')
            ->assertSee('Puntos totales')
            ->assertSee('Marcadores exactos')
            ->assertSee('#1')                 // unico jugador -> primero
            ->assertDontSee('Todavía no tenés pronósticos evaluados');
    }

    public function test_shows_empty_state_without_evaluated_predictions(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->get(route('liga.mi-desempeno', $liga))
            ->assertOk()
            ->assertSee('Todavía no tenés pronósticos evaluados');
    }
}
