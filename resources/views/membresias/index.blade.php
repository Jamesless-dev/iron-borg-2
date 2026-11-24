@extends('layouts.app')

@section('title', 'Gestión de Membresías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="page-title">Gestión de Membresías</h1>
        <p class="page-subtitle mb-0">Renueve o cancele el periodo de cada miembro</p>
    </div>

    <div class="d-flex gap-2 align-items-center">
        {{-- CUS-08 paso 1: campo «Buscar membresía...» --}}
        <form action="{{ route('membresias.index') }}" method="GET" class="d-flex">
            <div class="input-group" style="width: 320px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="q" value="{{ $q }}" class="form-control border-start-0"
                       placeholder="Buscar membresía (DNI, nombre o código)">
            </div>
            <button class="btn btn-outline-secondary ms-2">Buscar</button>
        </form>

        {{-- Tabla 15: botón «+ Nueva Membresía» → CUS-09 paso 1 --}}
        <button id="btnNueva" class="btn text-white px-3"
                style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); border: none;">
            + Nueva Membresía
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background-color: #f8f9fa;">
                    <tr class="text-center">
                        <th>Código</th>
                        <th>DNI</th>
                        <th>Miembro</th>
                        <th>Plan</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($miembros as $m)
                    @php
                        $mem = $ultimas[$m->idMiembro] ?? null;
                        $nuevaInicio = ($mem && $mem->estado === 'Activa' && $mem->fechaFin->gte(today()))
                            ? $mem->fechaFin->copy()->addDay()
                            : today();
                    @endphp
                    <tr class="text-center">
                        <td>M-{{ str_pad($m->idMiembro, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $m->dni }}</td>
                        <td class="text-start">{{ $m->nombre }} {{ $m->apellido }}</td>
                        <td>{{ $mem?->plan?->nombre ?? 'Sin membresía' }}</td>
                        <td>{{ $mem?->fechaInicio?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $mem?->fechaFin?->format('d/m/Y') ?? '—' }}</td>
                        <td>S/ {{ $mem ? number_format($mem->saldoPendiente, 2) : '0.00' }}</td>
                        <td>
                            @if($mem && $mem->estado === 'Activa')
                                <span class="badge bg-success">Activa</span>
                            @elseif($mem && $mem->estado === 'Vencida')
                                <span class="badge bg-warning text-dark">Vencida</span>
                            @elseif($mem && $mem->estado === 'Cancelada')
                                <span class="badge bg-danger">Cancelada</span>
                            @else
                                <span class="badge bg-secondary">Sin membresía</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                {{-- CUS-09 --}}
                                <button class="btn btn-sm btn-outline-primary"
                                        data-renovar
                                        data-miembro="{{ $m->idMiembro }}"
                                        data-nombre="{{ $m->nombre }} {{ $m->apellido }}"
                                        data-dni="{{ $m->dni }}"
                                        data-plan="{{ $mem?->plan?->nombre ?? 'Sin membresía' }}"
                                        data-estado="{{ $mem?->estado ?? 'Sin membresía' }}"
                                        data-inicio="{{ $mem?->fechaInicio?->format('d/m/Y') ?? '—' }}"
                                        data-fin="{{ $mem?->fechaFin?->format('d/m/Y') ?? '—' }}"
                                        data-saldo="{{ $mem ? number_format($mem->saldoPendiente, 2) : '0.00' }}"
                                        data-nueva-inicio="{{ $nuevaInicio->toDateString() }}">
                                    Renovar
                                </button>

                                {{-- CUS-10: solo Administrador (RNF-02) --}}
                                @if(auth()->user()->rol === 'Administrador' && $mem && $mem->estado !== 'Cancelada')
                                    <button class="btn btn-sm btn-outline-danger"
                                            data-cancelar
                                            data-membresia="{{ $mem->idMembresia }}"
                                            data-nombre="{{ $m->nombre }} {{ $m->apellido }}">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            {{ $q !== '' ? 'No se encontró ningún miembro con ese dato' : 'Tabla sin contenido' }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===== MODAL CUS-09: Renovar / Nueva Membresía ===== --}}
