@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="page-title">Dashboard Principal</h1>
    <p class="page-subtitle">Resumen general del gimnasio</p>

    {{-- Tarjetas de indicadores (Tabla 13 del PDF) --}}
    <div class="row g-3">

        {{-- Tarjeta 1: Miembros Activos (CUS-05 paso 2) --}}
        <div class="col-md-4">
            <div class="card-indicador">
                <div class="icon-box blue">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="label">Miembros Activos</div>
                <div class="value">{{ $miembrosActivos ?? 0 }}</div>
            </div>
        </div>

        {{-- Tarjeta 2: Ingresos del Mes (CUS-05 paso 3) --}}
        <div class="col-md-4">
            <div class="card-indicador">
                <div class="icon-box green">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="label">Ingresos del Mes</div>
                <div class="value">S/{{ number_format($ingresosMes ?? 0, 2) }}</div>
            </div>
        </div>

        {{-- Tarjeta 3: Por Vencer 7 días (CUS-06 paso 1) --}}
        <div class="col-md-4">
            <div class="card-indicador">
                <div class="icon-box orange">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="label">Por Vencer (7 días)</div>
                <div class="value">{{ $porVencer ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Gráfico: Ingresos Últimos 6 Meses (CUS-07 pasos 1 y 2) --}}
    <div class="card-grafico">
        <h5>Ingresos Últimos 6 Meses</h5>
        <div style="height: 320px;">
            <canvas id="graficoIngresos"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const etiquetas = @json($meses ?? []);
    const valores   = @json($ingresosGrafico ?? []);
    const maximo    = Math.max(...valores, 0);

    new Chart(document.getElementById('graficoIngresos').getContext('2d'), {
        type: 'line',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Ingresos',
                data: valores,
                borderColor: '#667EEA',
                backgroundColor: 'rgba(102,126,234,0.10)',
                borderWidth: 2.5,
                pointBackgroundColor: '#667EEA',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#6b7280', font: { family: 'Inter' } } },
                tooltip: {
                    callbacks: {
                        label: ctx => 'S/ ' + Number(ctx.parsed.y).toFixed(2)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: maximo > 0 ? maximo * 1.2 : 100,
                    title: { display: true, text: 'Monto (S/)', color: '#6b7280' },
                    grid: { color: '#f0f0f5' },
                    ticks: {
                        color: '#6b7280',
                        callback: v => 'S/' + v
                    }
                },
                x: {
                    title: { display: true, text: 'Mes', color: '#6b7280' },
                    grid: { display: false },
                    ticks: { color: '#6b7280' }
                }
            }
        }
    });
</script>
@endpush