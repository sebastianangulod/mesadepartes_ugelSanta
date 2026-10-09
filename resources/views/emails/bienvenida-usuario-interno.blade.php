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
        .creds {
            background-color: #fff;
            border: 1px solid #dee2e6;
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
        <h1>Bienvenido a Mesa de Partes Digital</h1>
    </div>
    <div class="content">
        <p>Estimado/a {{ $usuario['nombres'] ?? '' }} {{ $usuario['apellido_paterno'] ?? '' }} {{ $usuario['apellido_materno'] ?? '' }},</p>
        <p>Se ha creado su cuenta de usuario interno en el sistema Mesa de Partes Digital de la UGEL Santa.</p>
        <div class="creds">
            <p><strong>DNI:</strong> {{ $usuario['dni'] ?? '' }}</p>
            <p><strong>Correo electrónico:</strong> {{ $usuario['email'] ?? '' }}</p>
            <p><strong>Contraseña temporal:</strong> {{ $contrasenaTemporal }}</p>
            @if(!empty($nivelesAsignados))
            <p><strong>Nivel asignado:</strong> Nivel {{ $nivelesAsignados[0]['nivel'] ?? '' }}@if(!empty($nivelesAsignados[0]['nombre'])) - {{ $nivelesAsignados[0]['nombre'] }}@endif</p>
            @endif
        </div>
        <p>Por seguridad, cambie su contraseña temporal en cuanto ingrese por primera vez (menú de usuario &gt; Actualizar Contraseña).</p>
        <p style="text-align: center;">
            <a href="{{ $urlLoginInterno }}" class="button">Ingresar al Sistema</a>
        </p>
    </div>
    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ $anioActual }} Mesa de Partes Digital UGEL Santa</p>
    </div>
</body>
</html>
