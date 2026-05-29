<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Plantilla descargable para que el admin la rellene y luego la importe.
 * Los encabezados deben coincidir con lo que espera UsersImport (WithHeadingRow).
 */
class UsersTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['email', 'username', 'name'];
    }

    public function array(): array
    {
        return [
            ['juan@correo.com', 'juanp', 'Juan Pérez'],
            ['ana@correo.com', 'anag', 'Ana González'],
        ];
    }
}
