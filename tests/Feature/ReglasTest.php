<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReglasTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_rules_and_tiebreakers(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->get(route('liga.reglas.index', $liga))
            ->assertOk()
            ->assertSee('Reglas y desempates')
            ->assertSee('Marcador exacto')
            ->assertSee('Metricas de desempate')
            ->assertSee('Software de gestion, no plataforma de apuestas');
    }
}
