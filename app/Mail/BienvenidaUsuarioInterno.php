<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

/**
 * Mailable para el correo de bienvenida a usuarios internos
 * 
 * Este mailable se envía cuando se crea un nuevo usuario interno
 * en el sistema. Contiene credenciales de acceso, información
 * sobre el sistema y enlaces a recursos útiles.
 * 
 * Características:
 * - Credenciales de acceso temporales
 * - Información sobre el sistema y sus funciones
 * - Enlaces a guías y tutoriales
 * - Instrucciones de seguridad
 * - Diseño responsive para móviles
 * - Personalización según el área/nivel del usuario
 * 
 * @package App\Mail
 */
class BienvenidaUsuarioInterno extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * Datos del usuario interno
     *
     * @var array
     */
    public $usuario;

    /**
     * Contraseña temporal
     *
     * @var string
     */
    public $contrasenaTemporal;

    /**
     * URL del sistema
     *
     * @var string
     */
    public $urlSistema;

    /**
     * URL de login interno
     *
     * @var string
     */
    public $urlLoginInterno;

    /**
     * Año actual para el copyright
     *
     * @var string
     */
    public $anioActual;

    /**
     * Información del área
     *
     * @var array
     */
    public $infoArea;

    /**
     * Niveles asignados al usuario
     *
     * @var array
     */
    public $nivelesAsignados;

    /**
     * Permisos del usuario
     *
     * @var array
     */
    public $permisos;

    /**
     * Crear una nueva instancia del mensaje
     *
     * @param array $usuario
     * @param string $contrasenaTemporal
     * @param array $infoArea
     * @param array $nivelesAsignados
     * @param array $permisos
     */
    public function __construct(
        $usuario, 
        $contrasenaTemporal, 
        $infoArea = [], 
        $nivelesAsignados = [], 
        $permisos = []
    ) {
        $this->usuario = $usuario;
        $this->contrasenaTemporal = $contrasenaTemporal;
        $this->infoArea = $infoArea;
        $this->nivelesAsignados = $nivelesAsignados;
        $this->permisos = $permisos;
        
        // Generar URLs
        $this->urlSistema = config('app.url');
        $this->urlLoginInterno = config('app.url') . '/login-interno';
        $this->anioActual = date('Y');
    }

    /**
     * Obtener el sobre del mensaje
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Bienvenido al Sistema de Mesa de Partes Digital - Credenciales de Acceso',
            tags: ['bienvenida', 'usuario-interno', 'credenciales', 'acceso'],
            metadata: [
                'usuario_id' => $this->usuario['id'] ?? null,
                'usuario_email' => $this->usuario['email'] ?? '',
                'area_id' => $this->infoArea['id'] ?? null,
                'fecha_creacion' => now()->toDateTimeString(),
                'tipo_correo' => 'bienvenida_interno'
            ]
        );
    }

    /**
     * Obtener la definición de contenido del mensaje
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.bienvenida-usuario-interno',
            with: [
                'usuario' => $this->usuario,
                'contrasenaTemporal' => $this->contrasenaTemporal,
                'urlSistema' => $this->urlSistema,
                'urlLoginInterno' => $this->urlLoginInterno,
                'anioActual' => $this->anioActual,
                'infoArea' => $this->infoArea,
                'nivelesAsignados' => $this->nivelesAsignados,
                'permisos' => $this->permisos,
                'esAdministrador' => in_array('admin', $this->permisos),
                'puedeGestionarTramites' => in_array('gestionar_tramites', $this->permisos),
                'puedeGestionarUsuarios' => in_array('gestionar_usuarios', $this->permisos),
                'puedeVerReportes' => in_array('ver_reportes', $this->permisos),
                'tieneNivelesAsignados' => !empty($this->nivelesAsignados),
                'tienePermisosEspeciales' => !empty(array_diff($this->permisos, ['acceso_basico'])),
            ]
        );
    }

    /**
     * Obtener los archivos adjuntos del mensaje
     *
     * @return array
     */
    public function attachments()
    {
        $adjuntos = [];
        
        // Adjuntar manual de usuario interno
        $rutaManual = storage_path('app/public/manuales/manual-usuario-interno.pdf');
        if (file_exists($rutaManual)) {
            $adjuntos[] = Attachment::fromPath($rutaManual)
                ->as('Manual-de-Usuario-Interno.pdf')
                ->withMime('application/pdf');
        }

        // Adjuntar guía de primeros pasos
        $rutaGuia = storage_path('app/public/guias/guia-primeros-pasos-interno.pdf');
        if (file_exists($rutaGuia)) {
            $adjuntos[] = Attachment::fromPath($rutaGuia)
                ->as('Guia-Primeros-Pasos.pdf')
                ->withMime('application/pdf');
        }

        // Adjuntar políticas de seguridad
        $rutaPoliticas = storage_path('app/public/documentos/politicas-seguridad-interno.pdf');
        if (file_exists($rutaPoliticas)) {
            $adjuntos[] = Attachment::fromPath($rutaPoliticas)
                ->as('Politicas-de-Seguridad.pdf')
                ->withMime('application/pdf');
        }

        return $adjuntos;
    }

    /**
     * Configurar el build del mensaje
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->replyTo(config('mail.from.address'), config('mail.from.name'))
                    ->priority(1) // Alta prioridad
                    ->tag('bienvenida-interno')
                    ->metadata('usuario_id', $this->usuario['id'] ?? null)
                    ->metadata('area_id', $this->infoArea['id'] ?? null);
    }

    /**
     * Establecer información del área
     *
     * @param array $infoArea
     * @return $this
     */
    public function withInfoArea($infoArea)
    {
        $this->infoArea = $infoArea;
        return $this;
    }

    /**
     * Establecer niveles asignados
     *
     * @param array $niveles
     * @return $this
     */
    public function withNivelesAsignados($niveles)
    {
        $this->nivelesAsignados = $niveles;
        return $this;
    }

    /**
     * Establecer permisos
     *
     * @param array $permisos
     * @return $this
     */
    public function withPermisos($permisos)
    {
        $this->permisos = $permisos;
        return $this;
    }

    /**
     * Personalizar asunto del correo
     *
     * @param string $asunto
     * @return $this
     */
    public function withAsuntoPersonalizado($asunto)
    {
        return $this->subject($asunto);
    }

    /**
     * Obtener información de seguimiento
     *
     * @return array
     */
    public function getTrackingInfo()
    {
        return [
            'usuario_id' => $this->usuario['id'] ?? null,
            'usuario_email' => $this->usuario['email'] ?? '',
            'usuario_nombre' => $this->usuario['nombre_completo'] ?? '',
            'area_id' => $this->infoArea['id'] ?? null,
            'area_nombre' => $this->infoArea['nombre'] ?? '',
            'niveles_cantidad' => count($this->nivelesAsignados),
            'permisos_cantidad' => count($this->permisos),
            'fecha_envio' => now()->toDateTimeString(),
            'ip_origen' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'adjuntos_cantidad' => count($this->attachments())
        ];
    }

    /**
     * Verificar si el usuario tiene permisos administrativos
     *
     * @return bool
     */
    public function tienePermisosAdministrativos()
    {
        return in_array('admin', $this->permisos) || 
               in_array('gestionar_usuarios', $this->permisos) ||
               in_array('gestionar_areas', $this->permisos) ||
               in_array('gestionar_procedimientos', $this->permisos);
    }
}