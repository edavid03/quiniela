<?php

namespace App\Models;

use App\Models\Scopes\LigaScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    protected $table = 'partidos';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['id', 'local_id', 'visitante_id', 'fecha_utc', 'fecha_caracas', 'estadio', 'fase', 'goles_local', 'goles_visitante'];

    protected static function booted(): void
    {
        static::saving(function (Partido $partido): void {
            if ($partido->fecha_utc === null) {
                $partido->fecha_caracas = null;

                return;
            }

            $partido->fecha_caracas = self::fechaCaracasDesdeUtc($partido->fecha_utc);
        });
    }

    public static function fechaCaracasDesdeUtc(mixed $fechaUtc): string
    {
        return Carbon::parse($fechaUtc, 'UTC')
            ->setTimezone('America/Caracas')
            ->format('Y-m-d H:i:s');
    }

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

    public function scopeConPronosticosAbiertos(Builder $query): Builder
    {
        return $query->where('fecha_utc', '>', now()->utc()->addMinutes(30)->format('Y-m-d H:i:s'));
    }

    public static function proximoCierrePronosticosUtc(): ?Carbon
    {
        $proximoPartidoAbierto = static::query()
            ->conPronosticosAbiertos()
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
