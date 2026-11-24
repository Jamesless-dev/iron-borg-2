@extends('layouts.app')

@section('title', 'Reportes de Gestión')

@section('content')
<style>
    .rp-card{background:#fff;border:1px solid #edeff5;border-radius:16px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px -18px rgba(16,24,40,.25)}
    .rp-pad{padding:24px 26px}
    .rp-h{font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 16px}
    .rp-date{height:50px;border:1px solid #e4e7ef;border-radius:10px;padding:0 14px;font-size:.95rem;font-weight:500;color:#374151;width:100%}
    .rp-date:focus{outline:none;border-color:#667EEA;box-shadow:0 0 0 4px rgba(102,126,234,.14)}
    .btn-grad{background:linear-gradient(135deg,#667EEA 0%,#764BA2 100%);border:none;color:#fff;font-weight:600;border-radius:10px;height:50px;transition:filter .16s,box-shadow .16s,transform .16s}
    .btn-grad:hover:not(:disabled){color:#fff;filter:brightness(1.06);box-shadow:0 8px 20px -8px rgba(102,126,234,.75)}
    .btn-grad:active:not(:disabled){transform:translateY(1px)}
    .rp-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:14px}
    .rp-icon.blue{background:rgba(102,126,234,.12);color:#5a6fe0}
    .rp-icon.green{background:rgba(16,185,129,.12);color:#0ea472}
    .rp-icon.orange{background:rgba(245,158,11,.12);color:#d97706}
    .rp-icon.red{background:rgba(239,68,68,.12);color:#dc2626}
    .rp-label{font-size:.84rem;color:#6b7280;font-weight:500;margin-bottom:4px}
    .rp-value{font-size:1.85rem;font-weight:800;color:#0f172a;line-height:1.1;letter-spacing:-.02em;font-variant-numeric:tabular-nums}
    .rp-sin{display:inline-block;margin-top:6px;font-size:.72rem;font-weight:700;color:#9aa1b1;background:#f4f5f9;border-radius:999px;padding:3px 10px}
    .rp-table thead th{background:#fafbfd;border-top:1px solid #f0f2f7;border-bottom:1px solid #f0f2f7;font-size:.7rem;letter-spacing:.09em;text-transform:uppercase;color:#8b93a5;font-weight:700;padding:13px 20px;white-space:nowrap}
    .rp-table tbody td{padding:14px 20px;border-bottom:1px solid #f4f5f9;font-size:.9rem;color:#374151;vertical-align:middle}
    .rp-table tbody tr:hover{background:#fbfcff}
    .rp-table tbody tr:last-child td{border-bottom:0}
    .rp-num{font-variant-numeric:tabular-nums;font-weight:700;color:#5a6fe0}
    .rp-empty{padding:44px 20px;text-align:center;color:#9aa1b1;font-size:.92rem}
    .rp-franja{border:none;border-radius:14px}
    .rp-fade{animation:rpIn .28s cubic-bezier(.22,1,.36,1)}
    @keyframes rpIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
    .rp-scroll{max-height:440px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:#764BA2 #f6f7fb}
    .rp-scroll::-webkit-scrollbar{width:10px}
    .rp-scroll::-webkit-scrollbar-track{background:#f6f7fb}
    .rp-scroll::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#667EEA,#764BA2);border-radius:99px;border:3px solid #f6f7fb}
</style>

<h1 class="page-title">Reportes de Gestión</h1>
<p class="page-subtitle">Ingresos, membresías y asistencias por rango de fechas</p>

{{-- ===== CUS-18 pasos 2 y 3: Desde / Hasta + Generar ===== --}}
<div class="rp-card rp-pad mb-3">
    <h2 class="rp-h">Rango del reporte</h2>
    <form action="{{ route('reportes.generar') }}" method="POST" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-4">
            <label for="desde" class="form-label fw-semibold">Desde <span class="text-danger">*</span></label>
            <input type="date" name="desde" id="desde" class="rp-date"
                   value="{{ old('desde', $desde) }}" max="{{ date('Y-m-d') }}" required>
            @error('desde')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="hasta" class="form-label fw-semibold">Hasta <span class="text-danger">*</span></label>
            <input type="date" name="hasta" id="hasta" class="rp-date"
                   value="{{ old('hasta', $hasta) }}" max="{{ date('Y-m-d') }}" required>
            @error('hasta')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-grad flex-grow-1">
                <i class="bi bi-graph-up me-1"></i> Generar
            </button>
            {{-- CUS-22 FA-02: Cancelar regresa al Dashboard --}}
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="height:50px;line-height:36px">Cancelar</a>
        </div>
    </form>
</div>

{{-- ===== CUS-22: sin datos suficientes ===== --}}
@if($sinDatos)
    <div class="alert alert-warning rp-franja rp-fade">
        <div class="fw-bold mb-1">
            <i class="bi bi-exclamation-triangle me-1"></i>
            No existen datos suficientes entre {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}
            y {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
        </div>
        <div class="small">Amplíe el rango de fechas e intente nuevamente. No se generó ningún reporte.</div>
    </div>
@endif

{{-- ===== CUS-18 paso 6: indicadores + detalle ===== --}}
@if($reporte)
    <div class="d-flex justify-content-between align-items-center mb-3 rp-fade flex-wrap gap-2">
        <div class="text-muted small">
            Reporte N.° {{ str_pad($reporte->idReporte, 6, '0', STR_PAD_LEFT) }} ·
            {{ $reporte->fechaDesde->format('d/m/Y') }} al {{ $reporte->fechaHasta->format('d/m/Y') }} ·
            generado el {{ $reporte->fechaGeneracion->format('d/m/Y H:i') }}
        </div>
        <span class="badge {{ $reporte->estado === 'Generado' ? 'bg-success' : 'bg-warning text-dark' }}">
            {{ $reporte->estado }}
        </span>
    </div>

    <div class="row g-3 mb-3 rp-fade">
        <div class="col-md-3">
            <div class="rp-card rp-pad h-100">
                <div class="rp-icon green"><i class="bi bi-cash-stack"></i></div>
                <div class="rp-label">Ingresos totales</div>
                <div class="rp-value">S/ {{ number_format($reporte->totalIngresos, 2) }}</div>
                @if($reporte->totalIngresos == 0)<span class="rp-sin">Sin datos</span>@endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="rp-card rp-pad h-100">
                <div class="rp-icon blue"><i class="bi bi-people"></i></div>
                <div class="rp-label">Membresías activas</div>
                <div class="rp-value">{{ $reporte->membresiasActivas }}</div>
                @if($reporte->membresiasActivas == 0)<span class="rp-sin">Sin datos</span>@endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="rp-card rp-pad h-100">
                <div class="rp-icon red"><i class="bi bi-x-circle"></i></div>
                <div class="rp-label">Membresías vencidas</div>
                <div class="rp-value">{{ $reporte->membresiasVencidas }}</div>
                @if($reporte->membresiasVencidas == 0)<span class="rp-sin">Sin datos</span>@endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="rp-card rp-pad h-100">
                <div class="rp-icon orange"><i class="bi bi-clock-history"></i></div>
                <div class="rp-label">Total asistencias</div>
                <div class="rp-value">{{ $reporte->totalAsistencias }}</div>
                @if($reporte->totalAsistencias == 0)<span class="rp-sin">Sin datos</span>@endif
            </div>
        </div>
    </div>

    <div class="rp-card rp-fade">
        <div class="rp-pad pb-3 d-flex justify-content-between align-items-center">
            <h2 class="rp-h mb-0">Detalle de ingresos del rango</h2>
            <span class="text-muted small">{{ $detalle->count() }} pago(s)</span>
        </div>
        <div class="table-responsive rp-scroll">
            <table class="table rp-table mb-0">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Miembro</th>
                        <th>Plan</th>
                        <th>Método</th>
                        <th>Comprobante</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detalle as $p)
                        <tr>
                            <td>{{ $p->fechaPago->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->membresia?->miembro?->nombre }} {{ $p->membresia?->miembro?->apellido }}</td>
                            <td>{{ $p->membresia?->plan?->nombre ?? '—' }}</td>
                            <td>{{ $p->metodoPago }}</td>
                            <td class="rp-num">{{ $p->comprobante?->numero ?? '—' }}</td>
                            <td class="text-end fw-semibold">S/ {{ number_format($p->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="rp-empty">Sin datos de ingresos en este rango</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection