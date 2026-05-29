<?php

namespace App\Support;

use App\Models\Liga;

/**
 * Mantiene la liga "actual" de la request. La setea el middleware SetCurrentLiga
 * a partir del slug de la ruta. El LigaScope lo lee para filtrar users y
 * predicciones. En el area de superadmin no se setea ninguna liga, por lo que
 * el scope queda inerte y las queries corren globales (necesario para que el
 * scoring central puntue a TODAS las ligas).
 */
class Tenancy
{
    protected static ?Liga $liga = null;

    public static function set(?Liga $liga): void
    {
        static::$liga = $liga;
    }

    public static function liga(): ?Liga
    {
        return static::$liga;
    }

    public static function id(): ?string
    {
        return static::$liga?->id;
    }

    public static function check(): bool
    {
        return static::$liga !== null;
    }

    public static function forget(): void
    {
        static::$liga = null;
    }
}