<div class="modal fade" id="modalRenovar" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <form id="formRenovar" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-arrow-repeat text-primary"></i> Nueva membresía / Renovación
                        </h5>
                        <small class="text-muted">El sistema calcula la vigencia según el plan (RN-CA-02)</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-3">
                    {{-- Buscador (solo al abrir con «+ Nueva Membresía») --}}
                    <div id="bloqueBusqueda" class="mb-3 d-none">
                        <label class="form-label fw-semibold">Buscar miembro <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="inputBuscar" class="form-control" placeholder="DNI, nombre o código">
                        </div>
                        <div id="resultados" class="mt-2"></div>
                    </div>

                    {{-- CUS-09 paso 3: membresía actual --}}
                    <div id="infoActual" class="border rounded p-3 mb-3 bg-light d-none">
                        <div class="fw-bold mb-2" id="lblNombre">—</div>
                        <div class="row small text-muted">
                            <div class="col-md-4">DNI: <span id="lblDni">—</span></div>
                            <div class="col-md-4">Plan actual: <span id="lblPlan">—</span></div>
                            <div class="col-md-4">Estado: <span id="lblEstado">—</span></div>
                            <div class="col-md-4">Inicio: <span id="lblInicio">—</span></div>
                            <div class="col-md-4">Fin: <span id="lblFin">—</span></div>
                            <div class="col-md-4">Saldo: S/ <span id="lblSaldo">—</span></div>
                        </div>
                    </div>

                    {{-- CUS-09 FA-02 --}}
                    <div id="alertaCancelada" class="alert alert-warning small d-none">
                        La membresía está <strong>Cancelada</strong>: se creará una nueva membresía desde la fecha actual.
                        Confirme para continuar.
                    </div>

                    <div id="camposPlan" class="row g-3 d-none">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Plan <span class="text-danger">*</span></label>
                            <select name="idPlan" id="selectPlan" class="form-select" required>
                                <option value="">Seleccione un plan</option>
                                @foreach($planes as $p)
                                    <option value="{{ $p->idPlan }}"
                                            data-duracion="{{ $p->duracionDias }}"
                                            data-costo="{{ $p->costo }}">
                                        {{ $p->nombre }} · {{ $p->duracionDias }} días · S/ {{ number_format($p->costo, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nueva fecha de inicio</label>
                            <input type="text" id="nuevaInicio" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nueva fecha de fin</label>
                            <input type="text" id="nuevaFin" class="form-control bg-light" readonly value="—">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Costo / Saldo pendiente</label>
                            <input type="text" id="nuevoCosto" class="form-control bg-light" readonly value="S/ 0.00">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnConfirmar" class="btn text-white px-4" disabled
                            style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); border: none;">
                        Confirmar renovación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL CUS-10: Cancelar ===== --}}
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <form id="formCancelar" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-x-circle"></i> Cancelar membresía
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <p class="mb-2">Miembro: <strong id="lblCancelarNombre">—</strong></p>
                    <div class="alert alert-warning small">
                        La membresía pasará a <strong>Cancelada</strong>. No se elimina el registro (RN-ES-01)
                        y el cambio queda en el historial con su motivo (CUS-11).
                    </div>
                    <label class="form-label fw-semibold">Motivo <span class="text-danger">*</span></label>
                    <textarea name="motivo" class="form-control" rows="3" maxlength="255" required
                              placeholder="Ej.: solicitud del cliente"></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger px-4">Confirmar cancelación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const modalRenovar  = new bootstrap.Modal(document.getElementById('modalRenovar'));
const modalCancelar = new bootstrap.Modal(document.getElementById('modalCancelar'));

const formRenovar   = document.getElementById('formRenovar');
const bloqueBusqueda= document.getElementById('bloqueBusqueda');
const infoActual    = document.getElementById('infoActual');
const camposPlan    = document.getElementById('camposPlan');
const alertaCancel  = document.getElementById('alertaCancelada');
const selectPlan    = document.getElementById('selectPlan');
const btnConfirmar  = document.getElementById('btnConfirmar');
const inputBuscar   = document.getElementById('inputBuscar');
const resultados    = document.getElementById('resultados');

function resetRenovar() {
    formRenovar.action = '';
    selectPlan.value = '';
    document.getElementById('nuevaFin').value = '—';
    document.getElementById('nuevoCosto').value = 'S/ 0.00';
    resultados.innerHTML = '';
    inputBuscar.value = '';
    alertaCancel.classList.add('d-none');
    btnConfirmar.disabled = true;
}

