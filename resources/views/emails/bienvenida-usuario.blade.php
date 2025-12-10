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
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Bienvenido a Mesa de Partes Digital!</h1>
    </div>
    <div class="content">
        <p>Estimado/a {{ $nombreCompleto }},</p>
        <p>Su registro en el sistema Mesa de Partes Digital de la UGEL Santa ha sido completado exitosamente.</p>
        <p>Sus datos de acceso son:</p>
        <ul>
            <li><strong>DNI:</strong> {{ $usuario->dni }}</li>
            <li><strong>Correo electrónico:</strong> {{ $usuario->email }}</li>
        </ul>
        <p>Para comenzar a usar el sistema, puede acceder a través del siguiente enlace:</p>
        <p style="text-align: center;">
            <a href="{{ route('login') }}" class="button">Iniciar Sesión</a>
        </p>
        <p>Si tiene alguna pregunta o necesita asistencia, no dude en contactarnos.</p>
    </div>
    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ $anioActual }} Mesa de Partes Digital UGEL Santa</p>
    </div>
</body>
</html>

