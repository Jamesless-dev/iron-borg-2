<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\ParametroSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfiguracionController extends Controller
{
    // CUS-19: Mostrar configuración
    public function index()
    {
        if (Auth::user()->rol !== 'Administrador') {
            abort(403, 'No tiene permisos para esta acción.');
        }

        $planes = \App\Models\Plan::all();
        // Obtenemos el primer registro de parámetros (o null si está vacío)
        $parametros = \App\Models\ParametroSistema::first();

        return view('configuracion.index', compact('planes', 'parametros'));
    }

    // CUS-19: Crear nuevo plan
    public function storePlan(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:planes,nombre',
            'duracionDias' => 'required|integer|min:1',
            'costo' => 'required|numeric|min:0.01',
        ], [
            'nombre.required' => 'El nombre del plan es obligatorio.',
            'nombre.unique' => 'Ya existe un plan con ese nombre.',
            'duracionDias.required' => 'La duración es obligatoria.',
            'costo.required' => 'El costo es obligatorio.',
        ]);

        Plan::create([
            'nombre' => $request->nombre,
            'duracionDias' => $request->duracionDias,
            'costo' => $request->costo,
            'activo' => true,
        ]);

        return back()->with('success', 'Plan creado correctamente.');
    }

    // CUS-19: Editar plan
    public function updatePlan(Request $request, $idPlan)
    {
        $plan = Plan::findOrFail($idPlan);

        $request->validate([
            'nombre' => 'required|string|max:50|unique:planes,nombre,' . $idPlan . ',idPlan',
            'duracionDias' => 'required|integer|min:1',
            'costo' => 'required|numeric|min:0.01',
        ]);

        $plan->update([
            'nombre' => $request->nombre,
            'duracionDias' => $request->duracionDias,
            'costo' => $request->costo,
        ]);

        return back()->with('success', 'Plan actualizado correctamente.');
    }

    // CUS-19: Activar/Desactivar plan
    public function togglePlan($idPlan)
    {
        $plan = Plan::findOrFail($idPlan);
        $plan->update(['activo' => !$plan->activo]);

        return back()->with('success', 'Estado del plan actualizado.');
    }

    // CUS-19: Actualizar parámetros del sistema
    public function updateParametros(Request $request)
    {
        $request->validate([
            'intentosMaximos' => 'required|integer|min:1|max:10',
            'minutosInactividad' => 'required|integer|min:1',
        ], [
            'intentosMaximos.required' => 'El número de intentos es obligatorio.',
            'intentosMaximos.max' => 'El número de intentos no puede ser mayor a 10.',
            'minutosInactividad.required' => 'Los minutos de inactividad son obligatorios.',
            'minutosInactividad.min' => 'Los minutos de inactividad deben ser al menos 1.',
        ]);

        // Actualiza o crea el registro (asumiendo que solo hay uno con idParametro = 1)
        \App\Models\ParametroSistema::updateOrCreate(
            ['idParametro' => 1],
            [
                'intentosMaximos' => $request->intentosMaximos,
                'minutosInactividad' => $request->minutosInactividad,
            ]
        );

        return back()->with('success', 'Parámetros actualizados correctamente.');
    }
}