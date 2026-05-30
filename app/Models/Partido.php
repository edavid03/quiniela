<?php

namespace App\Models;

use App\Models\Scopes\LigaScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    protected $table = 'partidos';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['id', 'local_id', 'visitante_id', 'fecha_utc', 'estadio', 'fase', 'goles_local', 'goles_visitante'];

    public function local()
    {
        return $this->belongsTo(Equipo::class, 'local_id');
    }

    public function visitante()
    {
        return $this->belongsTo(Equipo::class, 'visitante_id');
    }

    public function predicciones()
    {
        return $this->hasMany(Prediccion::class);
    }

    public function fechaCierrePronosticosUtc(): Carbon
    {
        return Carbon::parse($this->fecha_utc, 'UTC')->utc()->subMinutes(30);
    }

    public function admitePronosticos(): bool
    {
        return now()->utc()->lessThan($this->fechaCierrePronosticosUtc());
    }

    public static function proximoCierrePronosticosUtc(): ?Carbon
    {
        $proximoPartidoAbierto = static::query()
            ->where('fecha_utc', '>', now()->utc()->addMinutes(30)->format('Y-m-d H:i:s'))
            ->orderBy('fecha_utc')
            ->first();

        if ($proximoPartidoAbierto === null) {
            return null;
        }

        return $proximoPartidoAbierto->fechaCierrePronosticosUtc();
    }

    public function finalizarPartido(int $golesLocal, int $golesVisitante): void
    {
        $this->update([
            'goles_local' => $golesLocal,
            'goles_visitante' => $golesVisitante,
        ]);

        $signoReal = ($golesLocal > $golesVisitante) ? 1 : (($golesLocal < $golesVisitante) ? 2 : 0);

        // El resultado central puntua a TODAS las ligas: se ignora el LigaScope
        // explicitamente para no depender de que no haya tenant en el contexto.
        $this->predicciones()
            ->withoutGlobalScope(LigaScope::class)
            ->get()
            ->each(function (Prediccion $prediccion) use ($golesLocal, $golesVisitante, $signoReal) {
                $prediccion->evaluarResultado($golesLocal, $golesVisitante, $signoReal);
            });

    }
}
