<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_username_across_ligas_logs_in_independently(): void
    {
        $ligaA = $this->createLiga(['slug' => 'liga-a']);
        $ligaB = $this->createLiga(['slug' => 'liga-b']);

        $juanA = $this->ligaUser($ligaA, ['username' => 'juan', 'password' => Hash::make('claveA')]);
        $this->ligaUser($ligaB, ['username' => 'juan', 'password' => Hash::make('claveB')]);

        $this->post(route('liga.login.store', $ligaA), [
            'username' => 'juan',
            'password' => 'claveA',
        ])->assertRedirect(route('liga.dashboard', $ligaA));

        $this->assertAuthenticatedAs($juanA);
    }

    public function test_user_cannot_access_another_liga(): void
    {
        $ligaA = $this->createLiga(['slug' => 'liga-a']);
        $ligaB = $this->createLiga(['slug' => 'liga-b']);
        $userA = $this->ligaUser($ligaA);

        $this->actingAs($userA)
            ->get(route('liga.dashboard', $ligaB))
            ->assertForbidden();
    }

    public function test_inactive_liga_is_forbidden(): void
    {
        $liga = $this->createLiga(['slug' => 'liga-off', 'is_active' => false]);

        $this->get(route('liga.login', $liga))->assertForbidden();
    }

    public function test_unknown_slug_returns_not_found(): void
    {
        $this->get('/no-existe/login')->assertNotFound();
    }

    public function test_bare_liga_slug_redirects_guests_to_liga_login(): void
    {
        $liga = $this->createLiga(['slug' => 'demo']);

        $this->get('/demo')->assertRedirect(route('liga.login', $liga));
    }

    public function test_bare_liga_slug_redirects_members_to_dashboard(): void
    {
        $liga = $this->createLiga(['slug' => 'demo']);
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->get('/demo')
            ->assertRedirect(route('liga.dashboard', $liga));
    }
}
