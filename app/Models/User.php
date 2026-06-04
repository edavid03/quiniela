<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Scopes\LigaScope;
use App\Support\Tenancy;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';

    public const ROLE_LIGA_ADMIN = 'liga_admin';

    public const ROLE_LIGA_USER = 'liga_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'liga_id',
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new LigaScope);

        // Autocompleta liga_id desde la liga actual al crear usuarios de liga
        // (p. ej. el import). El superadmin no pertenece a ninguna liga.
        static::creating(function (User $user): void {
            if ($user->liga_id === null && $user->role !== self::ROLE_SUPERADMIN) {
                $user->liga_id = Tenancy::id();
            }
        });
    }

    public function liga(): BelongsTo
    {
        return $this->belongsTo(Liga::class);
    }

    public function predicciones(): HasMany
    {
        return $this->hasMany(Prediccion::class, 'usuario_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isLigaAdmin(): bool
    {
        return $this->role === self::ROLE_LIGA_ADMIN;
    }

    public function isLigaUser(): bool
    {
        return $this->role === self::ROLE_LIGA_USER;
    }
}
