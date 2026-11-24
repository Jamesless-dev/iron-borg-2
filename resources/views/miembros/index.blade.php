{{-- resources/views/miembros/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestión de Miembros')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="page-title">Gestión de Miembros</h1>
        <p class="page-subtitle mb-0">Administra los miembros registrados en el gimnasio</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        {{-- Buscador por DNI, nombre o código (CUS-08) --}}
        <div class="input-group" style="width: 320px;">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" id="buscador" class="form-control border-start-0"
                   placeholder="Buscar por DNI, nombre o código" autocomplete="off">
        </div>
        {{-- Botón Nuevo Miembro (CUS-05) --}}
        <button class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modalMiembro"
                style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); border: none;">
            + Nuevo Miembro
        </button>
    </div>
</div>

{{-- Tabla de miembros --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background-color: #f8f9fa;">
                    <tr class="text-center">
                        <th>Código</th>
                        <th>DNI</th>
                        <th>Nombre Completo</th>
                        <th>Teléfono</th>
                        <th>Membresía</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaMiembros">
                    @forelse($miembros as $m)
                        <tr class="text-center">
                            <td>M-{{ str_pad($m->idMiembro, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $m->dni }}</td>
                            <td class="text-start">{{ $m->nombre }} {{ $m->apellido }}</td>
                            <td>{{ $m->telefono ?? '—' }}</td>
                            <td>{{ $m->membresiaActual->plan->nombre ?? 'Sin membresía' }}</td>
                            <td>
                                @if($m->membresiaActual && $m->membresiaActual->estado === 'Activa')
                                    <span class="badge bg-success">Activa</span>
                                @elseif($m->membresiaActual && $m->membresiaActual->estado === 'Vencida')
                                    <span class="badge bg-warning text-dark">Vencida</span>
                                @elseif($m->membresiaActual && $m->membresiaActual->estado === 'Cancelada')
                                    <span class="badge bg-danger">Cancelada</span>
                                @else
                                    <span class="badge bg-secondary">Sin membresía</span>
                                @endif
                            </td>
                            <td>
                                {{-- Extensión E-01 (3.1.1.2): editar datos personales --}}
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-editar
                                        data-miembro="{{ $m->idMiembro }}"
                                        data-dni="{{ $m->dni }}"
                                        data-nombre="{{ $m->nombre }}"
                                        data-apellido="{{ $m->apellido }}"
                                        data-telefono="{{ $m->telefono }}">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Tabla sin contenido</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: Registrar Miembro (CUS-05, CUS-06, CUS-07) — sin Correo --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalMiembro" tabindex="-1" aria-labelledby="tituloRegistro" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="tituloRegistro">
                        <i class="bi bi-person-plus-fill text-primary"></i> Registrar miembro
                    </h5>
                    <small class="text-muted">Los campos con * son obligatorios</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-3">
                <form action="{{ route('miembros.store') }}" method="POST" id="formMiembro">
                    @csrf
                    <input type="hidden" name="_form" value="registro">

                    <div class="row g-3">
                        {{-- DNI (CUS-06 · Lab 14 D-002: 8 dígitos) --}}
                        <div class="col-md-6">
                            <label for="dni" class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-hash"></i></span>
                                <input type="text" name="dni" id="dni" class="form-control"
                                       maxlength="8" inputmode="numeric" autocomplete="off"
                                       placeholder="8 dígitos" value="{{ old('_form') === 'registro' ? old('dni') : '' }}" required>
                            </div>
                            <div id="dni-msg" class="small mt-1"></div>
                            @error('dni')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="col-md-6">
                            <label for="telefonoRegistro" class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telefono" id="telefonoRegistro" class="form-control"
                                       maxlength="9" inputmode="numeric" autocomplete="off"
                                       placeholder="9 dígitos" value="{{ old('_form') === 'registro' ? old('telefono') : '' }}" required>
                            </div>
                            @error('telefono')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        {{-- Nombres --}}
                        <div class="col-md-6">
                            <label for="nombreRegistro" class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input type="text" name="nombre" id="nombreRegistro" class="form-control"
                                       maxlength="100" value="{{ old('_form') === 'registro' ? old('nombre') : '' }}" required>
                            </div>
                            @error('nombre')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        {{-- Apellidos --}}
                        <div class="col-md-6">
                            <label for="apellidoRegistro" class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input type="text" name="apellido" id="apellidoRegistro" class="form-control"
                                       maxlength="100" value="{{ old('_form') === 'registro' ? old('apellido') : '' }}" required>
                            </div>
                            @error('apellido')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        {{-- Plan de membresía (CUS-07) --}}
                        <div class="col-12">
                            <label for="planRegistro" class="form-label fw-semibold">Plan de membresía <span class="text-danger">*</span></label>
                            <select name="idPlan" id="planRegistro" class="form-select" required>
                                <option value="">Seleccione un plan</option>
                                @foreach($planes as $p)
                                    <option value="{{ $p->idPlan }}"
                                        {{ (old('_form') === 'registro' && old('idPlan') == $p->idPlan) ? 'selected' : '' }}>
                                        {{ $p->nombre }} - S/ {{ number_format($p->costo, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">
                                La membresía empieza hoy como Activa y el costo queda como saldo pendiente.
                            </small>
                            @error('idPlan')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn px-4 text-white" id="btnGuardar" disabled
                                style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); border: none;">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: Editar datos personales (Extensión E-01 · 3.1.1.2)     --}}
{{-- Solo datos personales: la membresía se gestiona en Membresías --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalEditarMiembro" tabindex="-1" aria-labelledby="tituloEdicion" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <form id="formEditarMiembro" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edicion">
                <input type="hidden" name="editar_id" id="eIdMiembro">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="tituloEdicion">
                            <i class="bi bi-pencil-square text-primary"></i> Editar miembro
                            <span class="text-muted fs-6 fw-normal" id="eCodigo"></span>
                        </h5>
                        <small class="text-muted">Solo datos personales. La membresía se gestiona en el módulo Membresías.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3">
                    <div class="row g-3">
                        {{-- DNI (único, excluyendo al propio miembro · RF-05) --}}
                        <div class="col-md-6">
                            <label for="eDni" class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-hash"></i></span>
                                <input type="text" name="dni" id="eDni" class="form-control"
                                       maxlength="8" inputmode="numeric" autocomplete="off" required>
                            </div>
                            <div id="eDniMsg" class="small mt-1"></div>
                            @error('dni')
                                @if(old('_form') === 'edicion')<div class="text-danger small">{{ $message }}</div>@endif
                            @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="col-md-6">
                            <label for="eTelefono" class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telefono" id="eTelefono" class="form-control"
                                       maxlength="9" inputmode="numeric" autocomplete="off" required>
                            </div>
                            @error('telefono')
                                @if(old('_form') === 'edicion')<div class="text-danger small">{{ $message }}</div>@endif
                            @enderror
                        </div>

                        {{-- Nombres --}}
                        <div class="col-md-6">
                            <label for="eNombre" class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input type="text" name="nombre" id="eNombre" class="form-control" maxlength="100" required>
                            </div>
                            @error('nombre')
                                @if(old('_form') === 'edicion')<div class="text-danger small">{{ $message }}</div>@endif
                            @enderror
                        </div>

                        {{-- Apellidos --}}
                        <div class="col-md-6">
                            <label for="eApellido" class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input type="text" name="apellido" id="eApellido" class="form-control" maxlength="100" required>
                            </div>
                            @error('apellido')
                                @if(old('_form') === 'edicion')<div class="text-danger small">{{ $message }}</div>@endif
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnActualizar" class="btn text-white px-4"
                            style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); border: none;">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ===== Utilidades =====
    const $ = id => document.getElementById(id);
    // Escapado anti-XSS para todo dato pintado con innerHTML (Lab 14 · S-02)
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));
    const soloDigitos = input => { input.value = input.value.replace(/\D/g, ''); };
    const codigo = id => 'M-' + String(id).padStart(4, '0');

    // ===== Registro (CUS-05 / CUS-06) =====
    const modalRegistro   = new bootstrap.Modal($('modalMiembro'));
    const dniInput        = $('dni');
    const dniMsg          = $('dni-msg');
    const btnGuardar      = $('btnGuardar');
    const telefonoRegistro = $('telefonoRegistro');

    function verificarDniRegistro() {
        fetch(`/miembros/verificar-dni?dni=${encodeURIComponent(dniInput.value)}`)
            .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    dniMsg.innerHTML = '<span class="text-danger fw-semibold">⚠ El cliente ya está registrado</span>';
                    btnGuardar.disabled = true;
                } else {
                    dniMsg.innerHTML = '<span class="text-success fw-semibold">✓ DNI disponible</span>';
                    btnGuardar.disabled = false;
                }
            })
            .catch(() => {
                dniMsg.innerHTML = '<span class="text-danger">No se pudo verificar el DNI. Intente nuevamente.</span>';
                btnGuardar.disabled = true;
            });
    }

    dniInput.addEventListener('input', function () {
        soloDigitos(this);
        dniMsg.textContent = '';
        btnGuardar.disabled = true;

        if (this.value.length === 8) {
            verificarDniRegistro();
        } else if (this.value.length > 0) {
            dniMsg.innerHTML = '<span class="text-danger">El DNI debe tener 8 dígitos</span>';
        }
    });

    telefonoRegistro.addEventListener('input', function () { soloDigitos(this); });

    // ===== Edición (Extensión E-01 · 3.1.1.2) =====
    const modalEdicion  = new bootstrap.Modal($('modalEditarMiembro'));
    const formEditar    = $('formEditarMiembro');
    const eDni          = $('eDni');
    const eDniMsg       = $('eDniMsg');
    const eTelefono     = $('eTelefono');
    const btnActualizar = $('btnActualizar');
    let idEditando = null;
    let dniOriginal = null;

    function abrirEdicion(d) {
        idEditando  = d.miembro;
        dniOriginal = d.dni;

        formEditar.action = `/miembros/${d.miembro}`;
        $('eIdMiembro').value = d.miembro;
        $('eCodigo').textContent = '(' + codigo(d.miembro) + ')';
        eDni.value          = d.dni ?? '';
        $('eNombre').value  = d.nombre ?? '';
        $('eApellido').value = d.apellido ?? '';
        eTelefono.value     = d.telefono ?? '';
        eDniMsg.textContent = '';
        btnActualizar.disabled = false;

        modalEdicion.show();
    }

    // Delegación: funciona también en filas renderizadas por el buscador
    $('tablaMiembros').addEventListener('click', e => {
        const btn = e.target.closest('[data-editar]');
        if (btn) abrirEdicion(btn.dataset);
    });

    eDni.addEventListener('input', function () {
        soloDigitos(this);
        eDniMsg.textContent = '';
        btnActualizar.disabled = true;

        if (this.value.length !== 8) {
            if (this.value.length > 0) {
                eDniMsg.innerHTML = '<span class="text-danger">El DNI debe tener 8 dígitos</span>';
            }
            return;
        }

        if (this.value === dniOriginal) {
            btnActualizar.disabled = false; // su propio DNI no es duplicado
            return;
        }

        fetch(`/miembros/verificar-dni?dni=${encodeURIComponent(this.value)}&excepto=${encodeURIComponent(idEditando)}`)
            .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    eDniMsg.innerHTML = '<span class="text-danger fw-semibold">⚠ Este DNI ya pertenece a otro miembro</span>';
                    btnActualizar.disabled = true;
                } else {
                    eDniMsg.innerHTML = '<span class="text-success fw-semibold">✓ DNI disponible</span>';
                    btnActualizar.disabled = false;
                }
            })
            .catch(() => {
                eDniMsg.innerHTML = '<span class="text-danger">No se pudo verificar el DNI. Intente nuevamente.</span>';
                btnActualizar.disabled = true;
            });
    });

    eTelefono.addEventListener('input', function () { soloDigitos(this); });

    // ===== Buscador en tiempo real (CUS-08) =====
    const tbody = $('tablaMiembros');
    const filasIniciales = tbody.innerHTML;
    let temporizador = null;

    function badgeEstado(estado) {
        if (estado === 'Activa')    return '<span class="badge bg-success">Activa</span>';
        if (estado === 'Vencida')   return '<span class="badge bg-warning text-dark">Vencida</span>';
        if (estado === 'Cancelada') return '<span class="badge bg-danger">Cancelada</span>';
        return '<span class="badge bg-secondary">Sin membresía</span>';
    }

    function renderTabla(lista) {
        if (!lista.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">No se encontró ningún miembro con ese dato</td></tr>';
            return;
        }
        tbody.innerHTML = lista.map(m => {
            const mem = m.membresia_actual ?? m.membresiaActual ?? null;
            return `
                <tr class="text-center">
                    <td>${esc(codigo(m.idMiembro))}</td>
                    <td>${esc(m.dni)}</td>
                    <td class="text-start">${esc(m.nombre)} ${esc(m.apellido)}</td>
                    <td>${esc(m.telefono || '—')}</td>
                    <td>${esc(mem?.plan?.nombre ?? 'Sin membresía')}</td>
                    <td>${badgeEstado(mem?.estado)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-editar
                                data-miembro="${esc(m.idMiembro)}"
                                data-dni="${esc(m.dni)}"
                                data-nombre="${esc(m.nombre)}"
                                data-apellido="${esc(m.apellido)}"
                                data-telefono="${esc(m.telefono ?? '')}">
                            <i class="bi bi-pencil"></i> Editar
                        </button>
                    </td>
                </tr>`;
        }).join('');
    }

    $('buscador').addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(temporizador);

        if (q === '') {
            tbody.innerHTML = filasIniciales;
            return;
        }
        // CUS-08 FA-02: al menos 3 letras o un DNI/código numérico
        if (q.length < 3 && !/^\d+$/.test(q)) {
            tbody.innerHTML = filasIniciales;
            return;
        }

        temporizador = setTimeout(() => {
            fetch(`/miembros/buscar?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(renderTabla)
                .catch(() => { tbody.innerHTML = filasIniciales; });
        }, 250);
    });

    // ===== Reabrir el modal correcto si la validación del servidor falló =====
    @if($errors->any())
        @if(old('_form') === 'edicion')
            abrirEdicion({
                miembro:  @json(old('editar_id')),
                dni:      @json(old('dni')),
                nombre:   @json(old('nombre')),
                apellido: @json(old('apellido')),
                telefono: @json(old('telefono'))
            });
            dniOriginal = null; // fuerza re-verificación del DNI editado
            eDni.dispatchEvent(new Event('input'));
        @else
            modalRegistro.show();
            if (dniInput.value.length === 8) verificarDniRegistro();
        @endif
    @endif
})();
</script>
@endpush