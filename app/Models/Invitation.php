<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasUuids;

    protected $fillable = [
        'liga_id',
        'user_id',
        'email',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function liga(): BelongsTo
    {
        return $this->belongsTo(Liga::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function hashToken(string $raw): string
    {
        return hash('sha256', $raw);
    }

    /**
     * Crea una invitacion y devuelve [Invitation, tokenCrudo]. Solo se persiste
     * el hash del token; el crudo viaja por mail y no se vuelve a poder leer.
     *
     * @return array{0: self, 1: string}
     */
    public static function generate(User $user, int $days = 7): array
    {
        $raw = Str::random(64);

        $invitation = static::create([
            'liga_id' => $user->liga_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => static::hashToken($raw),
            'expires_at' => now()->addDays($days),
        ]);

        return [$invitation, $raw];
    }

    public function isUsable(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }
}
