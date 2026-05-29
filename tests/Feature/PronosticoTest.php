<?php

namespace Tests\Feature;

use App\Models\Prediccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PronosticoTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_create_pronosticos(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido();

        $this->actingAs($user)
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $partido->id => [
                        'goles_local' => 2,
                        'goles_visitante' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('liga.pronosticos.edit', $liga));

        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $user->id,
            'partido_id' => $partido->id,
            'liga_id' => $liga->id,
            'goles_local' => 2,
            'goles_visitante' => 1,
        ]);
    }

    public function test_users_can_update_existing_pronosticos(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido();

        Prediccion::create([
            'liga_id' => $liga->id,
            'usuario_id' => $user->id,
            'partido_id' => $partido->id,
            'goles_local' => 0,
            'goles_visitante' => 0,
            'acertado' => false,
            'puntos' => null,
        ]);

        $this->actingAs($user)
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $partido->id => [
                        'goles_local' => 3,
                        'goles_visitante' => 2,
                    ],
                ],
            ])
            ->assertRedirect(route('liga.pronosticos.edit', $liga));

        $this->assertDatabaseCount('predicciones', 1);
        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $user->id,
            'partido_id' => $partido->id,
            'goles_local' => 3,
            'goles_visitante' => 2,
        ]);
    }

    public function test_users_can_save_only_some_pronosticos(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $primerPartido = $this->crearPartido();
        $segundoPartido = $this->crearPartido(3, 4);

        $this->actingAs($user)
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $primerPartido->id => [
                        'goles_local' => 1,
                        'goles_visitante' => 0,
                    ],
                    $segundoPartido->id => [
                        'goles_local' => null,
                        'goles_visitante' => null,
                    ],
                ],
            ])
            ->assertRedirect(route('liga.pronosticos.edit', $liga));

        $this->assertDatabaseCount('predicciones', 1);
        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $user->id,
            'partido_id' => $primerPartido->id,
            'goles_local' => 1,
            'goles_visitante' => 0,
        ]);
        $this->assertDatabaseMissing('predicciones', [
            'usuario_id' => $user->id,
            'partido_id' => $segundoPartido->id,
        ]);
    }

    public function test_incomplete_pronostico_is_rejected(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido();

        $this->actingAs($user)
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $partido->id => [
                        'goles_local' => 1,
                        'goles_visitante' => null,
                    ],
                ],
            ])
            ->assertSessionHasErrors('predicciones')
            ->assertSessionHas('security_alert', 'Intentaste guardar un pronostico incompleto.');

        $this->assertDatabaseCount('predicciones', 0);
    }

    public function test_incomplete_pronostico_shows_only_one_visible_alert(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido();

        $this->actingAs($user)
            ->from(route('liga.pronosticos.edit', $liga))
            ->followingRedirects()
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $partido->id => [
                        'goles_local' => 1,
                        'goles_visitante' => null,
                    ],
                ],
            ])
            ->assertOk()
            ->assertSee('Intentaste guardar un pronostico incompleto.')
            ->assertDontSee('Cada pronostico debe tener goles de ambos equipos.');
    }

    public function test_pronosticos_after_deadline_are_rejected_with_security_alert(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido(1, 2, now()->utc()->addDays(6)->format('Y-m-d H:i:s'));

        $this->actingAs($user)
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $partido->id => [
                        'goles_local' => 1,
                        'goles_visitante' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors('predicciones')
            ->assertSessionHas('security_alert', 'El plazo para registrar apuestas ha cerrado.');

        $this->assertDatabaseCount('predicciones', 0);
    }

    public function test_liga_admin_cannot_view_pronosticos_form(): void
    {
        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);

        $this->actingAs($admin)
            ->get(route('liga.pronosticos.edit', $liga))
            ->assertRedirect(route('liga.dashboard', $liga))
            ->assertSessionHas('security_alert');
    }

    public function test_liga_admin_cannot_submit_pronosticos(): void
    {
        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);
        $partido = $this->crearPartido();

        $this->actingAs($admin)
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $partido->id => [
                        'goles_local' => 2,
                        'goles_visitante' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('liga.dashboard', $liga))
            ->assertSessionHas('security_alert');

        $this->assertDatabaseCount('predicciones', 0);
    }

    public function test_users_can_view_pronosticos_form_with_existing_values(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido();

        Prediccion::create([
            'liga_id' => $liga->id,
            'usuario_id' => $user->id,
            'partido_id' => $partido->id,
            'goles_local' => 1,
            'goles_visitante' => 1,
            'acertado' => false,
            'puntos' => null,
        ]);

        $this->actingAs($user)
            ->get(route('liga.pronosticos.edit', $liga))
            ->assertOk()
            ->assertSee('Mis pron&oacute;sticos', false)
            ->assertSee('value="1"', false);
    }
}
