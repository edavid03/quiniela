<?php

namespace Tests\Feature;

use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_accept_invitation_and_set_password(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['username' => 'nuevo'])->forceFill(['email_verified_at' => null]);
        $user->save();

        [, $raw] = Invitation::generate($user);

        $this->get(route('liga.invitation.show', ['liga' => $liga, 'token' => $raw]))
            ->assertOk()
            ->assertSee('Activa tu cuenta');

        $this->post(route('liga.invitation.accept', ['liga' => $liga, 'token' => $raw]), [
            'password' => 'nuevaclave123',
            'password_confirmation' => 'nuevaclave123',
        ])->assertRedirect(route('liga.dashboard', $liga));

        $this->assertAuthenticatedAs($user->fresh());

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('invitations', [
            'user_id' => $user->id,
            'accepted_at' => null,
        ]);
    }

    public function test_set_password_page_shows_the_username(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['username' => 'pedro.gomez'])->forceFill(['email_verified_at' => null]);
        $user->save();

        [, $raw] = Invitation::generate($user);

        $this->get(route('liga.invitation.show', ['liga' => $liga, 'token' => $raw]))
            ->assertOk()
            ->assertSee('Tu usuario para entrar')
            ->assertSee('pedro.gomez');
    }

    public function test_password_validation_message_is_in_spanish(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['username' => 'nuevo'])->forceFill(['email_verified_at' => null]);
        $user->save();

        [, $raw] = Invitation::generate($user);

        $response = $this->post(route('liga.invitation.accept', ['liga' => $liga, 'token' => $raw]), [
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors('password');

        // Ancla el idioma: el mensaje por defecto de Laravel debe salir en espanol.
        $errors = session('errors')->get('password');
        $this->assertStringContainsString('caracteres', implode(' ', $errors));
    }

    public function test_expired_invitation_is_invalid(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        [$invitation, $raw] = Invitation::generate($user);
        $invitation->update(['expires_at' => now()->subDay()]);

        $this->get(route('liga.invitation.show', ['liga' => $liga, 'token' => $raw]))
            ->assertOk()
            ->assertSee('Invitación no válida');
    }

    public function test_used_invitation_is_invalid(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        [$invitation, $raw] = Invitation::generate($user);
        $invitation->update(['accepted_at' => now()]);

        $this->get(route('liga.invitation.show', ['liga' => $liga, 'token' => $raw]))
            ->assertOk()
            ->assertSee('Invitación no válida');
    }
}
