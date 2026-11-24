<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\HistorialCambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegistrarMiembroRequest;
use App\Http\Requests\ActualizarMiembroRequest;

class MiembroController extends Controller
{
    // Vista de gestión de miembros (Figura 15)
public function index()
{
    $planes = \App\Models\Plan::where('activo', true)->get();
    $miembros = \App\Models\Miembro::with('membresiaActual.plan')
        ->orderBy('idMiembro', 'desc')
        ->get();
    
    return view('miembros.index', compact('planes', 'miembros'));
}

public function verificarDni(\Illuminate\Http\Request $request)
{
    $dni     = $request->query('dni');
    $excepto = $request->query('excepto');

    $existe = \App\Models\Miembro::where('dni', $dni)
        ->when($excepto, fn ($q) => $q->where('idMiembro', '!=', $excepto))
        ->exists();

    return response()->json(['existe' => $existe]);
}

public function buscar(\Illuminate\Http\Request $request)
{
    $q = $request->input('q');
    $miembros = \App\Models\Miembro::with('membresiaActual.plan')
        ->where('dni', 'like', "%$q%")
        ->orWhere('nombre', 'like', "%$q%")
        ->orWhere('apellido', 'like', "%$q%")
        ->orWhere('idMiembro', $q)
        ->get();
    return response()->json($miembros);
}

public function store(RegistrarMiembroRequest $request)
{
    $plan = Plan::findOrFail($request->idPlan);

    // CUS-07 paso 2: Si no hay fecha de inicio, usar la fecha actual
    $fechaInicio = $request->fechaInicio ? \Carbon\Carbon::parse($request->fechaInicio) : \Carbon\Carbon::today();
    $fechaFin = $fechaInicio->copy()->addDays($plan->duracionDias);

    DB::beginTransaction();
    try {
        $miembro = Miembro::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'dni' => $request->dni,
            'telefono' => $request->telefono,
            'fechaRegistro' => $fechaInicio,
            'idUsuarioRegistra' => Auth::id(),
        ]);

        $membresia = Membresia::create([
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'costo' => $plan->costo,
            'saldoPendiente' => $plan->costo,
            'estado' => 'Activa',
            'idMiembro' => $miembro->idMiembro,
            'idPlan' => $plan->idPlan,
        ]);

        HistorialCambio::create([
            'accion' => 'Creación',
            'valorAnterior' => null,
            'valorNuevo' => 'Activa',
            'fechaHora' => now(),
            'idMembresia' => $membresia->idMembresia,
            'idUsuario' => Auth::id(),
        ]);

        DB::commit();

        return redirect()->route('miembros.index')
            ->with('success', "Miembro registrado con código M-" . str_pad($miembro->idMiembro, 4, '0', STR_PAD_LEFT));

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors(['error' => 'No se pudo registrar el miembro.']);
    }

    
}

public function update(ActualizarMiembroRequest $request, $idMiembro)
{
    $miembro = \App\Models\Miembro::findOrFail($idMiembro);

    // Solo datos personales. No se toca fechaRegistro, idUsuarioRegistra ni la membresía.
    $miembro->update([
        'nombre'   => $request->nombre,
        'apellido' => $request->apellido,
        'dni'      => $request->dni,
        'telefono' => $request->telefono,
    ]);

    return redirect()->route('miembros.index')->with(
        'success',
        'Datos del miembro M-' . str_pad($miembro->idMiembro, 4, '0', STR_PAD_LEFT) . ' actualizados correctamente.'
    );
}
}