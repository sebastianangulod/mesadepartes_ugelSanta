<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher de Trámite - {{ $tramite->id }}-{{ $tramite->periodo ?? date('Y') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 24px 16px;
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .container {
            border: 1px solid #cbd5e1;
            border-top: 4px solid #112a4d;
            padding: 0;
            width: 800px;
            max-width: 100%;
            margin: auto;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(17, 42, 77, 0.12);
        }

        .header {
            text-align: center;
            padding: 28px 30px 20px;
            border-bottom: 2px solid #112a4d;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #112a4d;
        }

        .header .oficina {
            font-size: 13px;
            font-weight: bold;
            color: #1a4078;
            margin: 6px 0 0;
            letter-spacing: 1px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 10px;
            font-style: italic;
        }

        .voucher-num {
            background-color: #112a4d;
            color: #fff;
            text-align: center;
            padding: 12px 20px;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .info {
            font-size: 14px;
            line-height: 1.8;
            padding: 8px 30px;
        }

        .info p {
            margin: 0;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info p:last-child {
            border-bottom: none;
        }

        .info span {
            font-weight: bold;
            color: #334155;
            display: inline-block;
            min-width: 220px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a4078;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 18px 30px 4px;
            margin: 0;
        }

        .footer {
            font-size: 12px;
            margin-top: 10px;
            padding: 20px 30px 26px;
            border-top: 1px solid #e2e8f0;
            color: #475569;
        }

        .footer a {
            color: #1a4078;
        }

        .center {
            text-align: center;
        }

        .derivado-list {
            list-style: none;
            padding-left: 0;
            margin: 4px 0 0;
        }

        .derivado-list li {
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .no-print {
            text-align: center;
            margin-top: 20px;
        }

        .btn-print {
            display: inline-block;
            padding: 10px 28px;
            background-color: #1a4078;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-print:hover {
            background-color: #173763;
        }

        .meta {
            text-align: center;
            font-size: 12px;
            color: #64748b;
            margin-top: 12px;
        }

        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .container {
                border: 1px solid #000;
                border-top: 4px solid #000;
                box-shadow: none;
                width: 100%;
            }
            .header h2, .header .oficina { color: #000; }
            .voucher-num { background-color: #000; }
            .no-print, .meta { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>UGEL SANTA</h2>
            <p class="oficina">MESA DE PARTES DIGITAL</p>
            <p class="subtitle">
                "Año del Bicentenario, de la consolidación de nuestra Independencia, <br>
                y de la conmemoración de las heroicas batallas de Junín y Ayacucho"
            </p>
        </div>

        <div class="voucher-num">
            VOUCHER N° {{ $tramite->numero_voucher ?? ($tramite->id . '-' . ($tramite->periodo ?? date('Y'))) }}
        </div>

        <div class="info">
            <p><span>Mesa de Partes:</span> {{ now()->format('d/m/Y H:i:s') }}</p>
            <p><span>Periodo:</span> {{ $tramite->periodo ?? date('Y') }}</p>
            <p><span>Tipo de Trámite:</span> {{ $tramite->tipo_tramite ?? 'VIRTUAL' }}</p>
            <p><span>Fecha de Ingreso:</span> {{ $tramite->fecha_creacion ? $tramite->fecha_creacion->format('d/m/Y') : 'N/A' }}</p>
            <p><span>Fecha de Recepción:</span> {{ $tramite->fecha_creacion ? $tramite->fecha_creacion->format('d/m/Y H:i:s') : 'N/A' }}</p>
            <p><span>Tipo de Documento:</span> Expediente</p>
            <p><span>N°:</span> {{ $tramite->id }}-{{ $tramite->periodo ?? date('Y') }}</p>
            <p><span>Clave:</span> {{ $tramite->id }}</p>
            @php $sol = $tramite->datosSolicitante(); @endphp
            <p><span>Razón Social & Apellidos y Nombres:</span>
                {{ $sol['razon_social'] && in_array($tramite->titular_tipo_persona, [2, 3]) ? $sol['razon_social'] : trim($sol['nombres'] . ' ' . $sol['apellido_paterno'] . ' ' . $sol['apellido_materno']) }}
            </p>
            @if(!empty($sol['dni']))
            <p><span>DNI:</span> {{ $sol['dni'] }}</p>
            @endif
            @if(!empty($sol['ruc']))
            <p><span>RUC:</span> {{ $sol['ruc'] }}</p>
            @endif
            <p><span>Registrado por (usuario interno):</span> {{ trim(($tramite->creador->nombres ?? '') . ' ' . ($tramite->creador->apellido_paterno ?? '')) ?: '—' }}</p>
            <p><span>Folios:</span> {{ $tramite->tramitescol ?? 1 }} FOLIOS</p>
            <p><span>Asunto:</span> {{ $tramite->procedimiento->nombre ?? 'N/A' }}</p>
        </div>

        <p class="section-title">Derivado a</p>
        <div class="info">
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

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            Imprimir
        </button>
        <p class="meta">Guarde este voucher: lo necesitará para el seguimiento de su trámite.</p>
    </div>
</body>
</html>
