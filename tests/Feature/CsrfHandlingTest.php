<?php

namespace Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class CsrfHandlingTest extends TestCase
{
    public function test_csrf_mismatch_redirects_instead_of_showing_419_page(): void
    {
        $this->startSession();

        $request = Request::create('/demo/login', 'POST');
        $request->setLaravelSession($this->app['session']->driver());

        $response = app(ExceptionHandler::class)
            ->render($request, new TokenMismatchException);

        // En vez de la pantalla cruda 419, redirige (back) con un mensaje amable.
        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_csrf_mismatch_returns_json_419_for_ajax(): void
    {
        $request = Request::create('/demo/admin/import/preview', 'POST', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = app(ExceptionHandler::class)
            ->render($request, new TokenMismatchException);

        $this->assertSame(419, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
