<?php

namespace App\Console\Commands;

use App\Domain\Tramites\EstadoTramite;
use App\Models\Tramite;
use App\Services\NotificacionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Rechaza automáticamente trámites vencidos y avisa a los involucrados:
 * - OBSERVADO con limite_observacion pasado (2 días sin subsanar).
 * - Activos con más de 30 días sin finalizar.
 *
 * Los rechazados salen de las bandejas activas (estado terminal).
 * Uso: php artisan tramites:vencer-automaticos [--dry-run]
 */
class VencerTramitesAutomaticamente extends Command
{
    protected $signature = 'tramites:vencer-automaticos {--dry-run : Muestra lo que vencería sin aplicar cambios}';

    protected $description = 'Rechaza automáticamente trámites vencidos (observaciones sin subsanar y expedientes con más de 30 días) y notifica a los involucrados';

    public function handle(NotificacionService $notificaciones): int
    {
        $seco = (bool) $this->option('dry-run');
        $obsVencidas = 0;
        $antiguos = 0;

        NotificacionService::$silenciarCambios = true;

        try {
            // 1. Observados que superaron el plazo de subsanación.
            $observados = Tramite::where('estado', EstadoTramite::OBSERVADO)
                ->whereNotNull('limite_observacion')
                ->where('limite_observacion', '<', now())
                ->get();

            foreach ($observados as $tramite) {
                $this->vencer($tramite, $notificaciones, 'superó el plazo de 2 días para subsanar la observación', $seco);
                $obsVencidas++;
            }

            // 2. Activos con más de 30 días sin finalizar.
            // (Los del punto 1 ya son RECHAZADO aquí, el scope los excluye.)
            $viejos = Tramite::activos()
                ->where('fecha_creacion', '<', now()->subDays(30))
                ->get();

            foreach ($viejos as $tramite) {
                $this->vencer($tramite, $notificaciones, 'superó los 30 días sin finalizar', $seco);
                $antiguos++;
            }
        } finally {
            NotificacionService::$silenciarCambios = false;
        }

        $mensaje = "Vencimiento automático" . ($seco ? ' (simulación)' : '')
            . ": {$obsVencidas} observados vencidos, {$antiguos} con más de 30 días.";
        $this->info($mensaje);
        Log::info($mensaje);

        return self::SUCCESS;
    }

    private function vencer(Tramite $tramite, NotificacionService $notificaciones, string $causa, bool $seco): void
    {
        $detalle = "Trámite #{$tramite->id} ({$tramite->estado}): {$causa}.";

        if ($seco) {
            $this->line('  [simulación] ' . $detalle);
            return;
        }

        $previa = $tramite->observaciones ? ' Observación previa: ' . $tramite->observaciones : '';

        $tramite->estado = EstadoTramite::RECHAZADO;
        $tramite->ultimo_estado = EstadoTramite::RECHAZADO;
        $tramite->observaciones = 'RECHAZO AUTOMÁTICO POR VENCIMIENTO (' . $causa . ').' . $previa;
        $tramite->limite_observacion = null;
        $tramite->fecha_rechazo = now();
        $tramite->rechazado_por = null; // sistema
        $tramite->save();

        $notificaciones->avisarVencimiento($tramite, $causa);

        Log::info('Rechazo automático por vencimiento', [
            'tramite_id' => $tramite->id,
            'causa' => $causa,
        ]);
    }
}
