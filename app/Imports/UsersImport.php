<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Parsea el xlsx a filas asociativas por encabezado. Se espera que la primera
 * fila tenga las columnas: email, username, name.
 */
class UsersImport implements ToArray, WithHeadingRow
{
    public function array(array $array): array
    {
        return $array;
    }
}
