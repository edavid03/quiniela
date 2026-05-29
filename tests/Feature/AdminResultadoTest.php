<?php

namespace Tests\Feature;

use App\Models\Prediccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResultadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_liga_admins_can_exist(): void
    {
        $ligaA = $this->createLiga(['slug' => 'liga-a']);
        $ligaB = $this->createLiga(['slug' => 'liga-b']);

        $this->ligaAdmin($ligaA);
        $this->ligaAdmin($ligaB);

        // Ya no rige el admin unico global: cada liga tiene el suyo.
        $this->assertDatabaseCount('users', 2);
    }

    public function test_non_superadmin_cannot_access_superadmin_resultados(): void
    {
        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);

        $this->actingAs($admin)
            ->get(route('superadmin.resultados.edit'))
            ->assertForbidden();
    }

    public function test_superadmin_can_view_resultados(): void
    {
        $superadmin = $this->superAdmin();

        $this->actingAs($superadmin)
            ->get(route('superadmin.resultados.edit'))
            ->assertOk()
            ->assertSee('Resultados de partidos');
    }

    public function test_superadmin_result_scores_predictions_across_all_ligas(): void
    {
        $superadmin = $this->superAdmin();
        $ligaA = $this->createLiga(['slug' => 'liga-a']);
        $ligaB = $this->createLiga(['slug' => 'liga-b']);
        $userA = $this->ligaUser($ligaA);
        $userB = $this->ligaUser($ligaB);
        $partido = $this->crearPartido();

        Prediccion::create([
            'liga_id' => $ligaA->id,
            'usuario_id' => $userA->id,
            'partido_id' => $partido->id,
            'goles_local' => 2,
            'goles_visitante' => 1,
            'acertado' => false,
            'puntos' => null,
        ]);

        Prediccion::create([
            'liga_id' => $ligaB->id,
            'usuario_id' => $userB->id,
            'partido_id' => $partido->id,
            'goles_local' => 3,
            'goles_visitante' => 0,
            'acertado' => false,
            'puntos' => null,
        ]);

        $this->actingAs($superadmin)
            ->post(route('superadmin.resultados.update'), [
                'resultados' => [
                    $partido->id => [
                        'goles_local' => 2,
                        'goles_visitante' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('superadmin.resultados.edit'));

        // Liga A: marcador exacto -> 3 puntos.
        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $userA->id,
            'partido_id' => $partido->id,
            'acertado' => true,
            'puntos' => 3,
        ]);

        // Liga B: solo signo correcto (gana local) -> 1 punto.
        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $userB->id,
            'partido_id' => $partido->id,
            'acertado' => false,
            'puntos' => 1,
        ]);
    }

    public function test_incomplete_superadmin_result_shows_security_alert(): void
    {
        $superadmin = $this->superAdmin();
        $partido = $this->crearPartido();

        $this->actingAs($superadmin)
            ->post(route('superadmin.resultados.update'), [
                'resultados' => [
                    $partido->id => [
                        'goles_local' => 2,
                        'goles_visitante' => null,
                    ],
                ],
            ])
            ->assertSessionHasErrors('resultados')
            ->assertSessionHas('security_alert', 'Intentaste guardar un resultado incompleto.');

        $this->assertDatabaseHas('partidos', [
            'id' => $partido->id,
            'goles_local' => null,
            'goles_visitante' => null,
        ]);
    }
}
