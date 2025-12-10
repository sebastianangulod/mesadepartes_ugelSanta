@extends('layouts.app')

@section('title', 'Confirmación de Trámite - Mesa de Partes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <x-alerts />
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-check-circle"></i> Trámite Registrado Exitosamente
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h4 class="alert-heading">¡Trámite creado correctamente!</h4>
                        <p>Su trámite ha sido registrado en el sistema con el siguiente número:</p>
                        <hr>
                        <p class="mb-0">
                            <strong>N° de Trámite:</strong> {{ $tramite->id }}-{{ $tramite->periodo ?? date('Y') }}
                        </p>
                        @if($tramite->numero_voucher)
                            <p class="mb-0">
                                <strong>N° de Voucher:</strong> {{ $tramite->numero_voucher }}
                            </p>
                        @endif
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Información del Trámite</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Procedimiento:</th>
                                    <td>{{ $tramite->procedimiento->nombre ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha de Registro:</th>
                                    <td>{{ $tramite->fecha_creacion->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge bg-warning">{{ $tramite->estado }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tipo de Trámite:</th>
                                    <td>{{ $tramite->tipo_tramite ?? 'VIRTUAL' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Información del Solicitante</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nombre:</th>
                                    <td>{{ $tramite->usuario->nombres }} {{ $tramite->usuario->apellido_paterno }} {{ $tramite->usuario->apellido_materno }}</td>
                                </tr>
                                <tr>
                                    <th>DNI:</th>
                                    <td>{{ $tramite->usuario->dni }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $tramite->usuario->email }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('mesa-partes.voucher', $tramite->id) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-download"></i> Descargar Voucher
                        </a>
                        <a href="{{ route('mesa-partes.nuevo') }}" class="btn btn-secondary">
                            <i class="fas fa-plus"></i> Crear Otro Trámite
                        </a>
                        <a href="{{ route('mesa-partes.documentos-enviados') }}" class="btn btn-info">
                            <i class="fas fa-list"></i> Ver Mis Documentos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

