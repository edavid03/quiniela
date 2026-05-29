<?php

namespace Tests\Feature;

use App\Models\Prediccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_rankings(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['name' => 'Ana', 'username' => 'ana']);

        $this->actingAs($user)
            ->get(route('liga.rankings.index', $liga))
            ->assertOk()
            ->assertSee('Ranking')
            ->assertSee('Ana');
    }

    public function test_dashboard_links_to_rankings(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->get(route('liga.dashboard', $liga))
            ->assertOk()
            ->assertSee('Ver ranking');
    }

    public function test_rankings_are_ordered_by_points(): void
    {
        $liga = $this->createLiga();
        $partido = $this->crearPartido();
        $ana = $this->ligaUser($liga, ['name' => 'Ana', 'username' => 'ana']);
        $bruno = $this->ligaUser($liga, ['name' => 'Bruno', 'username' => 'bruno']);

        Prediccion::create([
            'liga_id' => $liga->id,
            'usuario_id' => $ana->id,
            'partido_id' => $partido->id,
            'goles_local' => 1,
            'goles_visitante' => 0,
            'acertado' => false,
            'puntos' => 1,
        ]);

        Prediccion::create([
            'liga_id' => $liga->id,
            'usuario_id' => $bruno->id,
            'partido_id' => $partido->id,
            'goles_local' => 2,
            'goles_visitante' => 0,
            'acertado' => true,
            'puntos' => 3,
        ]);

        $this->actingAs($ana)
            ->get(route('liga.rankings.index', $liga))
            ->assertOk()
            ->assertSeeInOrder(['Bruno', 'Ana']);
    }

    public function test_liga_admin_is_excluded_from_rankings(): void
    {
        $liga = $this->createLiga();
        $partido = $this->crearPartido();
        $ana = $this->ligaUser($liga, ['name' => 'Ana Jugadora', 'username' => 'ana']);
        $organizadora = $this->ligaAdmin($liga, ['name' => 'Zoraida Organizadora', 'username' => 'admin']);

        // El admin tiene puntos en la BD, pero no debe figurar en la tabla.
        Prediccion::create([
            'liga_id' => $liga->id,
            'usuario_id' => $organizadora->id,
            'partido_id' => $partido->id,
            'goles_local' => 2,
            'goles_visitante' => 0,
            'acertado' => true,
            'puntos' => 3,
        ]);

        $this->actingAs($ana)
            ->get(route('liga.rankings.index', $liga))
            ->assertOk()
            ->assertSee('Ana Jugadora')
            ->assertDontSee('Zoraida Organizadora');
    }

    public function test_rankings_are_isolated_per_liga(): void
    {
        $ligaA = $this->createLiga(['name' => 'Liga A', 'slug' => 'liga-a']);
        $ligaB = $this->createLiga(['name' => 'Liga B', 'slug' => 'liga-b']);
        $partido = $this->crearPartido();

        $ana = $this->ligaUser($ligaA, ['name' => 'Ana A', 'username' => 'ana']);
        $beto = $this->ligaUser($ligaB, ['name' => 'Beto B', 'username' => 'beto']);

        Prediccion::create([
            'liga_id' => $ligaB->id,
            'usuario_id' => $beto->id,
            'partido_id' => $partido->id,
            'goles_local' => 2,
            'goles_visitante' => 0,
            'acertado' => true,
            'puntos' => 3,
        ]);

        // El usuario de la liga A no ve a los de la liga B en su ranking.
        $this->actingAs($ana)
            ->get(route('liga.rankings.index', $ligaA))
            ->assertOk()
            ->assertSee('Ana A')
            ->assertDontSee('Beto B');
    }
}