function cargarMiembro(d) {
    formRenovar.action = `/membresias/${d.idMiembro}/renovar`;
    document.getElementById('lblNombre').textContent = `${d.nombre} (${d.codigo})`;
    document.getElementById('lblDni').textContent    = d.dni;
    document.getElementById('lblPlan').textContent   = d.plan;
    document.getElementById('lblEstado').textContent = d.estado;
    document.getElementById('lblInicio').textContent = d.inicio;
    document.getElementById('lblFin').textContent    = d.fin;
    document.getElementById('lblSaldo').textContent  = d.saldo;
    document.getElementById('nuevaInicio').value     = d.nuevaInicio;

    infoActual.classList.remove('d-none');
    camposPlan.classList.remove('d-none');
    bloqueBusqueda.classList.add('d-none');
    alertaCancel.classList.toggle('d-none', d.estado !== 'Cancelada');
    calcularFin();
}

// RN-CA-02: fechaFin = fechaInicio + duración del plan
function calcularFin() {
    const opt = selectPlan.options[selectPlan.selectedIndex];
    const inicio = document.getElementById('nuevaInicio').value;
    if (!opt || !opt.value || !inicio) {
        document.getElementById('nuevaFin').value = '—';
        document.getElementById('nuevoCosto').value = 'S/ 0.00';
        btnConfirmar.disabled = true;
        return;
    }
    const fecha = new Date(inicio + 'T00:00:00');
    fecha.setDate(fecha.getDate() + parseInt(opt.dataset.duracion));
    const yyyy = fecha.getFullYear();
    const mm = String(fecha.getMonth() + 1).padStart(2, '0');
    const dd = String(fecha.getDate()).padStart(2, '0');
    document.getElementById('nuevaFin').value = `${dd}/${mm}/${yyyy}`;
    document.getElementById('nuevoCosto').value = 'S/ ' + parseFloat(opt.dataset.costo).toFixed(2);
    btnConfirmar.disabled = false;
}
selectPlan.addEventListener('change', calcularFin);

// Botones «Renovar» de cada fila
document.querySelectorAll('[data-renovar]').forEach(btn => {
    btn.addEventListener('click', () => {
        resetRenovar();
        const d = btn.dataset;
        cargarMiembro({
            idMiembro: d.miembro,
            codigo: 'M-' + String(d.miembro).padStart(4, '0'),
            nombre: d.nombre, dni: d.dni, plan: d.plan, estado: d.estado,
            inicio: d.inicio, fin: d.fin, saldo: d.saldo, nuevaInicio: d.nuevaInicio
        });
        modalRenovar.show();
    });
});

// Botón «+ Nueva Membresía» (Tabla 15 → CUS-09 paso 1)
document.getElementById('btnNueva').addEventListener('click', () => {
    resetRenovar();
    infoActual.classList.add('d-none');
    camposPlan.classList.add('d-none');
    bloqueBusqueda.classList.remove('d-none');
    modalRenovar.show();
    setTimeout(() => inputBuscar.focus(), 400);
});

// CUS-08: búsqueda dentro del modal
inputBuscar.addEventListener('input', () => {
    const q = inputBuscar.value.trim();
    if (q.length < 3 && !/^\d+$/.test(q)) { resultados.innerHTML = ''; return; }

    fetch(`/membresias/buscar?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(lista => {
            if (!lista.length) {
                resultados.innerHTML = '<div class="text-muted small">No se encontró ningún miembro con ese dato</div>';
                return;
            }
            resultados.innerHTML = '';
            lista.forEach(m => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action border rounded mb-1 text-start w-100';
                item.innerHTML = `<strong>${m.nombre}</strong> · DNI ${m.dni}
                    <br><small class="text-muted">${m.plan} · ${m.estado} · vence ${m.fin}</small>`;
                item.addEventListener('click', () => cargarMiembro(m));
                resultados.appendChild(item);
            });
        });
});

// Botones «Cancelar» (CUS-10)
document.querySelectorAll('[data-cancelar]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('formCancelar').action = `/membresias/${btn.dataset.membresia}/cancelar`;
        document.getElementById('lblCancelarNombre').textContent = btn.dataset.nombre;
        modalCancelar.show();
    });
});
</script>
@endpush