<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Plan;
use App\Models\Prediccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminLigaTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_login_with_email(): void
    {
        $su = $this->superAdmin([
            'email' => 'su@quiniela.test',
            'password' => Hash::make('secret123'),
        ]);

        $this->post(route('superadmin.login.store'), [
            'email' => 'su@quiniela.test',
            'password' => 'secret123',
        ])->assertRedirect(route('superadmin.ligas.index'));

        $this->assertAuthenticatedAs($su);
    }

    public function test_superadmin_can_create_liga_with_admin(): void
    {
        $su = $this->superAdmin();

        $this->actingAs($su)
            ->post(route('superadmin.ligas.store'), [
                'name' => 'Liga ADN',
                'slug' => 'liga-adn',
                'admin_name' => 'Admin ADN',
                'admin_username' => 'admin',
                'admin_email' => 'admin@adn.test',
                'admin_password' => 'password123',
                'plan_id' => Plan::PLAN_B,
            ])
            ->assertRedirect(route('superadmin.ligas.index'));

        $this->assertDatabaseHas('ligas', [
            'slug' => 'liga-adn',
            'plan_id' => Plan::PLAN_B,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@adn.test',
            'role' => 'liga_admin',
        ]);
    }

    public function test_superadmin_cannot_change_liga_to_plan_below_current_users(): void
    {
        $su = $this->superAdmin();
        $liga = $this->createLiga(['plan_id' => Plan::PLAN_E]);
        $this->ligaAdmin($liga);

        foreach (range(1, 5) as $i) {
            $this->ligaUser($liga, [
                'email' => "user{$i}@correo.test",
                'username' => "user{$i}",
            ]);
        }

        $this->actingAs($su)
            ->put(route('superadmin.ligas.update', $liga), [
                'name' => $liga->name,
                'slug' => $liga->slug,
                'is_active' => '1',
                'plan_id' => Plan::PLAN_A,
            ])
            ->assertSessionHasErrors('plan_id');

        $this->assertSame(Plan::PLAN_E, $liga->refresh()->plan_id);
    }

    public function test_reserved_slug_is_rejected(): void
    {
        $su = $this->superAdmin();

        $this->actingAs($su)
            ->post(route('superadmin.ligas.store'), [
                'name' => 'Intrusa',
                'slug' => 'superadmin',
                'admin_name' => 'X',
                'admin_username' => 'x',
                'admin_email' => 'x@x.test',
                'admin_password' => 'password123',
            ])
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseMissing('ligas', ['slug' => 'superadmin']);
    }

    public function test_deleting_liga_cascades_users_predictions_invitations(): void
    {
        $su = $this->superAdmin();
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $partido = $this->crearPartido();

        Prediccion::create([
            'liga_id' => $liga->id,
            'usuario_id' => $user->id,
            'partido_id' => $partido->id,
            'goles_local' => 1,
            'goles_visitante' => 0,
            'acertado' => false,
            'puntos' => null,
        ]);
        Invitation::generate($user);

        $this->actingAs($su)
            ->delete(route('superadmin.ligas.destroy', $liga))
            ->assertRedirect(route('superadmin.ligas.index'));

        $this->assertDatabaseMissing('ligas', ['id' => $liga->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseCount('predicciones', 0);
        $this->assertDatabaseCount('invitations', 0);
    }
}
