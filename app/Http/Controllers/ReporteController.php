<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Membresia;
use App\Models\Asistencia;
use App\Models\ReporteDeGestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /** CUS-18 pasos 1 y 2: pantalla con los campos «Desde» y «Hasta» */
    public function index()
    {
        $this->autorizar(); // RNF-02 / CUS-18 FA-03

        return view('reportes.index', [
            'desde' => Carbon::today()->startOfMonth()->toDateString(),
            'hasta' => Carbon::today()->toDateString(),
            'reporte' => null,
            'sinDatos' => false,
            'detalle' => collect(),
        ]);
    }

    /** CUS-18 pasos 3 a 6 + CUS-22 */
    public function generar(Request $request)
    {
        $this->autorizar();

        // CUS-18 FA-02: «Rango de fechas no válido»
        $request->validate([
            'desde' => ['required', 'date', 'before_or_equal:today'],
            'hasta' => ['required', 'date', 'after_or_equal:desde', 'before_or_equal:today'],
        ], [
            'desde.required' => 'La fecha «Desde» es obligatoria.',
            'desde.date' => 'Rango de fechas no válido.',
            'desde.before_or_equal' => 'Rango de fechas no válido: la fecha no puede ser futura.',
            'hasta.required' => 'La fecha «Hasta» es obligatoria.',
            'hasta.date' => 'Rango de fechas no válido.',
            'hasta.after_or_equal' => 'Rango de fechas no válido: «Desde» no puede ser mayor que «Hasta».',
            'hasta.before_or_equal' => 'Rango de fechas no válido: la fecha no puede ser futura.',
        ]);

        $desde = Carbon::parse($request->desde)->startOfDay();
        $hasta = Carbon::parse($request->hasta)->endOfDay();

        // CUS-18 paso 5: cálculo de indicadores del rango
        $ingresos = (float) Pago::whereBetween('fechaPago', [$desde, $hasta])->sum('monto');
        $asistencias = Asistencia::whereBetween('fechaHoraIngreso', [$desde, $hasta])->count();

        // Membresías cuyo periodo se cruza con el rango, según su estado
        $enRango = fn ($estado) => Membresia::where('estado', $estado)
            ->whereDate('fechaInicio', '<=', $hasta)
            ->whereDate('fechaFin', '>=', $desde)
            ->count();

        $activas = $enRango('Activa');
        $vencidas = $enRango('Vencida');

        $base = [
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
        ];

        // CUS-22: no hay ningún registro en el rango → no se genera reporte vacío
        if ($ingresos == 0 && $asistencias == 0 && $activas == 0 && $vencidas == 0) {
            return view('reportes.index', $base + [
                'reporte' => null,
                'sinDatos' => true,
                'detalle' => collect(),
            ]);
        }

        // CUS-22 FA-01: datos parciales → se genera y se marca «Sin datos» por sección
        $parcial = $ingresos == 0 || $asistencias == 0 || ($activas + $vencidas) == 0;

        $reporte = ReporteDeGestion::create([
            'fechaDesde' => $desde->toDateString(),
            'fechaHasta' => $hasta->toDateString(),
            'totalIngresos' => $ingresos,
            'membresiasActivas' => $activas,
            'membresiasVencidas' => $vencidas,
            'totalAsistencias' => $asistencias,
            'fechaGeneracion' => now(),
            'estado' => $parcial ? 'Generado parcial' : 'Generado',
            'idUsuarioGenera' => Auth::id(),
        ]);

        // CUS-18 paso 6: tabla de detalle (ingresos del rango)
        $detalle = Pago::with('membresia.miembro', 'comprobante')
            ->whereBetween('fechaPago', [$desde, $hasta])
            ->orderByDesc('fechaPago')
            ->get();

        return view('reportes.index', $base + [
            'reporte' => $reporte,
            'sinDatos' => false,
            'detalle' => $detalle,
        ]);
    }

    /** RNF-02: el rol se valida en el servidor, no solo en la vista */
    private function autorizar(): void
    {
        if (Auth::user()->rol !== 'Administrador') {
            abort(403, 'No tiene permisos para esta acción.');
        }
    }
}