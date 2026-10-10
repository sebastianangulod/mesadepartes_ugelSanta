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
        .button-container { text-align: center; margin: 26px 0; }
        .button { display: inline-block; padding: 12px 32px; background-color: #1a4078; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px; }
        .footer { text-align: center; padding: 20px 24px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mesa de Partes Digital</h1>
            <p>Unidad de Gestión Educativa Local · Santa</p>
        </div>
        <table class="tricolor" cellpadding="0" cellspacing="0"><tr>
            <td width="50%" height="3" bgcolor="#1a4078"></td>
            <td width="30%" height="3" bgcolor="#2b6dc0"></td>
            <td width="20%" height="3" bgcolor="#d52b1e"></td>
        </tr></table>

        <div class="content">
            <p>Estimado/a <strong>{{ trim($nombreCompleto) }}</strong>,</p>
            <p>Su registro en el sistema Mesa de Partes Digital de la UGEL Santa ha sido completado exitosamente.</p>
            <p>Sus datos de acceso son:</p>
            <div class="creds">
                @if(!empty($usuario->dni))
                <p><strong>DNI:</strong> {{ $usuario->dni }}</p>
                @endif
                @if(!empty($usuario->razon_social))
                <p><strong>Entidad:</strong> {{ $usuario->razon_social }}</p>
                @endif
                <p><strong>Correo electrónico:</strong> {{ $usuario->email }}</p>
            </div>
            <p>Para comenzar a usar el sistema, acceda a través del siguiente enlace:</p>
            <div class="button-container">
                <a href="{{ route('login') }}" class="button" bgcolor="#1a4078">Iniciar Sesión</a>
            </div>
            <p>Si tiene alguna pregunta o necesita asistencia, no dude en contactarnos: mesadepartes@ugelsanta.gob.pe · Lun a Vie 8:00 a.m. – 4:30 p.m.</p>
        </div>

        <div class="footer">
            <p><strong>UGEL Santa · Mesa de Partes Digital</strong></p>
            <p>Este es un correo automático, por favor no responder.</p>
            <p>&copy; {{ $anioActual }} UGEL Santa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
