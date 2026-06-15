<?php

namespace Tests\Feature;

use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PerLigaSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_cookie_is_named_and_pathed_per_slug(): void
    {
        $liga = $this->createLiga(['slug' => 'liga-a']);

        $response = $this->get(route('liga.login', $liga));

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === 'q_session_liga-a');

        $this->assertNotNull($cookie, 'Debe existir la cookie de sesión scopeada al slug.');
        $this->assertSame('/liga-a', $cookie->getPath());
    }

    public function test_superadmin_keeps_default_session_cookie(): void
    {
        $response = $this->get(route('superadmin.login'));

        $scoped = collect($response->headers->getCookies())
            ->contains(fn ($c) => str_starts_with($c->getName(), 'q_session_'));

        $this->assertFalse($scoped, 'El área superadmin no debe scopear la cookie por slug.');
    }

    public function test_remember_cookie_is_scoped_to_liga_path(): void
    {
        $liga = $this->createLiga(['slug' => 'liga-a']);
        $this->ligaUser($liga, ['username' => 'ana', 'password' => Hash::make('claveA')]);

        $response = $this->post(route('liga.login.store', $liga), [
            'username' => 'ana',
            'password' => 'claveA',
            'remember' => '1',
        ]);

        $remember = collect($response->headers->getCookies())
            ->first(fn ($c) => str_starts_with($c->getName(), 'remember_web'));

        $this->assertNotNull($remember, 'El login con remember debe emitir la cookie recaller.');
        $this->assertSame('/liga-a', $remember->getPath());
    }

    public function test_two_ligas_stay_authenticated_in_the_same_browser(): void
    {
        // El driver array no persiste entre requests; forzamos database para
        // poder reabrir cada sesión con su cookie.
        config(['session.driver' => 'database']);

        $ligaA = $this->createLiga(['slug' => 'liga-a']);
        $ligaB = $this->createLiga(['slug' => 'liga-b']);
        $userA = $this->ligaUser($ligaA, ['username' => 'ana', 'password' => Hash::make('claveA')]);
        $userB = $this->ligaUser($ligaB, ['username' => 'ben', 'password' => Hash::make('claveB')]);

        // En tests el SessionManager cachea el store entre requests del mismo
        // proceso (su nombre/atributos quedan fijados en el primer build). En
        // php-fpm cada request es un proceso nuevo y limpio; lo simulamos
        // olvidando el driver de sesión (se reconstruye con el cookie actual) y
        // los guards entre cada "pestaña".
        $freshBrowser = function (): void {
            $this->app['session']->forgetDrivers();
            $this->app->forgetInstance('session.store');
            $this->app['auth']->forgetGuards();
            Tenancy::forget();
        };

        // Login en liga A -> guardamos su cookie de sesión (cruda/encriptada).
        $loginA = $this->post(route('liga.login.store', $ligaA), [
            'username' => 'ana',
            'password' => 'claveA',
        ])->assertRedirect(route('liga.dashboard', $ligaA));
        $cookieA = $loginA->getCookie('q_session_liga-a', false)->getValue();
        $freshBrowser();

        // "Otra pestaña" sin la cookie de A: login en liga B.
        $loginB = $this->post(route('liga.login.store', $ligaB), [
            'username' => 'ben',
            'password' => 'claveB',
        ])->assertRedirect(route('liga.dashboard', $ligaB));
        $cookieB = $loginB->getCookie('q_session_liga-b', false)->getValue();
        $freshBrowser();

        // Presentando SOLO la cookie de A, liga A sigue siendo ana.
        $this->call('GET', route('liga.dashboard', $ligaA), [], ['q_session_liga-a' => $cookieA])
            ->assertOk();
        $this->assertAuthenticatedAs($userA);
        $freshBrowser();

        // Presentando SOLO la cookie de B, liga B sigue siendo ben.
        $this->call('GET', route('liga.dashboard', $ligaB), [], ['q_session_liga-b' => $cookieB])
            ->assertOk();
        $this->assertAuthenticatedAs($userB);
    }
}
