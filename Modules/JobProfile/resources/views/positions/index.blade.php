@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Códigos de Posición</h2>
                @can('create', \Modules\JobProfile\Entities\PositionCode::class)
                    <a href="{{ route('jobprofile.positions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Código
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if($positionCodes->isEmpty())
                        <div class="alert alert-info">
                            No hay códigos de posición registrados.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Salario Base</th>
                                        <th>EsSalud (9%)</th>
                                        <th>Total Mensual</th>
                                        <th>Meses</th>
                                        <th>Total Periodo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($positionCodes as $code)
                                        <tr>
                                            <td><code>{{ $code->code }}</code></td>
                                            <td>{{ $code->name }}</td>
                                            <td>{{ $code->formatted_base_salary }}</td>
                                            <td class="text-muted">{{ number_format($code->essalud_amount, 2) }}</td>
                                            <td><strong>{{ $code->formatted_monthly_total }}</strong></td>
                                            <td>{{ $code->contract_months }}</td>
                                            <td><strong class="text-primary">{{ $code->formatted_quarterly_total }}</strong></td>
                                            <td>
                                                @if($code->is_active)
                                                    <span class="badge badge-success">Activo</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @can('view', $code)
                                                        <a href="{{ route('jobprofile.positions.show', $code->id) }}"
                                                           class="btn btn-sm btn-info" title="Ver">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endcan

                                                    @can('update', $code)
                                                        <a href="{{ route('jobprofile.positions.edit', $code->id) }}"
                                                           class="btn btn-sm btn-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan

                                                    @can('toggleStatus', $code)
                                                        @if($code->is_active)
                                                            <form action="{{ route('jobprofile.positions.deactivate', $code->id) }}"
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-secondary"
                                                                        title="Desactivar"
                                                                        onclick="return confirm('¿Desactivar código?')">
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form action="{{ route('jobprofile.positions.activate', $code->id) }}"
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success"
                                                                        title="Activar">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
