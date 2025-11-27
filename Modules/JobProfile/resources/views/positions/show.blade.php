@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Detalle del Código de Posición</h2>
                <div>
                    @can('update', $positionCode)
                        <a href="{{ route('jobprofile.positions.edit', $positionCode->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    @endcan
                    <a href="{{ route('jobprofile.positions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Información General</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="font-weight-bold">Código:</label>
                            <p><code class="h5">{{ $positionCode->code }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold">Estado:</label>
                            <p>
                                @if($positionCode->is_active)
                                    <span class="badge badge-success badge-lg">Activo</span>
                                @else
                                    <span class="badge badge-secondary badge-lg">Inactivo</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="font-weight-bold">Nombre del Puesto:</label>
                            <p class="h5">{{ $positionCode->name }}</p>
                        </div>
                    </div>

                    @if($positionCode->description)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="font-weight-bold">Descripción:</label>
                                <p>{{ $positionCode->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="mb-0">Información Salarial</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td class="font-weight-bold" width="40%">Salario Base</td>
                                <td>{{ $positionCode->formatted_base_salary }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Porcentaje EsSalud</td>
                                <td>{{ number_format($positionCode->essalud_percentage, 2) }}%</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Monto EsSalud</td>
                                <td>S/ {{ number_format($positionCode->essalud_amount, 2) }}</td>
                            </tr>
                            <tr class="table-primary">
                                <td class="font-weight-bold">Total Mensual</td>
                                <td class="h5 mb-0">{{ $positionCode->formatted_monthly_total }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Meses de Contrato</td>
                                <td>{{ $positionCode->contract_months }} meses</td>
                            </tr>
                            <tr class="table-success">
                                <td class="font-weight-bold">Total por Periodo</td>
                                <td class="h4 mb-0 text-primary">{{ $positionCode->formatted_quarterly_total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($positionCode->jobProfiles->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="mb-0">Perfiles de Puesto Asociados</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Título</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($positionCode->jobProfiles as $profile)
                                        <tr>
                                            <td><code>{{ $profile->code }}</code></td>
                                            <td>{{ $profile->title }}</td>
                                            <td>{!! $profile->status_badge !!}</td>
                                            <td>{{ $profile->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('jobprofile.show', $profile->id) }}"
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    @can('toggleStatus', $positionCode)
                        @if($positionCode->is_active)
                            <form action="{{ route('jobprofile.positions.deactivate', $positionCode->id) }}"
                                  method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-block"
                                        onclick="return confirm('¿Está seguro de desactivar este código de posición?')">
                                    <i class="fas fa-ban"></i> Desactivar Código
                                </button>
                            </form>
                        @else
                            <form action="{{ route('jobprofile.positions.activate', $positionCode->id) }}"
                                  method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-check"></i> Activar Código
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Información del Registro</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Creado:</strong><br>
                        {{ $positionCode->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="mb-0">
                        <strong>Última modificación:</strong><br>
                        {{ $positionCode->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Estadísticas</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Perfiles Asociados:</strong>
                        <span class="badge badge-info">{{ $positionCode->jobProfiles->count() }}</span>
                    </p>
                    <p class="mb-0">
                        <strong>Perfiles Activos:</strong>
                        <span class="badge badge-success">
                            {{ $positionCode->jobProfiles->where('status', 'approved')->count() }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
