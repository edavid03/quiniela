<?php

namespace App\Support;

use App\Models\Liga;
use App\Models\Scopes\LigaScope;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetTokens
{
    /**
     * @return array{0: User, 1: string}|null
     */
    public function createForUsername(Liga $liga, string $username): ?array
    {
        $user = $this->findUser($liga, $username);

        if ($user === null) {
            return null;
        }

        $raw = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $this->hash($raw),
                'created_at' => now(),
            ],
        );

        return [$user, $raw];
    }

    public function findValidUser(Liga $liga, string $username, string $token): ?User
    {
        $user = $this->findUser($liga, $username);

        if ($user === null) {
            return null;
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        if ($record === null || ! hash_equals((string) $record->token, $this->hash($token))) {
            return null;
        }

        if ($this->isExpired($record->created_at)) {
            return null;
        }

        return $user;
    }

    public function deleteFor(User $user): void
    {
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();
    }

    public function isExpired(null|string|CarbonInterface $createdAt): bool
    {
        if ($createdAt === null) {
            return true;
        }

        $expiresAt = Carbon::parse($createdAt)
            ->addMinutes((int) config('auth.passwords.users.expire', 60));

        return $expiresAt->isPast();
    }

    private function findUser(Liga $liga, string $username): ?User
    {
        return User::withoutGlobalScope(LigaScope::class)
            ->where('liga_id', $liga->id)
            ->where('username', trim($username))
            ->where('role', '!=', User::ROLE_SUPERADMIN)
            ->first();
    }

    private function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
