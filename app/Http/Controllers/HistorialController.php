<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HistorialController extends Controller
{
    /** P-07 · Consultar historial del miembro (solo lectura) */
    public function index(Request $request)
    {
        // RNF-09 / RNF-02: solo el Administrador consulta datos personales
        if (Auth::user()->rol !== 'Administrador') {
            abort(403, 'No tiene permisos para esta acción.');
        }

        $q         = trim((string) $request->query('q', ''));
        $idMiembro = (int) $request->query('miembro', 0);
        $desde     = trim((string) $request->query('desde', ''));
        $hasta     = trim((string) $request->query('hasta', ''));

        $vista = [
            'q'             => $q,
            'desde'         => $desde,
            'hasta'         => $hasta,
            'miembro'       => null,
            'membresia'     => null,
            'resultados'    => collect(),
            'pagos'         => collect(),
            'asistencias'   => collect(),
            'totalPagado'   => 0.0,
            'sinRegistros'  => false,
            'rangoAplicado' => false,
            'errorBusqueda' => null,
            'errorRango'    => null,
        ];

        // ===== CUS-08: buscar al miembro =====
        if ($idMiembro === 0 && $q !== '') {
            // CUS-08 FA-02
            if (mb_strlen($q) < 3 && !ctype_digit($q) && !preg_match('/^M-?\d+$/i', $q)) {
                $vista['errorBusqueda'] = 'Ingrese un DNI, código o al menos 3 letras del nombre.';
                return view('historial.index', $vista);
            }

            $resultados = $this->buscarMiembros($q);

            // CUS-08 FA-01
            if ($resultados->isEmpty()) {
                $vista['errorBusqueda'] = 'No se encontró ningún miembro con ese dato';
                return view('historial.index', $vista);
            }

            // CUS-08 FA-03: coincidencia exacta por DNI o código → selección automática
            $exacto = preg_match('/^\d{8}$/', $q) || preg_match('/^M-?\d+$/i', $q);

            if ($exacto && $resultados->count() === 1) {
                $idMiembro = (int) $resultados->first()->idMiembro;
            } else {
                $vista['resultados'] = $resultados;
                return view('historial.index', $vista);
            }
        }

        if ($idMiembro === 0) {
            return view('historial.index', $vista);
        }

        $miembro = Miembro::find($idMiembro);

        if (!$miembro) {
            $vista['errorBusqueda'] = 'No se encontró ningún miembro con ese dato';
            return view('historial.index', $vista);
        }

        // CUS-17 paso 4: datos del miembro y su membresía actual
        $vista['miembro'] = $miembro;
        $vista['membresia'] = Membresia::with('plan')
            ->where('idMiembro', $miembro->idMiembro)
            ->orderByDesc('fechaInicio')
            ->orderByDesc('idMembresia')
            ->first();

        // ===== CUS-17 FA-02: rango inválido → no se aplica el filtro =====
        $aplicarRango = false;

        if ($desde !== '' && $hasta !== '') {
            try {
                $d = Carbon::parse($desde)->startOfDay();
                $h = Carbon::parse($hasta)->startOfDay();

                if ($d->gt($h)) {
                    $vista['errorRango'] = 'Rango de fechas no válido';
                } else {
                    $aplicarRango = true;
                    $vista['rangoAplicado'] = true;
                }
            } catch (\Throwable $e) {
                $vista['errorRango'] = 'Rango de fechas no válido';
            }
        }

        // ===== CUS-17 paso 5: dos listas, de la más reciente a la más antigua =====
        $pagos = Pago::with('comprobante')
            ->whereHas('membresia', fn ($x) => $x->where('idMiembro', $miembro->idMiembro))
            ->when($aplicarRango, fn ($x) => $x
                ->whereDate('fechaPago', '>=', $desde)
                ->whereDate('fechaPago', '<=', $hasta))
            ->orderByDesc('fechaPago')
            ->orderByDesc('idPago')
            ->get();

        $asistencias = Asistencia::whereHas('membresia', fn ($x) => $x->where('idMiembro', $miembro->idMiembro))
            ->when($aplicarRango, fn ($x) => $x
                ->whereDate('fechaHoraIngreso', '>=', $desde)
                ->whereDate('fechaHoraIngreso', '<=', $hasta))
            ->orderByDesc('fechaHoraIngreso')
            ->orderByDesc('idAsistencia')
            ->get();

        $vista['pagos']       = $pagos;
        $vista['asistencias'] = $asistencias;
        $vista['totalPagado'] = (float) $pagos->sum('monto');

        // CUS-17 FA-01: el miembro no tiene pagos ni asistencias
        $vista['sinRegistros'] = !$aplicarRango && $pagos->isEmpty() && $asistencias->isEmpty();

        return view('historial.index', $vista);
    }

    /** CUS-08: coincidencias con código, nombre, DNI, plan y estado */
    private function buscarMiembros(string $q)
    {
        $codigo = null;
        if (preg_match('/^M-?(\d+)$/i', $q, $m)) {
            $codigo = (int) $m[1];
        }

        $miembros = Miembro::where('dni', 'like', "%{$q}%")
            ->orWhere('nombre', 'like', "%{$q}%")
            ->orWhere('apellido', 'like', "%{$q}%")
            ->when($codigo, fn ($x) => $x->orWhere('idMiembro', $codigo))
            ->orderBy('idMiembro')
            ->limit(20)
            ->get();

        $ultimas = Membresia::with('plan')
            ->whereIn('idMiembro', $miembros->pluck('idMiembro'))
            ->orderByDesc('fechaInicio')
            ->orderByDesc('idMembresia')
            ->get()
            ->groupBy('idMiembro')
            ->map(fn ($grupo) => $grupo->first());

        return $miembros->map(function ($m) use ($ultimas) {
            $m->setRelation('membresiaReciente', $ultimas[$m->idMiembro] ?? null);
            return $m;
        });
    }
}