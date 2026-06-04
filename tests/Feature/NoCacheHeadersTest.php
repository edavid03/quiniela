<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoCacheHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_liga_pages_send_no_store(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $response = $this->actingAs($user)->get(route('liga.dashboard', $liga))->assertOk();

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_superadmin_pages_send_no_store(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->get(route('superadmin.ligas.index'))->assertOk();

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_login_password_field_has_visibility_toggle(): void
    {
        $liga = $this->createLiga();

        $this->get(route('liga.login', $liga))
            ->assertOk()
            ->assertSee('data-password-toggle', false)
            ->assertSee('name="password"', false);
    }
}
