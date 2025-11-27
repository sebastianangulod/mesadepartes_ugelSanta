<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\Usuario;

/**
 * Mailable para el correo de recuperación de contraseña
 * 
 * Este mailable se envía cuando un usuario solicita recuperar
 * su contraseña. Contiene un enlace único y temporal para
 * restablecer la contraseña.
 * 
 * Características:
     * - Enlace temporal con token único
     * - Tiempo de expiración claramente indicado
     * - Instrucciones de seguridad
     * - Enlace directo al formulario de restablecimiento
     * - Diseño responsive para móviles
     * - Notificación de seguridad si no fue el usuario
     * 
 * @package App\Mail
 */
class RecuperarPassword extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * El usuario que solicitó la recuperación
     *
     * @var \App\Models\Usuario
     */
    public $usuario;

    /**
     * Token de recuperación
     *
     * @var string
     */
    public $token;

    /**
     * URL de restablecimiento
     *
     * @var string
     */
    public $urlRestablecimiento;

    /**
     * Tiempo de expiración en minutos
     *
     * @var int
     */
    public $tiempoExpiracion;

    /**
     * Fecha de expiración
     *
     * @var string
     */
    public $fechaExpiracion;

    /**
     * Año actual para el copyright
     *
     * @var string
     */
    public $anioActual;

    /**
     * Dirección IP del solicitante
     *
     * @var string|null
     */
    public $ipSolicitante;

    /**
     * Dispositivo del solicitante
     *
     * @var string|null
     */
    public $dispositivoSolicitante;

    /**
     * Crear una nueva instancia del mensaje
     *
     * @param \App\Models\Usuario $usuario
     * @param string $token
     * @param int $tiempoExpiracion
     * @param string|null $ipSolicitante
     * @param string|null $dispositivoSolicitante
     */
    public function __construct(
        Usuario $usuario, 
        $token, 
        $tiempoExpiracion = 60, 
        $ipSolicitante = null, 
        $dispositivoSolicitante = null
    ) {
        $this->usuario = $usuario;
        $this->token = $token;
        $this->tiempoExpiracion = $tiempoExpiracion;
        $this->fechaExpiracion = now()->addMinutes($tiempoExpiracion)->format('d/m/Y H:i:s');
        $this->anioActual = date('Y');
        $this->ipSolicitante = $ipSolicitante;
        $this->dispositivoSolicitante = $dispositivoSolicitante;
        
        // Generar URL de restablecimiento
        $this->urlRestablecimiento = $this->generarUrlRestablecimiento();
    }

    /**
     * Obtener el sobre del mensaje
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Solicitud de Recuperación de Contraseña - Mesa de Partes Digital',
            tags: ['recuperacion', 'password', 'seguridad'],
            metadata: [
                'usuario_id' => $this->usuario->id,
                'token' => $this->token,
                'ip_solicitante' => $this->ipSolicitante,
                'fecha_solicitud' => now()->toDateTimeString()
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
            view: 'emails.recuperar-password',
            with: [
                'usuario' => $this->usuario,
                'token' => $this->token,
                'urlRestablecimiento' => $this->urlRestablecimiento,
                'tiempoExpiracion' => $this->tiempoExpiracion,
                'fechaExpiracion' => $this->fechaExpiracion,
                'anioActual' => $this->anioActual,
                'ipSolicitante' => $this->ipSolicitante,
                'dispositivoSolicitante' => $this->dispositivoSolicitante,
                'mostrarInfoSeguridad' => !empty($this->ipSolicitante) || !empty($this->dispositivoSolicitante),
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
        // No incluir adjuntos por seguridad
        return [];
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
                    ->priority(1) // Alta prioridad por seguridad
                    ->tag('recuperar-password')
                    ->metadata('usuario_id', $this->usuario->id)
                    ->metadata('token', $this->token);
    }

    /**
     * Generar URL de restablecimiento
     *
     * @return string
     */
    protected function generarUrlRestablecimiento()
    {
        $baseUrl = config('app.url');
        $ruta = '/recuperar-password/reset';
        
        return $baseUrl . $ruta . '?token=' . $this->token . '&email=' . urlencode($this->usuario->email);
    }

    /**
     * Establecer información de seguridad
     *
     * @param string $ip
     * @param string $dispositivo
     * @return $this
     */
    public function withInfoSeguridad($ip, $dispositivo)
    {
        $this->ipSolicitante = $ip;
        $this->dispositivoSolicitante = $dispositivo;
        return $this;
    }

    /**
     * Personalizar tiempo de expiración
     *
     * @param int $minutos
     * @return $this
     */
    public function withTiempoExpiracion($minutos)
    {
        $this->tiempoExpiracion = $minutos;
        $this->fechaExpiracion = now()->addMinutes($minutos)->format('d/m/Y H:i:s');
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
            'usuario_id' => $this->usuario->id,
            'usuario_email' => $this->usuario->email,
            'token' => $this->token,
            'fecha_envio' => now()->toDateTimeString(),
            'ip_origen' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'ip_solicitante' => $this->ipSolicitante,
            'dispositivo_solicitante' => $this->dispositivoSolicitante,
            'tiempo_expiracion' => $this->tiempoExpiracion,
            'url_restablecimiento' => $this->urlRestablecimiento
        ];
    }

    /**
     * Verificar si el token está cerca de expirar
     *
     * @return bool
     */
    public function estaCercaDeExpirar()
    {
        // Considerar que está cerca de expirar si quedan menos de 15 minutos
        $tiempoRestante = $this->tiempoExpiracion - (time() - strtotime($this->fechaExpiracion)) / 60;
        return $tiempoRestante <= 15;
    }
}