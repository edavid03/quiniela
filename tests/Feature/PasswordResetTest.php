<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Support\PasswordResetTokens;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_shows_forgot_password_link(): void
    {
        $liga = $this->createLiga();

        $this->get(route('liga.login', $liga))
            ->assertOk()
            ->assertSee('Olvid&eacute; mi contrase&ntilde;a', false)
            ->assertSee(route('liga.password.request', $liga), false);
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $liga = $this->createLiga();

        $this->get(route('liga.password.request', $liga))
            ->assertOk()
            ->assertSee('Recupera tu clave')
            ->assertSee('Usuario');
    }

    public function test_existing_liga_user_receives_password_reset_mail_with_query_token(): void
    {
        Mail::fake();

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, [
            'username' => 'ana',
            'email' => 'ana@example.test',
        ]);

        $this->post(route('liga.password.email', $liga), [
            'username' => 'ana',
        ])
            ->assertSessionHas('status', 'Si la cuenta existe, enviaremos un enlace de recuperacion al correo registrado.');

        Mail::assertQueued(PasswordResetMail::class, function (PasswordResetMail $mail) use ($liga, $user) {
            $query = [];
            parse_str((string) parse_url($mail->url, PHP_URL_QUERY), $query);

            return $mail->hasTo($user->email)
                && $mail->liga->is($liga)
                && $mail->username === 'ana'
                && ($query['username'] ?? null) === 'ana'
                && isset($query['token'])
                && $query['token'] !== '';
        });
    }

    public function test_unknown_username_gets_generic_response_without_mail(): void
    {
        Mail::fake();

        $liga = $this->createLiga();

        $this->post(route('liga.password.email', $liga), [
            'username' => 'no-existe',
        ])
            ->assertSessionHas('status', 'Si la cuenta existe, enviaremos un enlace de recuperacion al correo registrado.');

        Mail::assertNothingQueued();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_valid_token_shows_reset_form(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['username' => 'ana']);

        [, $token] = app(PasswordResetTokens::class)->createForUsername($liga, $user->username);

        $this->get(route('liga.password.reset', [
            'liga' => $liga,
            'username' => $user->username,
            'token' => $token,
        ]))
            ->assertOk()
            ->assertSee('Crea una nueva clave')
            ->assertSee('ana');
    }

    public function test_invalid_token_shows_invalid_page(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['username' => 'ana']);

        app(PasswordResetTokens::class)->createForUsername($liga, $user->username);

        $this->get(route('liga.password.reset', [
            'liga' => $liga,
            'username' => $user->username,
            'token' => 'token-invalido',
        ]))
            ->assertOk()
            ->assertSee('Enlace no v&aacute;lido', false);
    }

    public function test_expired_token_cannot_reset_password(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00', 'UTC'));

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, [
            'username' => 'ana',
            'password' => Hash::make('clave-vieja'),
        ]);

        [, $token] = app(PasswordResetTokens::class)->createForUsername($liga, $user->username);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(61)]);

        $this->post(route('liga.password.update', $liga), [
            'username' => $user->username,
            'token' => $token,
            'password' => 'clave-nueva',
            'password_confirmation' => 'clave-nueva',
        ])
            ->assertRedirect(route('liga.password.request', $liga))
            ->assertSessionHas('security_alert', 'El enlace de recuperacion no es valido o ya expiro.');

        $this->assertTrue(Hash::check('clave-vieja', $user->fresh()->password));
    }

    public function test_token_from_another_liga_is_rejected(): void
    {
        $ligaA = $this->createLiga(['name' => 'Liga A', 'slug' => 'liga-a']);
        $ligaB = $this->createLiga(['name' => 'Liga B', 'slug' => 'liga-b']);
        $userA = $this->ligaUser($ligaA, ['username' => 'ana']);
        $this->ligaUser($ligaB, ['username' => 'bruno']);

        [, $token] = app(PasswordResetTokens::class)->createForUsername($ligaA, $userA->username);

        $this->get(route('liga.password.reset', [
            'liga' => $ligaB,
            'username' => $userA->username,
            'token' => $token,
        ]))
            ->assertOk()
            ->assertSee('Enlace no v&aacute;lido', false);
    }

    public function test_successful_password_reset_changes_password_and_deletes_token(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, [
            'username' => 'ana',
            'password' => Hash::make('clave-vieja'),
        ]);

        [, $token] = app(PasswordResetTokens::class)->createForUsername($liga, $user->username);

        $this->post(route('liga.password.update', $liga), [
            'username' => $user->username,
            'token' => $token,
            'password' => 'clave-nueva',
            'password_confirmation' => 'clave-nueva',
        ])
            ->assertRedirect(route('liga.login', $liga))
            ->assertSessionHas('status', 'Tu contrasena fue actualizada. Ya puedes iniciar sesion.');

        $this->assertTrue(Hash::check('clave-nueva', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->post(route('liga.login.store', $liga), [
            'username' => $user->username,
            'password' => 'clave-nueva',
        ])
            ->assertRedirect(route('liga.dashboard', $liga));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_used_token_cannot_be_reused(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, [
            'username' => 'ana',
            'password' => Hash::make('clave-vieja'),
        ]);

        [, $token] = app(PasswordResetTokens::class)->createForUsername($liga, $user->username);

        $this->post(route('liga.password.update', $liga), [
            'username' => $user->username,
            'token' => $token,
            'password' => 'clave-nueva',
            'password_confirmation' => 'clave-nueva',
        ]);

        $this->post(route('liga.password.update', $liga), [
            'username' => $user->username,
            'token' => $token,
            'password' => 'otra-clave',
            'password_confirmation' => 'otra-clave',
        ])
            ->assertRedirect(route('liga.password.request', $liga))
            ->assertSessionHas('security_alert');

        $this->assertFalse(Hash::check('otra-clave', $user->fresh()->password));
    }
}
