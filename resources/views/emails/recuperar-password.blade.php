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
        .alert { background-color: #fffbeb; border-left: 4px solid #d97706; padding: 14px 16px; margin: 18px 0; border-radius: 4px; font-size: 14px; }
        .alert-title { font-weight: bold; color: #92400e; margin-bottom: 6px; }
        .button-container { text-align: center; margin: 26px 0; }
        .button { display: inline-block; padding: 12px 32px; background-color: #1a4078; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px; }
        .fallback { text-align: center; font-size: 12px; color: #64748b; }
        .info-box { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 6px; margin: 18px 0; font-size: 14px; }
        .security-box { background-color: #fef2f2; border: 1px solid #fecaca; padding: 14px 16px; margin: 18px 0; border-radius: 6px; font-size: 13px; }
        .security-box strong.title { color: #991b1b; }
        ul, ol { margin: 10px 0; padding-left: 22px; }
        li { margin: 5px 0; }
        .footer { text-align: center; padding: 20px 24px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Recuperación de Contraseña</h1>
            <p>Mesa de Partes Digital · UGEL Santa</p>
        </div>
        <table class="tricolor" cellpadding="0" cellspacing="0"><tr>
            <td width="50%" height="3" bgcolor="#1a4078"></td>
            <td width="30%" height="3" bgcolor="#2b6dc0"></td>
            <td width="20%" height="3" bgcolor="#d52b1e"></td>
        </tr></table>

        <div class="content">
            <p>Estimado/a <strong>{{ $usuario->nombres }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}</strong>,</p>

            <p>Hemos recibido una solicitud para restablecer la contraseña de su cuenta en el Sistema de Mesa de Partes Digital de la UGEL Santa.</p>

            <div class="alert">
                <div class="alert-title">Importante</div>
                <p style="margin: 0;">Si usted no realizó esta solicitud, ignore este correo de forma segura. Su contraseña no será modificada.</p>
            </div>

            <p>Para restablecer su contraseña, utilice el siguiente botón:</p>

            <div class="button-container">
                <a href="{{ $urlRestablecimiento }}" class="button" bgcolor="#1a4078" style="color: #ffffff !important; text-decoration: none; font-weight: bold;">Restablecer Contraseña</a>
            </div>

            <p class="fallback">
                O copie y pegue este enlace en su navegador:<br>
                <span style="word-break: break-all; color: #1a4078;">{{ $urlRestablecimiento }}</span>
            </p>

            <div class="info-box">
                <strong>Tiempo de validez:</strong><br>
                Este enlace expirará el <strong>{{ $fechaExpiracion }}</strong> ({{ $tiempoExpiracion }} minutos desde la solicitud).
            </div>

            <div class="security-box">
                <strong class="title">Información de seguridad:</strong>
                <ul>
                    <li>Este enlace es de un solo uso y expirará automáticamente.</li>
                    <li>No comparta este enlace con nadie.</li>
                    <li>Si el enlace expira, solicite uno nuevo desde la página de recuperación.</li>
                </ul>
            </div>

            @if($mostrarInfoSeguridad)
            <div class="info-box">
                <strong>Información de la solicitud:</strong><br>
                @if($ipSolicitante)
                <strong>Dirección IP:</strong> {{ $ipSolicitante }}<br>
                @endif
                @if($dispositivoSolicitante)
                <strong>Dispositivo:</strong> {{ $dispositivoSolicitante }}<br>
                @endif
                <strong>Fecha y hora:</strong> {{ now()->format('d/m/Y H:i:s') }}
            </div>
            @endif

            <p><strong>Instrucciones:</strong></p>
            <ol>
                <li>Haga clic en el botón "Restablecer Contraseña" o copie el enlace proporcionado.</li>
                <li>Ingrese su DNI y su nueva contraseña.</li>
                <li>Confirme la nueva contraseña.</li>
                <li>Inicie sesión con su nueva contraseña.</li>
            </ol>

            <p>Si tiene problemas para acceder al enlace o necesita asistencia adicional, comuníquese con la mesa de partes: mesadepartes@ugelsanta.gob.pe · Lun a Vie 8:00 a.m. – 4:30 p.m.</p>
        </div>

        <div class="footer">
            <p><strong>UGEL Santa · Mesa de Partes Digital</strong></p>
            <p>Este es un correo automático generado por el sistema. Por favor, no responda a este mensaje.</p>
            <p>&copy; {{ $anioActual }} UGEL Santa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
