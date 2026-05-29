<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $liga = $this->createLiga();

        $this->get(route('liga.login', $liga))
            ->assertOk()
            ->assertSee('Usuario');
    }

    public function test_users_can_login_with_username_and_password(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, [
            'username' => 'usuario_prueba',
            'password' => Hash::make('clave-segura'),
        ]);

        $this->post(route('liga.login.store', $liga), [
            'username' => 'usuario_prueba',
            'password' => 'clave-segura',
        ])->assertRedirect(route('liga.dashboard', $liga));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_users_can_view_dashboard(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->get(route('liga.dashboard', $liga))
            ->assertOk()
            ->assertSee('Mesa de la quiniela');
    }

    public function test_liga_admin_users_see_admin_link(): void
    {
        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);

        $this->actingAs($admin)
            ->get(route('liga.dashboard', $liga))
            ->assertOk()
            ->assertSee('Administrar liga');
    }

    public function test_users_cannot_login_with_invalid_password(): void
    {
        $liga = $this->createLiga();
        $this->ligaUser($liga, [
            'username' => 'usuario_prueba',
            'password' => Hash::make('clave-segura'),
        ]);

        $this->post(route('liga.login.store', $liga), [
            'username' => 'usuario_prueba',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->post(route('liga.logout', $liga))
            ->assertRedirect(route('liga.login', $liga));

        $this->assertGuest();
    }
}
