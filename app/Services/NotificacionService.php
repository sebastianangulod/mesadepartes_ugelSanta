<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Tramite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Crea las notificaciones de la bandeja del usuario ante
 * cambios de estado o de responsable de un trámite.
 */
class NotificacionService
{
    /**
     * Avisa a creador y responsable (menos al actor) cuando el
     * estado del trámite cambia.
     */
    public function avisarCambioEstado(Tramite $tramite, ?string $estadoAnterior = null): void
    {
        $contenido = $this->contenidoPorEstado($tramite);
        if (!$contenido) {
            return;
        }

        $this->notificarInvolucrados(
            $tramite,
            $contenido['titulo'],
            $contenido['mensaje'],
            $contenido['tipo']
        );
    }

    /**
     * Avisa al nuevo responsable cuando el trámite cambia de manos.
     */
    public function avisarNuevoResponsable(Tramite $tramite): void
    {
        $nuevo = (int) ($tramite->id_usuario ?? 0);
        if ($nuevo <= 0 || $nuevo === (int) Auth::id()) {
            return;
        }

        $this->crear(
            $nuevo,
            "Trámite #{$tramite->id} asignado",
            "Se te asignó el trámite #{$tramite->id} (" . ($tramite->procedimiento->nombre ?? 'procedimiento') . "). Revísalo en tu bandeja.",
            'info',
            route('mesa-partes.tramites-recibidos')
        );
    }

    /**
     * Crea una notificación, sin duplicar la misma sin leer.
     */
    public function crear(int $usuarioId, string $titulo, ?string $mensaje, string $tipo = 'info', ?string $url = null): ?Notificacion
    {
        if ($usuarioId <= 0 || $usuarioId === (int) Auth::id()) {
            return null;
        }

        $duplicada = Notificacion::where('id_usuario', $usuarioId)
            ->where('leida', false)
            ->where('titulo', $titulo)
            ->where('mensaje', $mensaje)
            ->exists();

        if ($duplicada) {
            return null;
        }

        try {
            return Notificacion::create([
                'id_usuario' => $usuarioId,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipo,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo crear la notificación: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Notifica a creador y responsable actual, excluyendo a quien actúa.
     */
    protected function notificarInvolucrados(Tramite $tramite, string $titulo, ?string $mensaje, string $tipo): void
    {
        $actor = (int) Auth::id();
        $destinatarios = array_unique(array_filter([
            (int) ($tramite->creado_por ?? 0),
            (int) ($tramite->id_usuario ?? 0),
        ]));

        foreach ($destinatarios as $usuarioId) {
            if ($usuarioId <= 0 || $usuarioId === $actor) {
                continue;
            }

            $this->crear(
                $usuarioId,
                $titulo,
                $mensaje,
                $tipo,
                route('mesa-partes.tramites-recibidos')
            );
        }
    }

    /**
     * Título, mensaje y tipo según el estado nuevo del trámite.
     */
    protected function contenidoPorEstado(Tramite $tramite): ?array
    {
        $id = $tramite->id;
        $proc = $tramite->procedimiento->nombre ?? 'tu expediente';

        return match ($tramite->estado) {
            'OBSERVADO' => [
                'titulo' => "Trámite #{$id} observado",
                'mensaje' => "Tu expediente #{$id} ({$proc}) fue observado. Revisa el motivo y subsánalo dentro del plazo.",
                'tipo' => 'warning',
            ],
            'VERIFICADO' => [
                'titulo' => "Trámite #{$id} verificado",
                'mensaje' => "Tu expediente #{$id} ({$proc}) fue verificado y continúa su curso.",
                'tipo' => 'success',
            ],
            'FINALIZADO' => [
                'titulo' => "Trámite #{$id} finalizado",
                'mensaje' => "Tu expediente #{$id} ({$proc}) fue finalizado. Ya puedes descargar tu voucher.",
                'tipo' => 'success',
            ],
            'RECHAZADO' => [
                'titulo' => "Trámite #{$id} rechazado",
                'mensaje' => "Tu expediente #{$id} ({$proc}) fue rechazado. Revisa el motivo en tu bandeja.",
                'tipo' => 'error',
            ],
            'EN_PROCESO' => [
                'titulo' => "Trámite #{$id} en proceso",
                'mensaje' => "Tu expediente #{$id} ({$proc}) fue aceptado y está en proceso.",
                'tipo' => 'info',
            ],
            default => null,
        };
    }
}
