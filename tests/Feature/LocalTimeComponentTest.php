<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LocalTimeComponentTest extends TestCase
{
    public function test_renders_match_time_in_caracas_timezone(): void
    {
        // 19:00 UTC -> 15:00 en America/Caracas (UTC-4, sin horario de verano).
        $html = Blade::render('<x-local-time :date="$date" />', [
            'date' => '2026-06-11 19:00:00',
        ]);

        $this->assertStringContainsString('11/06/2026 15:00', $html);
        $this->assertStringContainsString('VET', $html);
    }

    public function test_does_not_emit_browser_local_time_hook(): void
    {
        $html = Blade::render('<x-local-time :date="$date" />', [
            'date' => '2026-06-11 19:00:00',
        ]);

        // Ya no depende del navegador: el JS renderLocalTimes() no debe actuar aqui.
        $this->assertStringNotContainsString('data-local-time', $html);
    }
}
