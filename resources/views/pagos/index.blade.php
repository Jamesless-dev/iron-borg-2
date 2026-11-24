@extends('layouts.app')

@section('title', 'Pagos y Comprobantes')

@section('content')
<style>
    .pg-card{background:#fff;border:1px solid #edeff5;border-radius:16px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px -18px rgba(16,24,40,.25)}
    .pg-pad{padding:24px 26px}
    .pg-h{font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 16px}
    .pg-search{height:52px;border:1px solid #e4e7ef;font-size:1rem;background:#fcfcfe}
    .pg-search:focus{border-color:#667EEA;box-shadow:0 0 0 4px rgba(102,126,234,.14);background:#fff}
    .pg-ico{height:52px;background:#fcfcfe;border:1px solid #e4e7ef;border-right:0;color:#9aa1b1}
    .btn-grad{background:linear-gradient(135deg,#667EEA 0%,#764BA2 100%);border:none;color:#fff;font-weight:600;border-radius:10px;height:52px;transition:filter .16s,box-shadow .16s,transform .16s}
    .btn-grad:hover:not(:disabled){color:#fff;filter:brightness(1.06);box-shadow:0 8px 20px -8px rgba(102,126,234,.75)}
    .btn-grad:active:not(:disabled){transform:translateY(1px)}
    .btn-grad:disabled{opacity:.5;cursor:not-allowed}
    .pg-label{font-size:.78rem;color:#8b93a5;font-weight:600;margin-bottom:3px}
    .pg-value{font-size:1.02rem;font-weight:700;color:#0f172a}
    .pg-saldo{font-size:1.9rem;font-weight:800;letter-spacing:-.02em;font-variant-numeric:tabular-nums}
    .pg-saldo.deuda{color:#dc2626}
    .pg-saldo.cero{color:#0a8f63}
    .pg-divider{border-top:1px solid #f0f2f7;margin:18px 0}
    .pg-money{height:52px;font-size:1.15rem;font-weight:700;font-variant-numeric:tabular-nums;border:1px solid #e4e7ef}
    .pg-money:focus{border-color:#667EEA;box-shadow:0 0 0 4px rgba(102,126,234,.14)}
    .pg-select{height:52px;border:1px solid #e4e7ef;font-weight:500}
    .pg-table thead th{background:#fafbfd;border-top:1px solid #f0f2f7;border-bottom:1px solid #f0f2f7;font-size:.7rem;letter-spacing:.09em;text-transform:uppercase;color:#8b93a5;font-weight:700;padding:13px 20px;white-space:nowrap}
    .pg-table tbody td{padding:14px 20px;border-bottom:1px solid #f4f5f9;font-size:.9rem;color:#374151;vertical-align:middle}
    .pg-table tbody tr:hover{background:#fbfcff}
    .pg-table tbody tr:last-child td{border-bottom:0}
    .pg-num{font-variant-numeric:tabular-nums;font-weight:700;color:#5a6fe0}
    .pg-empty{padding:44px 20px;text-align:center;color:#9aa1b1;font-size:.92rem}
    .pg-franja{border:none;border-radius:14px;font-weight:600}
    .pg-fade{animation:pgIn .28s cubic-bezier(.22,1,.36,1)}
    @keyframes pgIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
    .recibo{border:1px dashed #d7dbe6;border-radius:12px;padding:24px;background:#fcfcfe}
    .recibo-titulo{font-weight:800;letter-spacing:2px;color:#0f172a}
    .recibo-num{font-size:1.5rem;font-weight:800;color:#5a6fe0;font-variant-numeric:tabular-nums}
    .recibo-fila{display:flex;justify-content:space-between;gap:16px;padding:7px 0;font-size:.9rem;border-bottom:1px dotted #e6e9f2}
    .recibo-fila:last-child{border-bottom:0}
    .recibo-k{color:#8b93a5;font-weight:600}
    .recibo-v{color:#0f172a;font-weight:600;text-align:right}
    .recibo-total{display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:14px;border-top:2px solid #0f172a}
    .recibo-total span:last-child{font-size:1.6rem;font-weight:800;font-variant-numeric:tabular-nums}
    .pg-scroll{max-height:420px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:#764BA2 #f6f7fb}
    .pg-scroll::-webkit-scrollbar{width:10px}
    .pg-scroll::-webkit-scrollbar-track{background:#f6f7fb}
    .pg-scroll::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#667EEA,#764BA2);border-radius:99px;border:3px solid #f6f7fb}
</style>

<h1 class="page-title">Registrar Pago y Comprobante</h1>
<p class="page-subtitle">Cobre la membresía y emita el comprobante del miembro</p>

{{-- ===== Buscador (CUS-08 · CUS-15 pasos 1 y 2) ===== --}}
<div class="pg-card pg-pad mb-3">
    <h2 class="pg-h">Buscar miembro</h2>
    <div class="d-flex gap-2 flex-wrap">
        <div class="input-group flex-grow-1" style="min-width:280px">
            <span class="input-group-text pg-ico" style="border-radius:10px 0 0 10px"><i class="bi bi-search"></i></span>
            <input type="text" id="criterio" class="form-control pg-search" style="border-radius:0 10px 10px 0"
                   placeholder="DNI, nombre o código del miembro (M-0001)" autocomplete="off" autofocus>
        </div>
        <button class="btn btn-grad px-4" id="btnBuscar" style="min-width:132px">
            <i class="bi bi-search me-1"></i> Buscar
        </button>
    </div>
    <div id="msgBusqueda" class="small mt-2"></div>
</div>

<div id="franjaExito" class="alert alert-success pg-franja pg-fade d-none"></div>
<div id="franjaError" class="alert alert-danger pg-franja pg-fade d-none"></div>

{{-- ===== Resumen + Formulario de pago ===== --}}
<div id="bloquePago" class="row g-3 d-none pg-fade">
    {{-- Resumen de membresía y saldo (CUS-15 paso 2) --}}
    <div class="col-lg-5">
        <div class="pg-card pg-pad h-100">
            <h2 class="pg-h">Resumen de membresía</h2>
            <div class="pg-value" id="rNombre"></div>
            <div class="text-muted small mb-3"><span id="rCodigo"></span> · DNI <span id="rDni"></span></div>

            <div class="row g-3">
                <div class="col-6"><div class="pg-label">Plan</div><div class="pg-value" id="rPlan"></div></div>
                <div class="col-6"><div class="pg-label">Costo del plan</div><div class="pg-value">S/ <span id="rCosto"></span></div></div>
                <div class="col-6"><div class="pg-label">Inicio</div><div class="pg-value" id="rInicio"></div></div>
                <div class="col-6"><div class="pg-label">Fin</div><div class="pg-value" id="rFin"></div></div>
                <div class="col-6"><div class="pg-label">Estado</div><span class="badge" id="rEstado"></span></div>
                <div class="col-6"><div class="pg-label">Ya pagado</div><div class="pg-value">S/ <span id="rPagado"></span></div></div>
            </div>

            <div class="pg-divider"></div>

            <div class="pg-label">Saldo pendiente</div>
            <div class="pg-saldo" id="rSaldo">S/ 0.00</div>
        </div>
    </div>

    {{-- Formulario de pago (CUS-15 paso 3) --}}
    <div class="col-lg-7">
        <div class="pg-card pg-pad h-100">
            <h2 class="pg-h">Datos del pago</h2>
            <form id="formPago" novalidate>
                <div class="mb-3">
                    <label for="monto" class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="height:52px;font-weight:700">S/</span>
                        <input type="text" id="monto" class="form-control pg-money" inputmode="decimal"
                               placeholder="0.00" autocomplete="off">
                    </div>
                    <div id="msgMonto" class="small mt-1"></div>
                </div>

                <div class="mb-4">
                    <label for="metodoPago" class="form-label fw-semibold">Método de pago <span class="text-danger">*</span></label>
                    <select id="metodoPago" class="form-select pg-select">
                        <option value="">Seleccione un método</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                    <div id="msgMetodo" class="small mt-1"></div>
                </div>

                <button type="submit" class="btn btn-grad w-100" id="btnRegistrar" disabled>
                    <i class="bi bi-cash-coin me-1"></i> Registrar pago
                </button>
                <p class="text-muted small mt-2 mb-0">
                    El pago y su comprobante se guardan en una sola transacción (RNF-06).
                </p>
            </form>
        </div>
    </div>
</div>

{{-- ===== Comprobantes emitidos hoy (CUS-16 FA-02: reimprimir) ===== --}}
<div class="pg-card mt-3">
    <div class="pg-pad pb-3 d-flex justify-content-between align-items-center">
        <h2 class="pg-h mb-0">Comprobantes emitidos hoy</h2>
        <span class="text-muted small"><i class="bi bi-receipt"></i> Reimpresión sin generar un nuevo número</span>
    </div>
    <div class="table-responsive pg-scroll">
        <table class="table pg-table mb-0">
            <thead>
                <tr>
                    <th>N.° comprobante</th>
                    <th>Miembro</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Fecha y hora</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyComprobantes">
                @forelse($comprobantes as $c)
                    <tr>
                        <td class="pg-num">{{ $c['numero'] }}</td>
                        <td>{{ $c['miembro'] }} <span class="text-muted small">· {{ $c['codigo'] }}</span></td>
                        <td>S/ {{ $c['montoFmt'] }}</td>
                        <td>{{ $c['metodoPago'] }}</td>
                        <td>{{ $c['fechaEmision'] }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-reimprimir="{{ $c['numero'] }}">
                                <i class="bi bi-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="pg-empty">Aún no se emitió ningún comprobante hoy</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== Vista previa del comprobante (CUS-16 paso 4) ===== --}}
<div class="modal fade" id="modalComprobante" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt-cutoff text-primary"></i> Comprobante de pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="recibo" id="recibo">
                    <div class="text-center mb-3">
                        <div class="recibo-titulo">IRON-BORG</div>
                        <div class="text-muted" style="font-size:.7rem;letter-spacing:1.5px">GYM MANAGEMENT</div>
                        <div class="mt-2 text-muted small">COMPROBANTE DE PAGO</div>
                        <div class="recibo-num" id="cNumero"></div>
                    </div>
                    <div class="recibo-fila"><span class="recibo-k">Fecha y hora</span><span class="recibo-v" id="cFecha"></span></div>
                    <div class="recibo-fila"><span class="recibo-k">Miembro</span><span class="recibo-v" id="cMiembro"></span></div>
                    <div class="recibo-fila"><span class="recibo-k">DNI</span><span class="recibo-v" id="cDni"></span></div>
                    <div class="recibo-fila"><span class="recibo-k">Código</span><span class="recibo-v" id="cCodigo"></span></div>
                    <div class="recibo-fila"><span class="recibo-k">Plan</span><span class="recibo-v" id="cPlan"></span></div>
                    <div class="recibo-fila"><span class="recibo-k">Vigencia</span><span class="recibo-v" id="cVigencia"></span></div>
                    <div class="recibo-fila"><span class="recibo-k">Método de pago</span><span class="recibo-v" id="cMetodo"></span></div>
                    <div class="recibo-fila"><span class="recibo-k">Atendido por</span><span class="recibo-v" id="cAtendido"></span></div>
                    <div class="recibo-total"><span class="fw-bold">TOTAL PAGADO</span><span id="cMonto"></span></div>
                    <div class="text-center text-muted mt-3" style="font-size:.75rem">
                        Saldo pendiente: S/ <span id="cSaldo"></span> · Comprobante interno, no válido como comprobante SUNAT
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-primary px-3" id="btnImprimir">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <button type="button" class="btn btn-grad px-3" id="btnPdf" style="height:42px">
                    <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const money = n => 'S/ ' + Number(n || 0).toFixed(2);

    const modalComp = new bootstrap.Modal($('modalComprobante'));
    let miembro = null, membresia = null, comprobanteActual = null;

    function ocultarFranjas() {
        $('franjaExito').classList.add('d-none');
        $('franjaError').classList.add('d-none');
    }

    function error(txt) {
        ocultarFranjas();
        $('franjaError').textContent = txt;
        $('franjaError').classList.remove('d-none');
    }

    function exito(txt) {
        ocultarFranjas();
        $('franjaExito').textContent = '✓ ' + txt;
        $('franjaExito').classList.remove('d-none');
    }

    /* ---------- CUS-08: buscar miembro ---------- */
    async function buscar() {
        const q = $('criterio').value.trim();
        if (!q) return;
        ocultarFranjas();
        $('msgBusqueda').textContent = '';
        $('btnBuscar').disabled = true;

        try {
            const r = await fetch(`/pagos/buscar?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
            const d = await r.json();

            if (!r.ok) {
                $('bloquePago').classList.add('d-none');
                miembro = null; membresia = null;
                $('msgBusqueda').innerHTML = `<span class="text-danger">${d.mensaje || 'No se pudo buscar al miembro.'}</span>`;
                return;
            }

            miembro = d.miembro;
            membresia = d.membresia;

            $('rNombre').textContent = miembro.nombre;
            $('rCodigo').textContent = miembro.codigo;
            $('rDni').textContent = miembro.dni;
            $('rPlan').textContent = membresia.plan;
            $('rCosto').textContent = membresia.costo.toFixed(2);
            $('rInicio').textContent = membresia.fechaInicio;
            $('rFin').textContent = membresia.fechaFin;
            $('rPagado').textContent = membresia.totalPagado.toFixed(2);

            const badge = $('rEstado');
            badge.className = 'badge ' + (membresia.estado === 'Activa' ? 'bg-success'
                : membresia.estado === 'Vencida' ? 'bg-warning text-dark' : 'bg-danger');
            badge.textContent = membresia.estado;

            pintarSaldo(membresia.saldoPendiente);

            $('monto').value = '';
            $('metodoPago').value = '';
            $('msgMonto').textContent = '';
            $('msgMetodo').textContent = '';
            $('bloquePago').classList.remove('d-none');
            validarFormulario();

            if (membresia.saldoPendiente <= 0) {
                $('msgBusqueda').innerHTML = '<span class="text-success">La membresía está al día: no tiene saldo pendiente por cobrar.</span>';
            }
        } catch (_) {
            error('No se pudo conectar con el servidor. Intente nuevamente.');
        } finally {
            $('btnBuscar').disabled = false;
        }
    }

    function pintarSaldo(saldo) {
        membresia.saldoPendiente = Number(saldo);
        const el = $('rSaldo');
        el.textContent = money(saldo);
        el.className = 'pg-saldo ' + (saldo > 0 ? 'deuda' : 'cero');
    }

    /* ---------- Validación en pantalla (CUS-15 FA-01/02/03) ---------- */
    function validarFormulario() {
        let ok = true;
        const saldo = membresia ? membresia.saldoPendiente : 0;
        const raw = $('monto').value.trim().replace(',', '.');
        const monto = parseFloat(raw);

        $('msgMonto').textContent = '';
        $('msgMetodo').textContent = '';

        if (saldo <= 0) {
            $('btnRegistrar').disabled = true;
            return false;
        }

        if (raw !== '') {
            if (isNaN(monto) || monto <= 0) {
                $('msgMonto').innerHTML = '<span class="text-danger">El monto debe ser mayor a cero.</span>';
                ok = false;
            } else if (monto > saldo) {
                $('msgMonto').innerHTML = `<span class="text-danger">El monto supera el saldo pendiente (${money(saldo)}).</span>`;
                ok = false;
            }
        } else {
            ok = false;
        }

        if (!$('metodoPago').value) ok = false;

        $('btnRegistrar').disabled = !ok;
        return ok;
    }

    $('monto').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9.,]/g, '');
        validarFormulario();
    });
    $('metodoPago').addEventListener('change', validarFormulario);

    /* ---------- CUS-15 + CUS-16: registrar pago ---------- */
    $('formPago').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!validarFormulario()) {
            if (!$('metodoPago').value) {
                $('msgMetodo').innerHTML = '<span class="text-danger">Seleccione un método de pago.</span>';
            }
            return;
        }

        $('btnRegistrar').disabled = true;
        $('btnRegistrar').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Registrando…';

        try {
            const r = await fetch('/pagos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    idMembresia: membresia.idMembresia,
                    monto: parseFloat($('monto').value.trim().replace(',', '.')),
                    metodoPago: $('metodoPago').value
                })
            });
            const d = await r.json();

            if (r.status === 422) {
                const errs = d.errors || {};
                if (errs.monto) $('msgMonto').innerHTML = `<span class="text-danger">${errs.monto[0]}</span>`;
                if (errs.metodoPago) $('msgMetodo').innerHTML = `<span class="text-danger">${errs.metodoPago[0]}</span>`;
                error(d.message || 'Revise los datos del pago.');
                return;
            }

            if (!r.ok || !d.ok) { error(d.mensaje || 'No se pudo registrar el pago, intente nuevamente.'); return; }

            exito(d.mensaje);
            pintarSaldo(d.saldoPendiente);
            $('rPagado').textContent = (membresia.totalPagado + d.comprobante.monto).toFixed(2);
            membresia.totalPagado += d.comprobante.monto;
            $('monto').value = '';
            $('metodoPago').value = '';
            agregarFila(d.comprobante);
            abrirComprobante(d.comprobante);
        } catch (_) {
            error('No se pudo registrar el pago, intente nuevamente.');
        } finally {
            $('btnRegistrar').innerHTML = '<i class="bi bi-cash-coin me-1"></i> Registrar pago';
            validarFormulario();
        }
    });

    /* ---------- CUS-16 paso 4: vista previa ---------- */
    function abrirComprobante(c) {
        comprobanteActual = c;
        $('cNumero').textContent = 'N.° ' + c.numero;
        $('cFecha').textContent = c.fechaEmision;
        $('cMiembro').textContent = c.miembro;
        $('cDni').textContent = c.dni;
        $('cCodigo').textContent = c.codigo;
        $('cPlan').textContent = c.plan;
        $('cVigencia').textContent = c.vigencia;
        $('cMetodo').textContent = c.metodoPago;
        $('cAtendido').textContent = c.atendidoPor;
        $('cMonto').textContent = money(c.monto);
        $('cSaldo').textContent = c.saldo;
        modalComp.show();
    }

    function agregarFila(c) {
        const tbody = $('tbodyComprobantes');
        if (tbody.querySelector('.pg-empty')) tbody.innerHTML = '';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="pg-num"></td>
            <td><span class="n"></span> <span class="text-muted small cod"></span></td>
            <td class="m"></td><td class="me"></td><td class="f"></td>
            <td class="text-end"><button class="btn btn-sm btn-outline-primary" data-reimprimir="${c.numero}"><i class="bi bi-eye"></i> Ver</button></td>`;
        tr.querySelector('.pg-num').textContent = c.numero;
        tr.querySelector('.n').textContent = c.miembro;
        tr.querySelector('.cod').textContent = '· ' + c.codigo;
        tr.querySelector('.m').textContent = money(c.monto);
        tr.querySelector('.me').textContent = c.metodoPago;
        tr.querySelector('.f').textContent = c.fechaEmision;
        tbody.prepend(tr);
    }

    // CUS-16 FA-02: reimprimir devuelve el MISMO número
    $('tbodyComprobantes').addEventListener('click', async e => {
        const b = e.target.closest('[data-reimprimir]');
        if (!b) return;
        try {
            const r = await fetch(`/pagos/comprobante/${b.dataset.reimprimir}`, { headers: { 'Accept': 'application/json' } });
            if (r.ok) abrirComprobante(await r.json());
        } catch (_) { error('No se pudo recuperar el comprobante.'); }
    });

    $('btnImprimir').addEventListener('click', () => {
        if (comprobanteActual) window.open(`/pagos/comprobante/${comprobanteActual.numero}/imprimir`, '_blank', 'width=420,height=680');
    });

    $('btnPdf').addEventListener('click', () => {
        if (!comprobanteActual) return;
        const c = comprobanteActual;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ unit: 'mm', format: [80, 190] });
        let y = 12;
        const linea = (k, v) => {
            doc.setFont('helvetica', 'bold'); doc.setFontSize(7); doc.text(k, 6, y);
            doc.setFont('helvetica', 'normal'); doc.text(String(v), 74, y, { align: 'right' });
            y += 5;
        };

        doc.setFont('helvetica', 'bold'); doc.setFontSize(13);
        doc.text('IRON-BORG', 40, y, { align: 'center' }); y += 5;
        doc.setFontSize(6); doc.setFont('helvetica', 'normal');
        doc.text('GYM MANAGEMENT', 40, y, { align: 'center' }); y += 7;
        doc.setFontSize(8); doc.text('COMPROBANTE DE PAGO', 40, y, { align: 'center' }); y += 6;
        doc.setFont('helvetica', 'bold'); doc.setFontSize(12);
        doc.text('N.° ' + c.numero, 40, y, { align: 'center' }); y += 8;

        linea('Fecha:', c.fechaEmision);
        linea('Miembro:', c.miembro);
        linea('DNI:', c.dni);
        linea('Codigo:', c.codigo);
        linea('Plan:', c.plan);
        linea('Vigencia:', c.vigencia);
        linea('Metodo:', c.metodoPago);
        linea('Atendio:', c.atendidoPor);

        y += 2; doc.setLineWidth(0.5); doc.line(6, y, 74, y); y += 6;
        doc.setFont('helvetica', 'bold'); doc.setFontSize(9);
        doc.text('TOTAL PAGADO', 6, y);
        doc.setFontSize(13); doc.text(money(c.monto), 74, y, { align: 'right' }); y += 8;
        doc.setFont('helvetica', 'normal'); doc.setFontSize(7);
        doc.text('Saldo pendiente: ' + money(parseFloat(c.saldo)), 40, y, { align: 'center' }); y += 5;
        doc.text('Comprobante interno - no SUNAT', 40, y, { align: 'center' });

        doc.save(`comprobante-${c.numero}.pdf`);
    });

    $('btnBuscar').addEventListener('click', buscar);
    $('criterio').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscar(); } });

    // CUS-14 FA-02: llegar con el miembro ya cargado (?dni= o ?miembro=)
    const params = new URLSearchParams(location.search);
    const preload = params.get('dni') || params.get('miembro') || params.get('q');
    if (preload) { $('criterio').value = preload; buscar(); }
})();
</script>
@endpush