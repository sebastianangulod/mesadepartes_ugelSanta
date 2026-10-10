<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 20px; background-color: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .header { background-color: #112a4d; color: #ffffff; padding: 28px 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 8px 0 0 0; font-size: 13px; color: #cbd5e1; }
        .tricolor { width: 100%; border-collapse: collapse; }
        .content { padding: 28px 24px; font-size: 15px; }
        .alert { background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 14px 16px; margin: 18px 0; border-radius: 4px; font-size: 14px; }
        .alert strong { color: #991b1b; }
        .footer { text-align: center; padding: 20px 24px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Aviso de Seguridad</h1>
            <p>Unidad de Gestión Educativa Local · Santa</p>
        </div>
        <table class="tricolor" cellpadding="0" cellspacing="0"><tr>
            <td width="50%" height="3" bgcolor="#1a4078"></td>
            <td width="30%" height="3" bgcolor="#2b6dc0"></td>
            <td width="20%" height="3" bgcolor="#d52b1e"></td>
        </tr></table>

        <div class="content">
            <p>Estimado/a <strong>{{ trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellido_paterno'] ?? '')) }}</strong>,</p>
            @if($motivo === 'admin')
            <p>Un administrador restableció la contraseña de su cuenta ({{ $usuario['email'] ?? '' }}) el {{ $fecha }}.</p>
            <p>Solicite su clave temporal por un canal seguro y cámbiela en cuanto ingrese.</p>
            @else
            <p>La contraseña de su cuenta ({{ $usuario['email'] ?? '' }}) fue actualizada el {{ $fecha }}.</p>
            @endif
            <div class="alert">
                <strong>¿No fue usted?</strong> Comuníquese de inmediato con la mesa de partes de la UGEL Santa.
            </div>
        </div>

        <div class="footer">
            <p><strong>UGEL Santa · Mesa de Partes Digital</strong></p>
            <p>Este es un correo automático, por favor no responder.</p>
            <p>&copy; {{ $anioActual }} UGEL Santa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
