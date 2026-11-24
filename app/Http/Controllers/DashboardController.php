<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use App\Models\Pago;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /** P-02 · Dashboard principal (Tabla 13 del informe) */
    public function index()
    {
        Carbon::setLocale('es');
        $hoy = Carbon::today();

        // Tarjeta «Miembros Activos»
        $miembrosActivos = Membresia::where('estado', 'Activa')
            ->whereDate('fechaFin', '>=', $hoy)
            ->distinct()
            ->count('idMiembro');

        // Tarjeta «Ingresos del Mes»
        $ingresosMes = (float) Pago::whereNotNull('fechaPago')
            ->whereBetween('fechaPago', [
                $hoy->copy()->startOfMonth()->startOfDay(),
                $hoy->copy()->endOfMonth()->endOfDay(),
            ])->sum('monto');

        // Tarjeta «Por Vencer (7 días)»
        $porVencer = Membresia::where('estado', 'Activa')
            ->whereBetween('fechaFin', [
                $hoy->toDateString(),
                $hoy->copy()->addDays(7)->toDateString(),
            ])
            ->distinct()
            ->count('idMiembro');

        // Gráfico «Ingresos Últimos 6 Meses»
        $inicio = $hoy->copy()->startOfMonth()->subMonths(5)->startOfDay();
        $fin    = $hoy->copy()->endOfMonth()->endOfDay();

        $porMes = Pago::whereNotNull('fechaPago')
            ->whereBetween('fechaPago', [$inicio, $fin])
            ->get(['monto', 'fechaPago'])
            ->groupBy(fn ($p) => $p->fechaPago->format('Y-m'))
            ->map(fn ($g) => (float) $g->sum('monto'));

        $meses = [];
        $ingresosGrafico = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = $hoy->copy()->startOfMonth()->subMonths($i);
            $meses[] = ucfirst($mes->translatedFormat('F'));
            $ingresosGrafico[] = round($porMes[$mes->format('Y-m')] ?? 0, 2);
        }

        return view('dashboard.index', compact(
            'miembrosActivos', 'ingresosMes', 'porVencer', 'meses', 'ingresosGrafico'
        ));
    }
}