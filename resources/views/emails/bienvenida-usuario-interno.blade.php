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
        .creds { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 18px; margin: 18px 0; }
        .creds p { margin: 6px 0; }
        .notice { background-color: #fffbeb; border-left: 4px solid #d97706; padding: 14px 16px; margin: 18px 0; border-radius: 4px; font-size: 14px; color: #92400e; }
        .button-container { text-align: center; margin: 26px 0; }
        .button { display: inline-block; padding: 12px 32px; background-color: #1a4078; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px; }
        .footer { text-align: center; padding: 20px 24px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Acceso Personal Interno</h1>
            <p>Unidad de Gestión Educativa Local · Santa</p>
        </div>
        <table class="tricolor" cellpadding="0" cellspacing="0"><tr>
            <td width="50%" height="3" bgcolor="#1a4078"></td>
            <td width="30%" height="3" bgcolor="#2b6dc0"></td>
            <td width="20%" height="3" bgcolor="#d52b1e"></td>
        </tr></table>

        <div class="content">
            <p>Estimado/a <strong>{{ trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellido_paterno'] ?? '') . ' ' . ($usuario['apellido_materno'] ?? '')) }}</strong>,</p>
            <p>Se ha creado su cuenta de usuario interno en el sistema Mesa de Partes Digital de la UGEL Santa.</p>
            <div class="creds">
                <p><strong>DNI:</strong> {{ $usuario['dni'] ?? '' }}</p>
                <p><strong>Correo electrónico:</strong> {{ $usuario['email'] ?? '' }}</p>
                <p><strong>Contraseña temporal:</strong> {{ $contrasenaTemporal }}</p>
                @if(!empty($nivelesAsignados))
                <p><strong>Nivel asignado:</strong> Nivel {{ $nivelesAsignados[0]['nivel'] ?? '' }}@if(!empty($nivelesAsignados[0]['nombre'])) - {{ $nivelesAsignados[0]['nombre'] }}@endif</p>
                @endif
            </div>
            <div class="notice">
                Por seguridad, cambie su contraseña temporal en cuanto ingrese por primera vez (menú de usuario &gt; Actualizar Contraseña).
            </div>
            <div class="button-container">
                <a href="{{ $urlLoginInterno }}" class="button" bgcolor="#1a4078">Ingresar al Sistema</a>
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
