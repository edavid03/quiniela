<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    public const PLAN_A = 'A';

    public const PLAN_B = 'B';

    public const PLAN_C = 'C';

    public const PLAN_D = 'D';

    public const PLAN_E = 'E';

    protected $table = 'planes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'limite_usuarios'];

    protected function casts(): array
    {
        return [
            'limite_usuarios' => 'integer',
        ];
    }

    public function ligas(): HasMany
    {
        return $this->hasMany(Liga::class, 'plan_id');
    }

    public function esIlimitado(): bool
    {
        return $this->limite_usuarios === null;
    }
}
