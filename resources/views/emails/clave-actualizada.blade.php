<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Aviso de Seguridad</h1>
    </div>
    <div class="content">
        <p>Estimado/a {{ $usuario['nombres'] ?? '' }} {{ $usuario['apellido_paterno'] ?? '' }},</p>
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
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ $anioActual }} Mesa de Partes Digital UGEL Santa</p>
    </div>
</body>
</html>
