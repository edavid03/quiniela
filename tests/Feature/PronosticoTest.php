<?php

namespace Tests\Feature;

use App\Models\Prediccion;
use Carbon\Carbon;
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
        Carbon::setTestNow(Carbon::parse('2026-06-11 14:00:00', 'UTC'));

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido(1, 2, now()->utc()->addMinutes(29)->format('Y-m-d H:i:s'));

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
            ->assertSessionHas('security_alert', 'El plazo para registrar este pronostico ha cerrado.');

        $this->assertDatabaseCount('predicciones', 0);
    }

    public function test_pronosticos_exactly_thirty_minutes_before_match_are_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 14:00:00', 'UTC'));

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido(1, 2, now()->utc()->addMinutes(30)->format('Y-m-d H:i:s'));

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
            ->assertSessionHas('security_alert', 'El plazo para registrar este pronostico ha cerrado.');

        $this->assertDatabaseCount('predicciones', 0);
    }

    public function test_pronosticos_more_than_thirty_minutes_before_match_are_allowed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 14:00:00', 'UTC'));

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido(1, 2, now()->utc()->addMinutes(31)->format('Y-m-d H:i:s'));

        $this->actingAs($user)
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $partido->id => [
                        'goles_local' => 1,
                        'goles_visitante' => 0,
                    ],
                ],
            ])
            ->assertRedirect(route('liga.pronosticos.edit', $liga));

        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $user->id,
            'partido_id' => $partido->id,
            'goles_local' => 1,
            'goles_visitante' => 0,
        ]);
    }

    public function test_closed_partidos_are_not_shown_on_pronosticos_form(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 14:00:00', 'UTC'));

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partidoAbierto = $this->crearPartido(1, 2, now()->utc()->addMinutes(31)->format('Y-m-d H:i:s'));
        $partidoCerrado = $this->crearPartido(3, 4, now()->utc()->addMinutes(30)->format('Y-m-d H:i:s'));

        Prediccion::create([
            'liga_id' => $liga->id,
            'usuario_id' => $user->id,
            'partido_id' => $partidoCerrado->id,
            'goles_local' => 9,
            'goles_visitante' => 8,
            'acertado' => false,
            'puntos' => null,
        ]);

        $this->actingAs($user)
            ->get(route('liga.pronosticos.edit', $liga))
            ->assertOk()
            ->assertSee($partidoAbierto->local->name)
            ->assertDontSee($partidoCerrado->local->name)
            ->assertDontSee('value="9"', false)
            ->assertDontSee('Pronostico cerrado');
    }

    public function test_pronosticos_form_has_no_submit_button_when_all_partidos_are_closed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 14:00:00', 'UTC'));

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $this->crearPartido(1, 2, now()->utc()->addMinutes(30)->format('Y-m-d H:i:s'));

        $this->actingAs($user)
            ->get(route('liga.pronosticos.edit', $liga))
            ->assertOk()
            ->assertSee('No hay partidos disponibles para pronosticar.')
            ->assertDontSee('Guardar cambios');
    }

    public function test_manipulated_request_with_closed_partido_does_not_save_any_pronosticos(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 14:00:00', 'UTC'));

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partidoAbierto = $this->crearPartido(1, 2, now()->utc()->addMinutes(31)->format('Y-m-d H:i:s'));
        $partidoCerrado = $this->crearPartido(3, 4, now()->utc()->addMinutes(29)->format('Y-m-d H:i:s'));

        $this->actingAs($user)
            ->post(route('liga.pronosticos.update', $liga), [
                'predicciones' => [
                    $partidoAbierto->id => [
                        'goles_local' => 1,
                        'goles_visitante' => 0,
                    ],
                    $partidoCerrado->id => [
                        'goles_local' => 2,
                        'goles_visitante' => 1,
                    ],
                ],
            ])
            ->assertSessionHasErrors('predicciones')
            ->assertSessionHas('security_alert', 'El plazo para registrar este pronostico ha cerrado.');

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
