<?php

namespace Tests\Unit;

use App\Models\Liga;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LigaPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_ligas_default_to_plan_e_at_database_level(): void
    {
        $liga = Liga::create([
            'name' => 'Liga Existente',
            'slug' => 'liga-existente',
            'is_active' => true,
        ]);

        $this->assertSame(Plan::PLAN_E, $liga->refresh()->plan_id);
    }

    public function test_plan_e_has_no_user_limit(): void
    {
        $liga = $this->createLiga(['plan_id' => Plan::PLAN_E]);
        $this->ligaAdmin($liga);

        $this->assertNull($liga->limiteUsuarios());
        $this->assertNull($liga->usuariosDisponibles());
        $this->assertTrue($liga->permiteAgregarUsuarios(500));
    }

    public function test_liga_user_limit_counts_admin(): void
    {
        $liga = $this->createLiga(['plan_id' => Plan::PLAN_A]);
        $this->ligaAdmin($liga);
        $this->ligaUser($liga);

        $this->assertSame(5, $liga->limiteUsuarios());
        $this->assertSame(2, $liga->usuariosActuales());
        $this->assertSame(3, $liga->usuariosDisponibles());
        $this->assertTrue($liga->permiteAgregarUsuarios(3));
        $this->assertFalse($liga->permiteAgregarUsuarios(4));
    }
}
