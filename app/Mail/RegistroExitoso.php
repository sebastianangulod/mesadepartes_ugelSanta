<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\Usuario;

/**
 * Mailable para el correo de registro exitoso
 * 
 * Este mailable se envía cuando un usuario se registra exitosamente
 * en el sistema. Contiene información de bienvenida, credenciales
 * de acceso y enlaces útiles para el usuario.
 * 
 * Características:
 * - Información personalizada del usuario
 * - Credenciales de acceso (si fueron generadas)
 * - Enlaces a recursos útiles
 * - Diseño responsive para móviles
 * - Soporte para múltiples idiomas (futuro)
 * - Personalización de contenido según tipo de usuario
 * 
 * @package App\Mail
 */
class RegistroExitoso extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * El usuario que se registró
     *
     * @var \App\Models\Usuario
     */
    public $usuario;

    /**
     * Contraseña temporal (si fue generada)
     *
     * @var string|null
     */
    public $contrasenaTemporal;

    /**
     * Datos adicionales para el correo
     *
     * @var array
     */
    public $datosAdicionales;

    /**
     * URL del sistema
     *
     * @var string
     */
    public $urlSistema;

    /**
     * Año actual para el copyright
     *
     * @var string
     */
    public $anioActual;

    /**
     * Crear una nueva instancia del mensaje
     *
     * @param \App\Models\Usuario $usuario
     * @param string|null $contrasenaTemporal
     * @param array $datosAdicionales
     */
    public function __construct(Usuario $usuario, $contrasenaTemporal = null, $datosAdicionales = [])
    {
        $this->usuario = $usuario;
        $this->contrasenaTemporal = $contrasenaTemporal;
        $this->datosAdicionales = $datosAdicionales;
        $this->urlSistema = config('app.url');
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
            subject: 'Bienvenido a Mesa de Partes Digital - Registro Exitoso',
            tags: ['registro', 'bienvenida', 'usuario'],
            metadata: [
                'usuario_id' => $this->usuario->id,
                'tipo_usuario' => $this->usuario->tipo_persona ?? 'natural',
                'fecha_registro' => now()->toDateTimeString()
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
            view: 'emails.registro-exitoso',
            with: [
                'usuario' => $this->usuario,
                'contrasenaTemporal' => $this->contrasenaTemporal,
                'datosAdicionales' => $this->datosAdicionales,
                'urlSistema' => $this->urlSistema,
                'anioActual' => $this->anioActual,
                'mostrarCredenciales' => !empty($this->contrasenaTemporal),
                'esPersonaNatural' => $this->usuario->tipo_persona === 'natural',
                'esPersonaJuridica' => $this->usuario->tipo_persona === 'juridica',
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
        // Adjuntar guía de usuario si existe
        $adjuntos = [];
        
        $rutaGuia = storage_path('app/public/guias/guia-usuario.pdf');
        if (file_exists($rutaGuia)) {
            $adjuntos[] = Attachment::fromPath($rutaGuia)
                ->as('Guia-de-Usuario.pdf')
                ->withMime('application/pdf');
        }

        // Adjuntar términos y condiciones
        $rutaTerminos = storage_path('app/public/documentos/terminos-condiciones.pdf');
        if (file_exists($rutaTerminos)) {
            $adjuntos[] = Attachment::fromPath($rutaTerminos)
                ->as('Terminos-y-Condiciones.pdf')
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
                    ->tag('registro-exitoso')
                    ->metadata('usuario_id', $this->usuario->id);
    }

    /**
     * Establecer contraseña temporal
     *
     * @param string $contrasena
     * @return $this
     */
    public function withContrasenaTemporal($contrasena)
    {
        $this->contrasenaTemporal = $contrasena;
        return $this;
    }

    /**
     * Establecer datos adicionales
     *
     * @param array $datos
     * @return $this
     */
    public function withDatosAdicionales($datos)
    {
        $this->datosAdicionales = array_merge($this->datosAdicionales, $datos);
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
            'usuario_tipo' => $this->usuario->tipo_persona ?? 'natural',
            'fecha_envio' => now()->toDateTimeString(),
            'ip_origen' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'contrasena_incluida' => !empty($this->contrasenaTemporal),
            'adjuntos' => count($this->attachments())
        ];
    }
}