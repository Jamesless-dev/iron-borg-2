@extends('layouts.app')

@section('title', 'Registrar Miembro')
@section('page-title', 'Nuevo Miembro')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('miembros.store') }}" method="POST" id="formMiembro">
                    @csrf

                    {{-- BLOQUE 1: DATOS PERSONALES (Tabla 14) --}}
                    <h5 class="text-primary mb-3 fw-bold">Datos Personales</h5>
                    <div class="row g-3 mb-4">
                        
                        {{-- Campo DNI (CUS-06) --}}
                        <div class="col-md-4">
                            <label for="dni" class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                            <input type="text" 
                                    class="form-control @error('dni') is-invalid @enderror" 
                                    id="dni" 
                                    name="dni" 
                                    value="{{ old('dni') }}" 
                                    maxlength="8" 
                                    pattern="\d{8}" 
                                    placeholder="8 dígitos" 
                                    required>
                            <div id="dni-msg" class="small mt-1"></div>
                            @error('dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campo Nombre --}}
                        <div class="col-md-4">
                            <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" 
                                    class="form-control @error('nombre') is-invalid @enderror" 
                                    id="nombre" 
                                    name="nombre" 
                                    value="{{ old('nombre') }}" 
                                    required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campo Apellido --}}
                        <div class="col-md-4">
                            <label for="apellido" class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                            <input type="text" 
                                    class="form-control @error('apellido') is-invalid @enderror" 
                                    id="apellido" 
                                    name="apellido" 
                                    value="{{ old('apellido') }}" 
                                    required>
                            @error('apellido')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campo Teléfono --}}
                        <div class="col-md-4">
                            <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                            <input type="text" 
                                    class="form-control @error('telefono') is-invalid @enderror" 
                                    id="telefono" 
                                    name="telefono" 
                                    value="{{ old('telefono') }}" 
                                    maxlength="9" 
                                    pattern="\d{9}">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- BLOQUE 2: MEMBRESÍA (CUS-07) --}}
                    <h5 class="text-primary mb-3 fw-bold">Membresía</h5>
                    <div class="row g-3 mb-4">
                        
                        {{-- Lista de Planes --}}
                        <div class="col-md-6">
                            <label for="idPlan" class="form-label fw-semibold">Plan de Membresía <span class="text-danger">*</span></label>
                            <select class="form-select @error('idPlan') is-invalid @enderror" id="idPlan" name="idPlan" required>
                                <option value="">Seleccione un plan...</option>
                                @foreach($planes as $plan)
                                    <option value="{{ $plan->idPlan }}" 
                                            data-duracion="{{ $plan->duracionDias }}" 
                                            data-costo="{{ $plan->costo }}"
                                            {{ old('idPlan') == $plan->idPlan ? 'selected' : '' }}>
                                        {{ $plan->nombre }} - S/ {{ number_format($plan->costo, 2) }} ({{ $plan->duracionDias }} días)
                                    </option>
                                @endforeach
                            </select>
                            @error('idPlan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fecha de Inicio --}}
                        <div class="col-md-6">
                            <label for="fechaInicio" class="form-label fw-semibold">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" 
                                    class="form-control @error('fechaInicio') is-invalid @enderror" 
                                    id="fechaInicio" 
                                    name="fechaInicio" 
                                    value="{{ old('fechaInicio', date('Y-m-d')) }}" 
                                    min="{{ date('Y-m-d') }}" 
                                    required>
                            @error('fechaInicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Costo (Solo lectura - CUS-07 paso 2) --}}
                        <div class="col-md-6">
                            <label for="costo" class="form-label fw-semibold">Costo Total</label>
                            <input type="text" 
                                    class="form-control bg-light" 
                                    id="costo" 
                                    value="S/ 0.00" 
                                    readonly>
                        </div>

                        {{-- Fecha de Fin (Solo lectura - CUS-07 paso 2) --}}
                        <div class="col-md-6">
                            <label for="fechaFin" class="form-label fw-semibold">Fecha de Fin</label>
                            <input type="text" 
                                    class="form-control bg-light" 
                                    id="fechaFin" 
                                    value="--" 
                                    readonly>
                            </div>
                    </div>

                    {{-- BOTONES DE ACCIÓN (Tabla 14) --}}
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4" id="btnRegistrar" disabled>
                            Registrar Miembro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const dniInput = document.getElementById('dni');
    const dniMsg = document.getElementById('dni-msg');
    const btnRegistrar = document.getElementById('btnRegistrar');

    dniInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        dniMsg.textContent = '';
        
        if (this.value.length === 8) {
            verificarDni(this.value);
        } else {
            btnRegistrar.disabled = true;
        }
    });

    function verificarDni(dni) {
        fetch(`/miembros/verificar-dni?dni=${dni}`)
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    dniMsg.textContent = '⚠️ El cliente ya está registrado.';
                    dniMsg.className = 'text-danger small mt-1 fw-semibold';
                    btnRegistrar.disabled = true;
                } else {
                    dniMsg.textContent = '✓ DNI disponible';
                    dniMsg.className = 'text-success small mt-1 fw-semibold';
                    btnRegistrar.disabled = false;
                }
            })
            .catch(() => {
                dniMsg.textContent = 'Error al verificar el DNI.';
                dniMsg.className = 'text-danger small mt-1';
            });
    }

    const selectPlan = document.getElementById('idPlan');
    const inputFechaInicio = document.getElementById('fechaInicio');
    const inputFechaFin = document.getElementById('fechaFin');
    const inputCosto = document.getElementById('costo');

    function calcularDatosMembresia() {
        const planId = selectPlan.value;
        const fechaInicio = inputFechaInicio.value;

        if (planId && fechaInicio) {
            const option = selectPlan.options[selectPlan.selectedIndex];
            const dias = parseInt(option.dataset.duracion);
            const costo = parseFloat(option.dataset.costo);

            const fecha = new Date(fechaInicio + 'T00:00:00'); 
            fecha.setDate(fecha.getDate() + dias);
            
            const yyyy = fecha.getFullYear();
            const mm = String(fecha.getMonth() + 1).padStart(2, '0');
            const dd = String(fecha.getDate()).padStart(2, '0');
            
            inputFechaFin.value = `${yyyy}-${mm}-${dd}`;
            inputCosto.value = `S/ ${costo.toFixed(2)}`;
        } else {
            inputFechaFin.value = '--';
            inputCosto.value = 'S/ 0.00';
        }
    }

    selectPlan.addEventListener('change', calcularDatosMembresia);
    inputFechaInicio.addEventListener('change', calcularDatosMembresia);
</script>
@endpush