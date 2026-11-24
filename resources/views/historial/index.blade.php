@extends('layouts.app')

@section('title', 'Historial del Miembro')

@section('content')
<style>
    .hi-card{background:#fff;border:1px solid #edeff5;border-radius:16px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px -18px rgba(16,24,40,.25)}
    .hi-pad{padding:24px 26px}
    .hi-h{font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 16px}
    .hi-search{height:52px;border:1px solid #e4e7ef;font-size:1rem;background:#fcfcfe}
    .hi-search:focus{border-color:#667EEA;box-shadow:0 0 0 4px rgba(102,126,234,.14);background:#fff}
    .hi-ico{height:52px;background:#fcfcfe;border:1px solid #e4e7ef;border-right:0;color:#9aa1b1}
    .btn-grad{background:linear-gradient(135deg,#667EEA 0%,#764BA2 100%);border:none;color:#fff;font-weight:600;border-radius:10px;height:52px;transition:filter .16s,box-shadow .16s,transform .16s}
    .btn-grad:hover:not(:disabled){color:#fff;filter:brightness(1.06);box-shadow:0 8px 20px -8px rgba(102,126,234,.75)}
    .btn-grad:active:not(:disabled){transform:translateY(1px)}
    .hi-date{height:46px;border:1px solid #e4e7ef;border-radius:10px;padding:0 12px;font-size:.92rem;font-weight:500;color:#374151;width:100%}
    .hi-date:focus{outline:none;border-color:#667EEA;box-shadow:0 0 0 4px rgba(102,126,234,.14)}
    .hi-label{font-size:.76rem;color:#8b93a5;font-weight:600;margin-bottom:3px;text-transform:uppercase;letter-spacing:.06em}
    .hi-value{font-size:1.02rem;font-weight:700;color:#0f172a}
    .hi-divider{border-top:1px solid #f0f2f7;margin:20px 0}
    .hi-avatar{width:52px;height:52px;border-radius:14px;background:rgba(102,126,234,.12);color:#5a6fe0;display:flex;align-items:center;justify-content:center;font-size:1.4rem}
    .hi-item{display:block;border:1px solid #eceef5;border-radius:12px;padding:14px 18px;text-decoration:none;transition:all .16s ease;margin-bottom:8px}
    .hi-item:hover{border-color:#667EEA;background:#fbfcff;transform:translateX(2px)}
    .hi-item .n{font-weight:700;color:#0f172a}
    .hi-item .s{font-size:.8rem;color:#8b93a5}
    .hi-tabs{border-bottom:1px solid #f0f2f7;padding:0 26px}
    .hi-tabs .nav-link{border:0;border-bottom:2px solid transparent;color:#8b93a5;font-weight:600;font-size:.92rem;padding:14px 4px;margin-right:26px}
    .hi-tabs .nav-link:hover{color:#374151;border-color:transparent}
    .hi-tabs .nav-link.active{color:#5a6fe0;border-bottom-color:#5a6fe0;background:transparent}
    .hi-count{display:inline-block;background:#f4f5f9;color:#6b7280;border-radius:999px;padding:1px 9px;font-size:.74rem;font-weight:700;margin-left:7px}
    .nav-link.active .hi-count{background:rgba(102,126,234,.14);color:#5a6fe0}
    .hi-table thead th{background:#fafbfd;border-top:1px solid #f0f2f7;border-bottom:1px solid #f0f2f7;font-size:.7rem;letter-spacing:.09em;text-transform:uppercase;color:#8b93a5;font-weight:700;padding:13px 20px;white-space:nowrap}
    .hi-table tbody td{padding:14px 20px;border-bottom:1px solid #f4f5f9;font-size:.9rem;color:#374151;vertical-align:middle}
    .hi-table tbody tr:hover{background:#fbfcff}
    .hi-table tbody tr:last-child td{border-bottom:0}
    .hi-table tfoot td{padding:14px 20px;background:#fafbfd;font-weight:700;color:#0f172a;border-top:2px solid #eceef5}
    .hi-num{font-variant-numeric:tabular-nums;font-weight:700;color:#5a6fe0}
    .hi-time{font-variant-numeric:tabular-nums;font-weight:600;color:#1f2937}
    .hi-empty{padding:48px 20px;text-align:center;color:#9aa1b1;font-size:.92rem}
    .hi-franja{border:none;border-radius:14px}
    .hi-fade{animation:hiIn .28s cubic-bezier(.22,1,.36,1)}
    @keyframes hiIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
    .hi-scroll{max-height:440px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:#764BA2 #f6f7fb}
    .hi-scroll::-webkit-scrollbar{width:10px}
    .hi-scroll::-webkit-scrollbar-track{background:#f6f7fb}
    .hi-scroll::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#667EEA,#764BA2);border-radius:99px;border:3px solid #f6f7fb}
</style>

<h1 class="page-title">Historial del Miembro</h1>
<p class="page-subtitle">Consulte los pagos y asistencias registrados de un miembro</p>

{{-- ===== CUS-17 paso 2 · CUS-08: buscador ===== --}}
<div class="hi-card hi-pad mb-3">
    <h2 class="hi-h">Buscar miembro</h2>
    <form action="{{ route('historial.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
        <div class="input-group flex-grow-1" style="min-width:280px">
            <span class="input-group-text hi-ico" style="border-radius:10px 0 0 10px"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control hi-search" style="border-radius:0 10px 10px 0"
                   placeholder="DNI, nombre o código del miembro (M-0001)"
                   value="{{ $miembro ? '' : $q }}" autocomplete="off" autofocus>
        </div>
        <button class="btn btn-grad px-4" style="min-width:132px">
            <i class="bi bi-search me-1"></i> Buscar
        </button>
        @if($miembro)
            <a href="{{ route('historial.index') }}" class="btn btn-outline-secondary" style="height:52px;line-height:38px">
                Cambiar de miembro
            </a>
        @endif
    </form>
</div>

@if($errorBusqueda)
    <div class="alert alert-warning hi-franja hi-fade">
        <i class="bi bi-exclamation-circle me-1"></i> {{ $errorBusqueda }}
    </div>
@endif

{{-- ===== CUS-08 paso 2: lista de resultados ===== --}}
@if($resultados->isNotEmpty())
    <div class="hi-card hi-pad hi-fade">
        <h2 class="hi-h">Resultados <span class="hi-count">{{ $resultados->count() }}</span></h2>
        @foreach($resultados as $r)
            @php $mem = $r->membresiaReciente; @endphp
            <a class="hi-item" href="{{ route('historial.index', ['miembro' => $r->idMiembro]) }}">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <div class="n">{{ $r->nombre }} {{ $r->apellido }}</div>
                        <div class="s">
                            M-{{ str_pad($r->idMiembro, 4, '0', STR_PAD_LEFT) }} · DNI {{ $r->dni }}
                            @if($r->telefono) · Tel. {{ $r->telefono }} @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="s">{{ $mem?->plan?->nombre ?? 'Sin membresía' }}</div>
                        @if($mem && $mem->estado === 'Activa')
                            <span class="badge bg-success">Activa</span>
                        @elseif($mem && $mem->estado === 'Vencida')
                            <span class="badge bg-warning text-dark">Vencida</span>
                        @elseif($mem && $mem->estado === 'Cancelada')
                            <span class="badge bg-danger">Cancelada</span>
                        @else
                            <span class="badge bg-secondary">Sin membresía</span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif

@if($miembro)
    {{-- ===== CUS-17 paso 4: ficha del miembro y membresía actual ===== --}}
    <div class="hi-card hi-pad mb-3 hi-fade">
        <div class="d-flex gap-3 align-items-center mb-3">
            <div class="hi-avatar"><i class="bi bi-person"></i></div>
            <div>
                <div class="hi-value" style="font-size:1.2rem">{{ $miembro->nombre }} {{ $miembro->apellido }}</div>
                <div class="text-muted small">
                    M-{{ str_pad($miembro->idMiembro, 4, '0', STR_PAD_LEFT) }} · DNI {{ $miembro->dni }}
                    @if($miembro->telefono) · Tel. {{ $miembro->telefono }} @endif
                </div>
            </div>
        </div>

        <div class="hi-divider"></div>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="hi-label">Plan actual</div>
                <div class="hi-value">{{ $membresia?->plan?->nombre ?? 'Sin membresía' }}</div>
            </div>
            <div class="col-md-3">
                <div class="hi-label">Vigencia</div>
                <div class="hi-value">
                    @if($membresia)
                        {{ $membresia->fechaInicio->format('d/m/Y') }} — {{ $membresia->fechaFin->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </div>
            </div>
            <div class="col-md-2">
                <div class="hi-label">Estado</div>
                @if($membresia && $membresia->estado === 'Activa')
                    <span class="badge bg-success">Activa</span>
                @elseif($membresia && $membresia->estado === 'Vencida')
                    <span class="badge bg-warning text-dark">Vencida</span>
                @elseif($membresia && $membresia->estado === 'Cancelada')
                    <span class="badge bg-danger">Cancelada</span>
                @else
                    <span class="badge bg-secondary">Sin membresía</span>
                @endif
            </div>
            <div class="col-md-2">
                <div class="hi-label">Saldo pendiente</div>
                <div class="hi-value">S/ {{ $membresia ? number_format($membresia->saldoPendiente, 2) : '0.00' }}</div>
            </div>
            <div class="col-md-2">
                <div class="hi-label">Registrado</div>
                <div class="hi-value">{{ $miembro->fechaRegistro?->format('d/m/Y') ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- ===== CUS-17 paso 6: filtro opcional por rango ===== --}}
    <div class="hi-card hi-pad mb-3">
        <h2 class="hi-h">Filtrar por rango de fechas <span class="text-muted fw-normal" style="font-size:.82rem">(opcional)</span></h2>
        <form action="{{ route('historial.index') }}" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="miembro" value="{{ $miembro->idMiembro }}">
            <div class="col-md-4">
                <label for="desde" class="form-label fw-semibold">Desde</label>
                <input type="date" name="desde" id="desde" class="hi-date" value="{{ $desde }}">
            </div>
            <div class="col-md-4">
                <label for="hasta" class="form-label fw-semibold">Hasta</label>
                <input type="date" name="hasta" id="hasta" class="hi-date" value="{{ $hasta }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-grad flex-grow-1" style="height:46px">
                    <i class="bi bi-funnel me-1"></i> Aplicar
                </button>
                <a href="{{ route('historial.index', ['miembro' => $miembro->idMiembro]) }}"
                   class="btn btn-outline-secondary" style="height:46px;line-height:32px">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- CUS-17 FA-02 --}}
    @if($errorRango)
        <div class="alert alert-danger hi-franja hi-fade">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ $errorRango }}. No se aplicó el filtro.
        </div>
    @endif

    {{-- CUS-17 FA-01 --}}
    @if($sinRegistros)
        <div class="alert alert-secondary hi-franja hi-fade">
            <i class="bi bi-inbox me-1"></i> Sin registros previos
        </div>
    @endif

    {{-- ===== CUS-17 paso 5: dos pestañas ===== --}}
    <div class="hi-card hi-fade">
        <ul class="nav nav-tabs hi-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pagos" type="button" role="tab">
                    <i class="bi bi-cash-coin me-1"></i> Pagos <span class="hi-count">{{ $pagos->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-asistencias" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> Asistencias <span class="hi-count">{{ $asistencias->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- Pestaña Pagos: fecha, monto, método y N.° de comprobante --}}
            <div class="tab-pane fade show active" id="tab-pagos" role="tabpanel">
                <div class="table-responsive hi-scroll">
                    <table class="table hi-table mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método de pago</th>
                                <th>N.° de comprobante</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $p)
                                <tr>
                                    <td>{{ $p->fechaPago->format('d/m/Y H:i') }}</td>
                                    <td>{{ $p->metodoPago }}</td>
                                    <td class="hi-num">{{ $p->comprobante?->numero ?? '—' }}</td>
                                    <td class="text-end fw-semibold">S/ {{ number_format($p->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="hi-empty">
                                        {{ $rangoAplicado ? 'Sin pagos en el rango seleccionado' : 'Sin registros previos' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($pagos->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="3">Total pagado {{ $rangoAplicado ? 'en el rango' : 'histórico' }}</td>
                                    <td class="text-end">S/ {{ number_format($totalPagado, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Pestaña Asistencias: fecha y hora --}}
            <div class="tab-pane fade" id="tab-asistencias" role="tabpanel">
                <div class="table-responsive hi-scroll">
                    <table class="table hi-table mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora de ingreso</th>
                                <th>Hora de salida</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asistencias as $a)
                                <tr>
                                    <td>{{ $a->fechaHoraIngreso->format('d/m/Y') }}</td>
                                    <td><span class="hi-time">{{ $a->fechaHoraIngreso->format('H:i') }}</span></td>
                                    <td>
                                        @if($a->fechaHoraFin)
                                            <span class="hi-time">{{ $a->fechaHoraFin->format('H:i') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="hi-empty">
                                        {{ $rangoAplicado ? 'Sin asistencias en el rango seleccionado' : 'Sin registros previos' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection