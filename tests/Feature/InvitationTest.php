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
            ->assertSee('Activá tu cuenta');

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
