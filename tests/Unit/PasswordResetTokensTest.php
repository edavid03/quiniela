<?php

namespace Tests\Unit;

use App\Support\PasswordResetTokens;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasswordResetTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_is_stored_hashed_and_validates_with_raw_token(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['username' => 'ana']);
        $tokens = app(PasswordResetTokens::class);

        [, $raw] = $tokens->createForUsername($liga, 'ana');

        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        $this->assertNotNull($record);
        $this->assertNotSame($raw, $record->token);
        $this->assertSame(hash('sha256', $raw), $record->token);
        $this->assertTrue($tokens->findValidUser($liga, 'ana', $raw)->is($user));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $liga = $this->createLiga();
        $this->ligaUser($liga, ['username' => 'ana']);
        $tokens = app(PasswordResetTokens::class);

        $tokens->createForUsername($liga, 'ana');

        $this->assertNull($tokens->findValidUser($liga, 'ana', 'incorrecto'));
    }

    public function test_expired_token_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00', 'UTC'));

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['username' => 'ana']);
        $tokens = app(PasswordResetTokens::class);

        [, $raw] = $tokens->createForUsername($liga, 'ana');

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(61)]);

        $this->assertNull($tokens->findValidUser($liga, 'ana', $raw));
    }

    public function test_token_is_scoped_to_liga(): void
    {
        $ligaA = $this->createLiga(['name' => 'Liga A', 'slug' => 'liga-a']);
        $ligaB = $this->createLiga(['name' => 'Liga B', 'slug' => 'liga-b']);
        $this->ligaUser($ligaA, ['username' => 'ana']);
        $this->ligaUser($ligaB, ['username' => 'bruno']);
        $tokens = app(PasswordResetTokens::class);

        [, $raw] = $tokens->createForUsername($ligaA, 'ana');

        $this->assertNull($tokens->findValidUser($ligaB, 'ana', $raw));
    }
}
