<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_landing_at_root(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Quiero mi liga')
            ->assertSee('Ligas privadas');
    }

    public function test_authenticated_superadmin_is_redirected_to_ligas(): void
    {
        $su = $this->superAdmin();

        $this->actingAs($su)
            ->get('/')
            ->assertRedirect(route('superadmin.ligas.index'));
    }

    public function test_authenticated_liga_user_is_redirected_to_dashboard(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('liga.dashboard', $liga));
    }

    public function test_bare_superadmin_redirects_guests_to_login(): void
    {
        $this->get('/superadmin')->assertRedirect(route('superadmin.login'));
    }

    public function test_bare_superadmin_redirects_logged_in_superadmin_to_ligas(): void
    {
        $su = $this->superAdmin();

        $this->actingAs($su)
            ->get('/superadmin')
            ->assertRedirect(route('superadmin.ligas.index'));
    }

    public function test_contact_form_queues_a_mail(): void
    {
        Mail::fake();

        $this->from('/')
            ->post(route('contacto.send'), [
                'name' => 'Ana',
                'email' => 'ana@correo.test',
                'message' => 'Quiero armar mi liga.',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('status');

        Mail::assertQueued(ContactMail::class);
    }

    public function test_contact_form_validates_input(): void
    {
        Mail::fake();

        $this->from('/')
            ->post(route('contacto.send'), [
                'name' => '',
                'email' => 'no-es-un-email',
                'message' => '',
            ])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        Mail::assertNothingQueued();
    }

    public function test_honeypot_silently_drops_bots(): void
    {
        Mail::fake();

        $this->from('/')
            ->post(route('contacto.send'), [
                'name' => 'Bot',
                'email' => 'bot@spam.test',
                'message' => 'spam',
                'website' => 'http://spam.test',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('status');

        Mail::assertNothingQueued();
    }
}
