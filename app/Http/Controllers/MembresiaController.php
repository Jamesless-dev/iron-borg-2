<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\HistorialCambio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MembresiaController extends Controller
{
    /** P-04 · CUS-08: listado con la membresía más reciente de cada miembro */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // CUS-08 FA-02: criterio vacío o nombre con menos de 3 letras
        if ($q !== '' && mb_strlen($q) < 3 && !ctype_digit($q)) {
            return redirect()->route('membresias.index')
                ->withErrors(['q' => 'Ingrese un DNI, código o al menos 3 letras del nombre.']);
        }

        $miembros = Miembro::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('dni', 'like', "%{$q}%")
                        ->orWhere('nombre', 'like', "%{$q}%")
                        ->orWhere('apellido', 'like', "%{$q}%")
                        ->orWhere('idMiembro', $q);
                });
            })
            ->orderBy('idMiembro', 'desc')
            ->get();

        // Membresía más reciente por miembro (CUS-13 paso 2)
        $ultimas = Membresia::with('plan')
            ->whereIn('idMiembro', $miembros->pluck('idMiembro'))
            ->orderBy('idMembresia', 'desc')
            ->get()
            ->groupBy('idMiembro')
            ->map(fn ($grupo) => $grupo->first());

        $planes = Plan::where('activo', true)->orderBy('duracionDias')->get();

        return view('membresias.index', compact('miembros', 'ultimas', 'planes', 'q'));
    }

    /** CUS-08: búsqueda para el modal "+ Nueva Membresía" */
    public function buscar(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '' || (mb_strlen($q) < 3 && !ctype_digit($q))) {
            return response()->json([]);
        }

        $miembros = Miembro::where('dni', 'like', "%{$q}%")
            ->orWhere('nombre', 'like', "%{$q}%")
            ->orWhere('apellido', 'like', "%{$q}%")
            ->orWhere('idMiembro', $q)
            ->limit(10)
            ->get();

        $hoy = Carbon::today();

        $resultado = $miembros->map(function ($m) use ($hoy) {
            $mem = Membresia::with('plan')
                ->where('idMiembro', $m->idMiembro)
                ->orderBy('idMembresia', 'desc')
                ->first();

            // Última membresía NO cancelada: base para calcular la nueva fecha de inicio
            $base = Membresia::where('idMiembro', $m->idMiembro)
                ->where('estado', '!=', 'Cancelada')
                ->orderBy('idMembresia', 'desc')
                ->first();

            // CUS-09 paso 5: cálculo de la nueva fecha de inicio
            $nuevaInicio = ($base && $base->estado === 'Activa' && $base->fechaFin->gte($hoy))
                ? $base->fechaFin->copy()->addDay()
                : $hoy->copy();

            return [
                'idMiembro'    => $m->idMiembro,
                'codigo'       => 'M-' . str_pad($m->idMiembro, 4, '0', STR_PAD_LEFT),
                'dni'          => $m->dni,
                'nombre'       => $m->nombre . ' ' . $m->apellido,
                'plan'         => $mem?->plan?->nombre ?? 'Sin membresía',
                'estado'       => $mem?->estado ?? 'Sin membresía',
                'inicio'       => $mem?->fechaInicio?->format('d/m/Y') ?? '—',
                'fin'          => $mem?->fechaFin?->format('d/m/Y') ?? '—',
                'saldo'        => $mem ? number_format($mem->saldoPendiente, 2) : '0.00',
                'nuevaInicio'  => $nuevaInicio->toDateString(),
            ];
        });

        return response()->json($resultado);
    }

    /** CUS-09: Renovar membresía (incluye CUS-11) */
    public function renovar(Request $request, $idMiembro)
    {
        $request->validate([
            'idPlan' => ['required', 'exists:planes,idPlan'],
        ], [
            'idPlan.required' => 'Seleccione un plan de membresía.',
            'idPlan.exists'   => 'El plan seleccionado no es válido.',
        ]);

        $miembro = Miembro::findOrFail($idMiembro);
        $plan    = Plan::where('activo', true)->findOrFail($request->idPlan);

        // Última membresía registrada (cualquier estado): solo para el historial
        $ultima = Membresia::where('idMiembro', $miembro->idMiembro)
            ->orderBy('idMembresia', 'desc')
            ->first();

        // Última membresía NO cancelada: base para calcular la fecha de inicio,
        // así una cancelada (aunque tenga fecha futura) no altera el cálculo.
        $actual = Membresia::where('idMiembro', $miembro->idMiembro)
            ->where('estado', '!=', 'Cancelada')
            ->orderBy('idMembresia', 'desc')
            ->first();

        // CUS-09 paso 5 y FA-02: día siguiente a la fecha de fin si sigue vigente,
        // o la fecha actual si está vencida o cancelada.
        $hoy = Carbon::today();
        $fechaInicio = ($actual && $actual->estado === 'Activa' && $actual->fechaFin->gte($hoy))
            ? $actual->fechaFin->copy()->addDay()
            : $hoy->copy();

        // RN-CA-02: fechaFin = fechaInicio + duración del plan
        $fechaFin = $fechaInicio->copy()->addDays($plan->duracionDias);

        DB::beginTransaction();
        try {
            // RN-ES-01: se registra un NUEVO periodo, no se edita el anterior
            $nueva = Membresia::create([
                'fechaInicio'    => $fechaInicio,
                'fechaFin'       => $fechaFin,
                'costo'          => $plan->costo,
                'saldoPendiente' => $plan->costo, // RN-CA-01
                'estado'         => 'Activa',
                'idMiembro'      => $miembro->idMiembro,
                'idPlan'         => $plan->idPlan,
            ]);

            // CUS-11: historial en la misma transacción
            HistorialCambio::create([
                'accion'        => 'Renovación',
                'valorAnterior' => $ultima?->estado ?? 'Sin membresía',
                'valorNuevo'    => 'Activa',
                'detalle'       => "Plan {$plan->nombre} · {$fechaInicio->format('d/m/Y')} al {$fechaFin->format('d/m/Y')}",
                'fechaHora'     => now(),
                'idMembresia'   => $nueva->idMembresia,
                'idUsuario'     => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('membresias.index')
                ->with('success', 'Membresía renovada hasta ' . $fechaFin->format('d/m/Y'));

        } catch (\Throwable $e) {
            DB::rollBack(); // CUS-11 FA-02
            report($e);
            return back()->withErrors(['error' => 'No se pudo completar la operación.']);
        }
    }

    /** CUS-10: Cancelar membresía (solo Administrador, validado en el servidor) */
    public function cancelar(Request $request, $idMembresia)
    {
        // RNF-02 / CUS-10 FA-01
        if (Auth::user()->rol !== 'Administrador') {
            abort(403, 'No tiene permisos para esta acción.');
        }

        $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ], [
            'motivo.required' => 'Debe indicar el motivo de la cancelación.',
            'motivo.max'      => 'El motivo no debe exceder los 255 caracteres.',
        ]);

        $membresia = Membresia::findOrFail($idMembresia);

        // CUS-10 FA-02
        if ($membresia->estado === 'Cancelada') {
            return back()->withErrors(['error' => 'La membresía ya se encuentra cancelada.']);
        }

        DB::beginTransaction();
        try {
            $anterior = $membresia->estado;
            $membresia->update(['estado' => 'Cancelada']);

            HistorialCambio::create([
                'accion'        => 'Cancelación',
                'valorAnterior' => $anterior,
                'valorNuevo'    => 'Cancelada',
                'detalle'       => $request->motivo,
                'fechaHora'     => now(),
                'idMembresia'   => $membresia->idMembresia,
                'idUsuario'     => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('membresias.index')
                ->with('success', 'Membresía cancelada correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'No se pudo completar la operación.']);
        }
    }
}