<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\Usuario;

/**
 * Mailable para el correo de bienvenida al registrarse
 * 
 * Este mailable se envía cuando un usuario se registra exitosamente
 * en el sistema. Contiene información de bienvenida y sus credenciales.
 * 
 * @package App\Mail
 */
class BienvenidaUsuario extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * El usuario recién registrado
     *
     * @var \App\Models\Usuario
     */
    public $usuario;

    /**
     * Crear una nueva instancia del mensaje
     *
     * @param \App\Models\Usuario $usuario
     */
    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Obtener el sobre del mensaje
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Registro Exitoso - Mesa de Partes Digital UGEL Santa',
            tags: ['bienvenida', 'registro', 'usuario'],
            metadata: [
                'usuario_id' => $this->usuario->id,
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
            view: 'emails.bienvenida-usuario',
            with: [
                'usuario' => $this->usuario,
                'nombreCompleto' => $this->usuario->nombres . ' ' . $this->usuario->apellido_paterno . ' ' . $this->usuario->apellido_materno,
                'anioActual' => date('Y'),
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
        return [];
    }
}

