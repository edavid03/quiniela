<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Liga extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ligas';

    protected $fillable = ['name', 'slug', 'is_active', 'plan_id'];

    /**
     * Slugs que no puede tomar una liga porque chocan con rutas del sistema
     * (el slug vive en la raiz: /{slug}/...).
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = [
        'superadmin',
        'admin',
        'login',
        'logout',
        'register',
        'api',
        'assets',
        'storage',
        'build',
        'css',
        'js',
        'up',
        'dashboard',
        'invitacion',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function limiteUsuarios(): ?int
    {
        return $this->plan?->limite_usuarios;
    }

    public function usuariosActuales(): int
    {
        return User::withoutGlobalScope(Scopes\LigaScope::class)
            ->where('liga_id', $this->id)
            ->count();
    }

    public function usuariosDisponibles(): ?int
    {
        $limite = $this->limiteUsuarios();

        if ($limite === null) {
            return null;
        }

        return max(0, $limite - $this->usuariosActuales());
    }

    public function permiteAgregarUsuarios(int $cantidad): bool
    {
        $disponibles = $this->usuariosDisponibles();

        return $disponibles === null || $cantidad <= $disponibles;
    }
}
