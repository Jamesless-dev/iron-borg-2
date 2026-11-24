@extends('layouts.app')

@section('title', 'Control de Asistencia')

@section('content')
<style>
    .as-card{background:#fff;border:1px solid #edeff5;border-radius:16px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px -18px rgba(16,24,40,.25)}
    .as-pad{padding:26px 28px}
    .as-h{font-size:1.08rem;font-weight:700;color:#0f172a;letter-spacing:-.01em;margin:0}
    .as-icon{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:18px}
    .as-icon.blue{background:rgba(102,126,234,.12);color:#5a6fe0}
    .as-icon.green{background:rgba(16,185,129,.12);color:#0ea472}
    .as-kpi-label{font-size:.86rem;color:#6b7280;font-weight:500;margin-bottom:4px}
    .as-kpi-value{font-size:2rem;font-weight:800;color:#0f172a;line-height:1.1;letter-spacing:-.02em;font-variant-numeric:tabular-nums}
    .as-kpi{transition:transform .18s ease,box-shadow .18s ease}
    .as-kpi:hover{transform:translateY(-2px);box-shadow:0 2px 4px rgba(16,24,40,.05),0 16px 32px -20px rgba(16,24,40,.35)}

    .as-search{height:54px;border:1px solid #e4e7ef;font-size:1rem;background:#fcfcfe}
    .as-search:focus{border-color:#667EEA;box-shadow:0 0 0 4px rgba(102,126,234,.14);background:#fff}
    .as-search-ico{height:54px;background:#fcfcfe;border:1px solid #e4e7ef;border-right:0;color:#9aa1b1}
    .btn-grad{background:linear-gradient(135deg,#667EEA 0%,#764BA2 100%);border:none;color:#fff;font-weight:600;border-radius:10px;height:54px;transition:filter .16s ease,transform .16s ease,box-shadow .16s ease}
    .btn-grad:hover:not(:disabled){color:#fff;filter:brightness(1.06);box-shadow:0 8px 20px -8px rgba(102,126,234,.75)}
    .btn-grad:active:not(:disabled){transform:translateY(1px)}
    .btn-grad:disabled{opacity:.5;cursor:not-allowed}

    .as-date{height:42px;border:1px solid #e4e7ef;border-radius:10px;padding:0 12px;font-size:.9rem;color:#374151;font-weight:500;background:#fff}
    .as-date:focus{outline:none;border-color:#667EEA;box-shadow:0 0 0 4px rgba(102,126,234,.14)}
    .as-chip{border:1px solid #e4e7ef;background:#fff;color:#6b7280;border-radius:999px;padding:7px 14px;font-size:.8rem;font-weight:600;transition:all .16s ease}
    .as-chip:hover{border-color:#c7cddd;color:#374151}
    .as-chip.on{background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.4);color:#0a8f63}

    .as-table{margin:0}
    .as-table thead th{background:#fafbfd;border-top:1px solid #f0f2f7;border-bottom:1px solid #f0f2f7;font-size:.7rem;letter-spacing:.09em;text-transform:uppercase;color:#8b93a5;font-weight:700;padding:14px 20px;white-space:nowrap}
    .as-table tbody td{padding:16px 20px;border-bottom:1px solid #f4f5f9;vertical-align:middle;font-size:.92rem;color:#374151}
    .as-table tbody tr{transition:background .14s ease}
    .as-table tbody tr:hover{background:#fbfcff}
    .as-table tbody tr:last-child td{border-bottom:0}
    .as-name{font-weight:600;color:#0f172a}
    .as-sub{font-size:.76rem;color:#9aa1b1}
    .as-time{font-variant-numeric:tabular-nums;font-weight:600;color:#1f2937}

    .as-live{display:inline-flex;align-items:center;gap:7px;background:rgba(16,185,129,.1);color:#0a8f63;border-radius:999px;padding:5px 12px;font-size:.76rem;font-weight:700}
    .as-dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:asPulse 1.9s ease-in-out infinite}
    @keyframes asPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.35;transform:scale(.8)}}
    .as-done{display:inline-flex;align-items:center;gap:6px;color:#8b93a5;font-size:.8rem;font-weight:600}

    .as-out{border:1px solid #dfe3ec;background:#fff;color:#4b5563;border-radius:8px;padding:6px 13px;font-size:.8rem;font-weight:600;transition:all .16s ease}
    .as-out:hover{border-color:#764BA2;color:#764BA2;background:rgba(118,75,162,.05)}
    .as-out:disabled{opacity:.55;cursor:progress}

    .as-empty{padding:56px 20px;text-align:center;color:#9aa1b1;font-size:.94rem}
    .as-franja{border:none;border-radius:14px;font-weight:600}
    .as-divider{border-top:1px solid #f0f2f7;margin:22px 0}
    .as-mini-label{font-size:.78rem;color:#8b93a5;font-weight:600;margin-bottom:3px}
    .as-mini-value{font-size:1.05rem;font-weight:700;color:#0f172a}
    .as-fade{animation:asIn .28s cubic-bezier(.22,1,.36,1)}
    @keyframes asIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}

    .as-scroll{max-height:460px;overflow-y:auto}
    .as-scroll::-webkit-scrollbar{width:10px}
    .as-scroll::-webkit-scrollbar-track{background:#f6f7fb}
    .as-scroll::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#667EEA,#764BA2);border-radius:99px;border:3px solid #f6f7fb}
    .as-scroll::-webkit-scrollbar-thumb:hover{background:linear-gradient(180deg,#5a6fe0,#6a4292)}
    .as-scroll{scrollbar-width:thin;scrollbar-color:#764BA2 #f6f7fb}
</style>

<h1 class="page-title">Control de Asistencia</h1>
<p class="page-subtitle">Registro de ingreso de los miembros</p>

{{-- ===== Indicadores ===== --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="as-card as-kpi as-pad h-100">
            <div class="as-icon blue"><i class="bi bi-people"></i></div>
            <div class="as-kpi-label">Ingresos de hoy</div>
            <div class="as-kpi-value" id="kpiIngresos">{{ $indicadores['ingresosHoy'] }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="as-card as-kpi as-pad h-100">
            <div class="as-icon green"><i class="bi bi-person-check"></i></div>
            <div class="as-kpi-label">Miembros sin salida registrada</div>
            <div class="as-kpi-value" id="kpiSinSalida">{{ $indicadores['sinSalida'] }}</div>
        </div>
    </div>
</div>

{{-- ===== Registrar ingreso ===== --}}
<div class="as-card as-pad mb-3">
    <h2 class="as-h mb-3">Registrar ingreso</h2>
    <div class="d-flex gap-2 flex-wrap">
        <div class="input-group flex-grow-1" style="min-width:280px">
            <span class="input-group-text as-search-ico" style="border-radius:10px 0 0 10px"><i class="bi bi-search"></i></span>
            <input type="text" id="criterio" class="form-control as-search" style="border-radius:0 10px 10px 0"
                   placeholder="DNI o código del miembro (M-0001)" autocomplete="off" autofocus>
        </div>
        <button class="btn btn-grad px-4" id="btnBuscar" style="min-width:132px">
            <i class="bi bi-search me-1"></i> Buscar
        </button>
    </div>
</div>

{{-- ===== Franja verde ===== --}}
<div id="franjaVerde" class="alert alert-success as-franja as-fade text-center py-3 d-none"></div>

{{-- ===== Miembro no encontrado ===== --}}
<div id="noEncontrado" class="alert alert-warning as-franja as-fade d-none">
    <i class="bi bi-exclamation-circle me-1"></i> Miembro no encontrado.
    <a href="{{ route('miembros.index') }}" class="alert-link">Registrar nuevo miembro</a>
</div>

{{-- ===== Franja roja ===== --}}
<div id="franjaRoja" class="alert alert-danger as-franja as-fade d-none">
    <div class="mb-2" id="textoRojo"></div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('membresias.index') }}" class="btn btn-sm btn-danger">Renovar</a>
        <a href="{{ route('pagos.create') }}" class="btn btn-sm btn-outline-danger d-none" id="btnPago">Registrar pago</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCerrarAlerta">Cerrar</button>
    </div>
</div>

{{-- ===== Tarjeta del miembro ===== --}}
<div id="tarjetaMiembro" class="as-card as-pad as-fade mb-3 d-none">
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
        <div class="d-flex gap-3 align-items-center">
            <div class="as-icon blue mb-0"><i class="bi bi-person-check"></i></div>
            <div>
                <div class="as-mini-value" id="mNombre"></div>
                <div class="as-sub"><span id="mCodigo"></span> · DNI <span id="mDni"></span></div>
            </div>
        </div>
        <span class="badge" id="mEstado"></span>
    </div>

    <div class="as-divider"></div>

    <div class="row g-3">
        <div class="col-md-4"><div class="as-mini-label">Plan</div><div class="as-mini-value" id="mPlan"></div></div>
        <div class="col-md-4"><div class="as-mini-label">Fecha de fin</div><div class="as-mini-value" id="mFechaFin"></div></div>
        <div class="col-md-4"><div class="as-mini-label">Días restantes</div><div class="as-mini-value" id="mDias"></div></div>
    </div>

    <div class="as-divider"></div>

    <button class="btn btn-grad w-100" id="btnRegistrar">
        <i class="bi bi-box-arrow-in-right me-1"></i> Registrar ingreso
    </button>
    <button class="btn as-out w-100 mt-2 d-none" id="btnSalidaRapida" style="height:46px">
        <i class="bi bi-box-arrow-right me-1"></i> Registrar salida de <span id="lblSalidaRapida"></span>
    </button>
</div>

{{-- ===== Asistencias del día ===== --}}
<div class="as-card">
    <div class="as-pad pb-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <h2 class="as-h">Asistencias del día</h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="as-chip" id="chipSoloDentro">Solo en el gimnasio</button>
            <button type="button" class="as-chip" id="chipHoy">Hoy</button>
            <span class="text-muted" style="font-size:.85rem"><i class="bi bi-calendar3"></i></span>
            <input type="date" id="fechaConsulta" class="as-date" value="{{ $fecha }}" max="{{ $hoy }}">
        </div>
    </div>

    <div class="table-responsive as-scroll">
        <table class="table as-table">
            <thead>
                <tr>
                    <th>Miembro</th>
                    <th>Fecha</th>
                    <th>Hora de ingreso</th>
                    <th>Hora de salida</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyAsistencias"></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const estado = {
        fecha: @json($fecha),
        hoy: @json($hoy),
        asistencias: @json($asistencias),
        soloDentro: false,
        miembro: null,
        dentro: null,
    };

    /* ---------- Pintado ---------- */
    function pintarIndicadores(ind) {
        $('kpiIngresos').textContent = ind.ingresosHoy;
        $('kpiSinSalida').textContent = ind.sinSalida;
    }

    function pintarTabla() {
        const tbody = $('tbodyAsistencias');
        const filas = estado.soloDentro
            ? estado.asistencias.filter(a => !a.horaSalida)
            : estado.asistencias;

        if (!filas.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="as-empty">${
                estado.soloDentro
                    ? 'Ningún miembro permanece en el gimnasio en esta fecha'
                    : 'No hay asistencias en esta fecha'
            }</td></tr>`;
            return;
        }

        tbody.innerHTML = filas.map(a => `
            <tr>
                <td>
                    <div class="as-name">${esc(a.miembro)}</div>
                    <div class="as-sub">${esc(a.codigo)} · DNI ${esc(a.dni)}</div>
                </td>
                <td>${esc(a.fecha)}</td>
                <td><span class="as-time">${esc(a.horaIngreso)}</span></td>
                <td>${
                    a.horaSalida
                        ? `<span class="as-time">${esc(a.horaSalida)}</span> <span class="as-sub">· ${esc(a.permanencia)}</span>`
                        : `<span class="as-live"><span class="as-dot"></span> En el gimnasio</span>`
                }</td>
                <td class="text-end">${
                    a.horaSalida
                        ? `<span class="as-done"><i class="bi bi-check-circle"></i> Finalizada</span>`
                        : `<button type="button" class="as-out" data-salida="${esc(a.idAsistencia)}">
                               <i class="bi bi-box-arrow-right me-1"></i> Registrar salida
                           </button>`
                }</td>
            </tr>`).join('');
    }

    function sincronizarChips() {
        $('chipSoloDentro').classList.toggle('on', estado.soloDentro);
        $('chipHoy').classList.toggle('on', estado.fecha === estado.hoy);
    }

    /* ---------- Datos ---------- */
    async function refrescar(fecha) {
        try {
            const r = await fetch(`/asistencias/dia?fecha=${encodeURIComponent(fecha)}`, { headers: { 'Accept': 'application/json' } });
            if (!r.ok) return;
            const d = await r.json();
            estado.fecha = d.fecha;
            estado.asistencias = d.asistencias;
            $('fechaConsulta').value = d.fecha;
            pintarIndicadores(d.indicadores);
            pintarTabla();
            sincronizarChips();
        } catch (_) { /* la vista conserva el último estado válido */ }
    }

    /* ---------- Búsqueda / ingreso ---------- */
    function ocultarTodo() {
        ['franjaVerde', 'noEncontrado', 'franjaRoja', 'tarjetaMiembro'].forEach(i => $(i).classList.add('d-none'));
    }

    function limpiar() {
        ocultarTodo();
        estado.miembro = null;
        estado.dentro = null;
        $('criterio').value = '';
        $('criterio').focus();
    }

    async function buscar() {
        const q = $('criterio').value.trim();
        if (!q) return;
        ocultarTodo();
        $('btnBuscar').disabled = true;

        try {
            const r = await fetch(`/asistencias/identificar?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
            const d = await r.json();

            if (r.status === 404) { $('noEncontrado').classList.remove('d-none'); return; }
            if (!r.ok) { $('textoRojo').textContent = d.mensaje || 'No se pudo identificar al miembro.'; $('franjaRoja').classList.remove('d-none'); return; }

            estado.miembro = d.miembro;
            estado.dentro = d.dentro;

            $('mNombre').textContent = d.miembro.nombre;
            $('mCodigo').textContent = d.miembro.codigo;
            $('mDni').textContent = d.miembro.dni;

            const m = d.membresia;
            $('mPlan').textContent = m ? m.plan : '—';
            $('mFechaFin').textContent = m ? m.fechaFin : '—';
            $('mDias').textContent = m ? m.diasRestantes : '—';

            const badge = $('mEstado');
            badge.className = 'badge ' + (!m ? 'bg-secondary' : m.estado === 'Activa' ? 'bg-success' : m.estado === 'Vencida' ? 'bg-warning text-dark' : 'bg-danger');
            badge.textContent = m ? m.estado : 'Sin membresía';

            $('tarjetaMiembro').classList.remove('d-none');

            const rapida = $('btnSalidaRapida');
            if (d.dentro) {
                $('lblSalidaRapida').textContent = `${d.miembro.nombre} (ingresó ${d.dentro.horaIngreso})`;
                rapida.dataset.salida = d.dentro.idAsistencia;
                rapida.classList.remove('d-none');
            } else {
                rapida.classList.add('d-none');
            }

            if (d.vigente && !d.dentro) {
                $('btnRegistrar').disabled = false;
            } else {
                $('btnRegistrar').disabled = true;
                if (!d.vigente) {
                    $('textoRojo').textContent = !m ? 'El miembro no tiene membresía registrada'
                        : m.estado === 'Vencida' ? `Membresía VENCIDA el ${m.fechaFin}`
                        : 'Membresía CANCELADA';
                    $('btnPago').classList.toggle('d-none', !(m && m.saldoPendiente > 0));
                    $('franjaRoja').classList.remove('d-none');
                } else {
                    $('textoRojo').textContent = `El miembro ya está dentro del gimnasio desde las ${d.dentro.horaIngreso}. Registre su salida para habilitar un nuevo ingreso.`;
                    $('btnPago').classList.add('d-none');
                    $('franjaRoja').classList.remove('d-none');
                }
            }
        } catch (_) {
            $('textoRojo').textContent = 'No se pudo conectar con el servidor. Intente nuevamente.';
            $('franjaRoja').classList.remove('d-none');
        } finally {
            $('btnBuscar').disabled = false;
        }
    }

    async function registrarIngreso() {
        if (!estado.miembro) return;
        $('btnRegistrar').disabled = true;

        try {
            const r = await fetch('/asistencias/registrar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ idMiembro: estado.miembro.idMiembro })
            });
            const d = await r.json();

            if (d.ok) {
                const nombre = d.miembro;
                ocultarTodo();
                $('franjaVerde').textContent = `✓ Ingreso registrado – ${d.hora} · ${nombre}`;
                $('franjaVerde').classList.remove('d-none');
                estado.miembro = null;
                estado.dentro = null;
                $('criterio').value = '';
                $('criterio').focus();
                await refrescar(estado.hoy);
                setTimeout(() => $('franjaVerde').classList.add('d-none'), 4500);
            } else {
                $('textoRojo').textContent = d.mensaje;
                $('btnPago').classList.add('d-none');
                $('franjaRoja').classList.remove('d-none');
                if (d.duplicado) await refrescar(estado.fecha);
            }
        } catch (_) {
            $('textoRojo').textContent = 'No se pudo registrar el ingreso. Intente nuevamente.';
            $('franjaRoja').classList.remove('d-none');
            $('btnRegistrar').disabled = false;
        }
    }

    async function registrarSalida(id, boton) {
        if (boton) { boton.disabled = true; boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Registrando…'; }

        try {
            const r = await fetch(`/asistencias/${id}/salida`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            const d = await r.json();

            if (d.ok) {
                ocultarTodo();
                $('franjaVerde').textContent = `✓ Salida registrada – ${d.hora} · ${d.miembro} · ${d.permanencia}`;
                $('franjaVerde').classList.remove('d-none');
                estado.miembro = null;
                estado.dentro = null;
                $('criterio').value = '';
                await refrescar(estado.fecha);
                setTimeout(() => $('franjaVerde').classList.add('d-none'), 4500);
            } else {
                $('textoRojo').textContent = d.mensaje;
                $('btnPago').classList.add('d-none');
                $('franjaRoja').classList.remove('d-none');
                await refrescar(estado.fecha);
            }
        } catch (_) {
            if (boton) { boton.disabled = false; boton.innerHTML = '<i class="bi bi-box-arrow-right me-1"></i> Registrar salida'; }
            $('textoRojo').textContent = 'No se pudo registrar la salida. Intente nuevamente.';
            $('franjaRoja').classList.remove('d-none');
        }
    }

    /* ---------- Eventos ---------- */
    $('btnBuscar').addEventListener('click', buscar);
    $('criterio').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscar(); } });
    $('btnRegistrar').addEventListener('click', registrarIngreso);
    $('btnCerrarAlerta').addEventListener('click', limpiar);

    $('btnSalidaRapida').addEventListener('click', function () { registrarSalida(this.dataset.salida, this); });

    $('tbodyAsistencias').addEventListener('click', e => {
        const b = e.target.closest('[data-salida]');
        if (b) registrarSalida(b.dataset.salida, b);
    });

    $('fechaConsulta').addEventListener('change', function () {
        if (this.value) refrescar(this.value);
    });

    $('chipHoy').addEventListener('click', () => refrescar(estado.hoy));

    $('chipSoloDentro').addEventListener('click', () => {
        estado.soloDentro = !estado.soloDentro;
        sincronizarChips();
        pintarTabla();
    });

    /* ---------- Inicio ---------- */
    pintarTabla();
    sincronizarChips();
})();
</script>
@endpush