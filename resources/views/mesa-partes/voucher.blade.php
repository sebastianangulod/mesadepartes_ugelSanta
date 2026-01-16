<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher de Trámite - {{ $tramite->id }}-{{ $tramite->periodo ?? date('Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        .container {
            border: 2px solid #000;
            padding: 30px;
            width: 800px;
            margin: auto;
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .header .subtitle {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }

        .info {
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .info p {
            margin: 8px 0;
        }

        .info span {
            font-weight: bold;
        }

        .footer {
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
        }

        .footer a {
            color: #0066cc;
            text-decoration: underline;
        }

        .center {
            text-align: center;
        }

        .derivado-list {
            list-style: none;
            padding-left: 0;
        }

        .derivado-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        @media print {
            body {
                background-color: #fff;
                margin: 0;
            }
            .container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>UGEL SANTA</h2>
            <p class="subtitle">
                "Año del Bicentenario, de la consolidación de nuestra Independencia, <br>
                y de la conmemoración de las heroicas batallas de Junín y Ayacucho"
            </p>
            <div class="info">
                <p>
                    <span>Mesa de Partes:</span> {{ now()->format('d/m/Y H:i:s') }}<br>
                    <span>Periodo:</span> {{ $tramite->periodo ?? date('Y') }}
                </p>
            </div>
        </div>

        <div class="info">
            <p><span>Tipo de Trámite:</span> {{ $tramite->tipo_tramite ?? 'VIRTUAL' }}</p>
            <p><span>Fecha de Ingreso:</span> {{ $tramite->fecha_creacion ? $tramite->fecha_creacion->format('d/m/Y') : 'N/A' }}</p>
            <p><span>Fecha de Recepción:</span> {{ $tramite->fecha_creacion ? $tramite->fecha_creacion->format('d/m/Y H:i:s') : 'N/A' }}</p>
            <p><span>Tipo de Documento:</span> Expediente</p>
            <p><span>N°:</span> {{ $tramite->id }}-{{ $tramite->periodo ?? date('Y') }}</p>
            <p><span>Clave:</span> {{ $tramite->id }}</p>
            <p><span>Razón Social & Apellidos y Nombres:</span> 
                {{ $tramite->usuario->razon_social ?? ($tramite->usuario->nombres . ' ' . $tramite->usuario->apellido_paterno . ' ' . $tramite->usuario->apellido_materno) }}
            </p>
            <p><span>DNI:</span> {{ $tramite->usuario->dni }}</p>
            <p><span>Registrado por:</span> {{ $tramite->usuario->nombres }} {{ $tramite->usuario->apellido_paterno }}</p>
            <p><span>Folios:</span> {{ $tramite->tramitescol ?? 1 }} FOLIOS</p>
            <p><span>Asunto:</span> {{ $tramite->procedimiento->nombre ?? 'N/A' }}</p>
            <p><span>Número de Voucher:</span> {{ $tramite->numero_voucher ?? ($tramite->id . '-' . ($tramite->periodo ?? date('Y'))) }}</p>
        </div>

        <div class="info">
            <p><span>Derivado A:</span></p>
            <ul class="derivado-list">
                @if(isset($nivelesDerivacion) && count($nivelesDerivacion) > 0)
                    @foreach($nivelesDerivacion as $nivel)
                        <li>{{ $nivel }}</li>
                    @endforeach
                @else
                    <li>NINGUNO</li>
                @endif
            </ul>
        </div>

        <div class="footer">
            <p>
                Ahora consulta tu trámite vía internet en <br>
                <a href="{{ url('/mesa-partes/rastrear') }}">{{ url('/mesa-partes/rastrear') }}</a><br>
                Ingresa los datos de tu voucher y luego Consultar
            </p>
        </div>
    </div>

    <div class="center" style="margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
</body>
</html>

