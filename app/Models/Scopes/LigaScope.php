<?php

namespace App\Models\Scopes;

use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LigaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Sin liga actual (contexto superadmin) el scope es un no-op: las queries
        // corren globales. Con liga actual, filtra por la columna liga_id.
        if (! Tenancy::check()) {
            return;
        }

        $builder->where($model->getTable().'.liga_id', Tenancy::id());
    }
}
