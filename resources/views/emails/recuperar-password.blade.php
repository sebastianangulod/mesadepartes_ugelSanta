<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .alert {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            text-align: center;
            border: none;
        }
        .button:hover {
            background-color: #1d4ed8;
            color: #ffffff !important;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .info-box {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box strong {
            color: #1f2937;
        }
        .security-info {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f9fafb;
            font-size: 12px;
            color: #6b7280;
        }
        .token-info {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 13px;
        }
        .token-info strong {
            color: #991b1b;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Recuperación de Contraseña</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px;">Mesa de Partes Digital - UGEL Santa</p>
        </div>
        
        <div class="content">
            <p>Estimado/a <strong>{{ $usuario->nombres }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}</strong>,</p>
            
            <p>Hemos recibido una solicitud para restablecer la contraseña de su cuenta en el Sistema de Mesa de Partes Digital de la UGEL Santa.</p>
            
            <div class="alert">
                <div class="alert-title">⚠️ Importante</div>
                <p style="margin: 0;">Si usted no realizó esta solicitud, puede ignorar este correo de forma segura. Su contraseña no será modificada.</p>
            </div>
            
            <p>Para restablecer su contraseña, haga clic en el siguiente botón:</p>
            
            <div class="button-container">
                <a href="{{ $urlRestablecimiento }}" class="button" style="color: #ffffff !important; text-decoration: none; font-weight: bold;">Restablecer Contraseña</a>
            </div>
            
            <p style="text-align: center; font-size: 12px; color: #6b7280;">
                O copie y pegue este enlace en su navegador:<br>
                <span style="word-break: break-all; color: #2563eb;">{{ $urlRestablecimiento }}</span>
            </p>
            
            <div class="info-box">
                <strong>⏰ Tiempo de validez:</strong><br>
                Este enlace expirará el <strong>{{ $fechaExpiracion }}</strong> ({{ $tiempoExpiracion }} minutos desde la solicitud).
            </div>
            
            <div class="token-info">
                <strong>🔑 Información de seguridad:</strong>
                <ul>
                    <li>Este enlace es de un solo uso y expirará automáticamente.</li>
                    <li>No comparta este enlace con nadie.</li>
                    <li>Si el enlace expira, puede solicitar uno nuevo desde la página de recuperación.</li>
                </ul>
            </div>
            
            @if($mostrarInfoSeguridad)
            <div class="security-info">
                <strong>📱 Información de la solicitud:</strong><br>
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
            
            <p>Si tiene problemas para acceder al enlace o necesita asistencia adicional, por favor contacte al administrador del sistema.</p>
        </div>
        
        <div class="footer">
            <p><strong>Mesa de Partes Digital - UGEL Santa</strong></p>
            <p>Este es un correo automático generado por el sistema. Por favor, no responda a este mensaje.</p>
            <p>&copy; {{ $anioActual }} Ministerio de Educación - UGEL Santa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

