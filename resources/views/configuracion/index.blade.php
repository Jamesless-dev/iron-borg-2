@extends('layouts.app')
@section('title', 'Configuración')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Configuración del Sistema</h1>
        <p class="page-subtitle mb-0">Gestione los planes de membresía y parámetros del sistema</p>
    </div>
</div>

<div class="row">
    {{-- SECCIÓN 1: PLANES DE MEMBRESÍA (CUS-19) --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-credit-card-2-front text-primary"></i> Planes de Membresía
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                {{-- Formulario para crear nuevo plan --}}
                <form action="{{ route('configuracion.storePlan') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nombre del Plan <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Estudiantes" required>
                            @error('nombre')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Duración (días) <span class="text-danger">*</span></label>
                            <input type="number" name="duracionDias" class="form-control" placeholder="30" required>
                            @error('duracionDias')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Costo (S/) <span class="text-danger">*</span></label>
                            <input type="number" name="costo" class="form-control" step="0.01" placeholder="50.00" required>
                            @error('costo')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg"></i> Crear
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Tabla de planes existentes --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th>Nombre</th>
                                <th>Duración</th>
                                <th>Costo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($planes as $plan)
                            <tr>
                                <td class="fw-semibold">{{ $plan->nombre }}</td>
                                <td>{{ $plan->duracionDias }} días</td>
                                <td>S/ {{ number_format($plan->costo, 2) }}</td>
                                <td>
                                    @if($plan->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- Botón para activar/desactivar --}}
                                        <form action="{{ route('configuracion.togglePlan', $plan->idPlan) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $plan->activo ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                {{ $plan->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>

                                        {{-- Botón para editar (abre modal) --}}
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarPlan{{ $plan->idPlan }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal para editar plan --}}
                            <div class="modal fade" id="modalEditarPlan{{ $plan->idPlan }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('configuracion.updatePlan', $plan->idPlan) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Plan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Nombre</label>
                                                    <input type="text" name="nombre" class="form-control" value="{{ $plan->nombre }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Duración (días)</label>
                                                    <input type="number" name="duracionDias" class="form-control" value="{{ $plan->duracionDias }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Costo (S/)</label>
                                                    <input type="number" name="costo" class="form-control" step="0.01" value="{{ $plan->costo }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay planes configurados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 2: PARÁMETROS DEL SISTEMA (CUS-19) --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-gear text-primary"></i> Parámetros del Sistema
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('configuracion.updateParametros') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Intentos máximos de login <span class="text-danger">*</span></label>
                        <input type="number" name="intentosMaximos" class="form-control" 
                            value="{{ $parametros->intentosMaximos ?? 3 }}" min="1" max="10" required>
                        <small class="text-muted">Número de intentos antes de bloquear la cuenta (RNF-08)</small>
                        @error('intentosMaximos')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Minutos de inactividad <span class="text-danger">*</span></label>
                        <input type="number" name="minutosInactividad" class="form-control" 
                            value="{{ $parametros->minutosInactividad ?? 15 }}" min="1" required>
                        <small class="text-muted">Tiempo antes de cerrar sesión automáticamente (RNF-01)</small>
                        @error('minutosInactividad')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Guardar parámetros
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection