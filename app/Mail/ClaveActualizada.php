<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Aviso de seguridad: la contraseña de la cuenta fue cambiada.
 *
 * No contiene secretos: solo informa del cambio (por el propio usuario
 * o por un administrador) para que el titular detecte cambios no autorizados.
 */
class ClaveActualizada extends Mailable
{
    use Queueable;

    public $usuario;

    public $motivo;

    public $fecha;

    public $anioActual;

    /**
     * @param array $usuario ['nombres','apellido_paterno','apellido_materno','email']
     * @param string $motivo 'propio'|'admin'
     */
    public function __construct($usuario, $motivo = 'propio')
    {
        $this->usuario = $usuario;
        $this->motivo = $motivo;
        $this->fecha = now()->format('d/m/Y H:i:s');
        $this->anioActual = date('Y');
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Aviso de seguridad: su contraseña fue actualizada - Mesa de Partes UGEL Santa',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.clave-actualizada',
            with: [
                'usuario' => $this->usuario,
                'motivo' => $this->motivo,
                'fecha' => $this->fecha,
                'anioActual' => $this->anioActual,
            ]
        );
    }

    public function attachments()
    {
        return [];
    }
}
