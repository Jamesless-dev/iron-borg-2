<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use App\Models\Membresia;
use App\Models\Asistencia;
use App\Models\HistorialCambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AsistenciaController extends Controller
{
    /** P-05 · Control de asistencia */
    public function index(Request $request)
    {
        $fecha = $this->fechaConsulta($request->query('fecha'));

        return view('asistencias.index', [
            'fecha'       => $fecha->toDateString(),
            'hoy'         => Carbon::today()->toDateString(),
            'indicadores' => $this->indicadores(),
            'asistencias' => $this->listadoDelDia($fecha),
        ]);
    }

    /** Listado del día + indicadores (actualización en vivo) */
    public function dia(Request $request)
    {
        $fecha = $this->fechaConsulta($request->query('fecha'));

        return response()->json([
            'fecha'       => $fecha->toDateString(),
            'indicadores' => $this->indicadores(),
            'asistencias' => $this->listadoDelDia($fecha),
        ]);
    }

    /** CUS-08 + CUS-13 · Identificar al miembro y validar vigencia */
    public function identificar(Request $request)
    {
        $criterio = trim((string) $request->query('q', ''));

        if ($criterio === '') {
            return response()->json(['mensaje' => 'Ingrese un DNI o código de miembro.'], 422);
        }

        $miembro = $this->buscarPorDniOCodigo($criterio);

        if (!$miembro) {
            return response()->json(['mensaje' => 'Miembro no encontrado'], 404);
        }

        $membresia = $this->membresiaReciente($miembro->idMiembro);
        $hoy = Carbon::today();

        // CUS-13 FA-01 · fechaFin vencida con estado aún "Activa"
        if ($membresia && $membresia->estado === 'Activa' && $membresia->fechaFin->lt($hoy)) {
            $this->marcarVencida($membresia);
        }

        $vigente = $membresia
            && $membresia->estado === 'Activa'
            && $membresia->fechaFin->gte($hoy);

        $dentro = Asistencia::whereNull('fechaHoraFin')
            ->whereHas('membresia', fn ($q) => $q->where('idMiembro', $miembro->idMiembro))
            ->latest('fechaHoraIngreso')
            ->first();

        return response()->json([
            'miembro' => [
                'idMiembro' => $miembro->idMiembro,
                'codigo'    => 'M-' . str_pad($miembro->idMiembro, 4, '0', STR_PAD_LEFT),
                'nombre'    => $miembro->nombre . ' ' . $miembro->apellido,
                'dni'       => $miembro->dni,
            ],
            'membresia' => $membresia ? [
                'plan'           => $membresia->plan->nombre,
                'fechaFin'       => $membresia->fechaFin->format('d/m/Y'),
                'estado'         => $membresia->estado,
                'saldoPendiente' => (float) $membresia->saldoPendiente,
                'diasRestantes'  => max(0, (int) $hoy->diffInDays($membresia->fechaFin, false)),
            ] : null,
            'vigente' => $vigente,
            'dentro'  => $dentro ? [
                'idAsistencia' => $dentro->idAsistencia,
                'horaIngreso'  => $dentro->fechaHoraIngreso->format('H:i'),
            ] : null,
        ]);
    }

    /** CUS-12 · Registrar ingreso (revalida vigencia en el servidor) */
    public function registrar(Request $request)
    {
        $request->validate([
            'idMiembro' => ['required', 'integer', 'exists:miembros,idMiembro'],
        ], [
            'idMiembro.required' => 'Debe identificar al miembro antes de registrar el ingreso.',
            'idMiembro.exists'   => 'El miembro indicado no existe.',
        ]);

        $miembro   = Miembro::findOrFail($request->idMiembro);
        $membresia = $this->membresiaReciente($miembro->idMiembro);
        $hoy = Carbon::today();

        if ($membresia && $membresia->estado === 'Activa' && $membresia->fechaFin->lt($hoy)) {
            $this->marcarVencida($membresia);
        }

        // RN-ER-02 / RN-OP-02
        if (!$membresia || $membresia->estado !== 'Activa' || $membresia->fechaFin->lt($hoy)) {
            $mensaje = match ($membresia->estado ?? null) {
                'Vencida'   => 'Membresía VENCIDA el ' . $membresia->fechaFin->format('d/m/Y'),
                'Cancelada' => 'Membresía CANCELADA',
                default     => 'El miembro no tiene membresía registrada',
            };
            return response()->json(['ok' => false, 'mensaje' => $mensaje], 403);
        }

        // Evita doble ingreso abierto del mismo miembro
        $abierta = Asistencia::whereNull('fechaHoraFin')
            ->where('idMembresia', $membresia->idMembresia)
            ->latest('fechaHoraIngreso')
            ->first();

        if ($abierta) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'El miembro ya tiene un ingreso abierto a las ' . $abierta->fechaHoraIngreso->format('H:i') . '. Registre su salida primero.',
                'duplicado' => true,
            ], 409);
        }

        // RN-ES-03
        $asistencia = Asistencia::create([
            'fechaHoraIngreso'  => now(),
            'idMembresia'       => $membresia->idMembresia,
            'idUsuarioRegistra' => Auth::id(),
        ]);

        return response()->json([
            'ok'      => true,
            'hora'    => $asistencia->fechaHoraIngreso->format('H:i'),
            'miembro' => $miembro->nombre . ' ' . $miembro->apellido,
        ]);
    }

    /** Registrar salida del miembro (fechaHoraFin) */
    public function registrarSalida($idAsistencia)
    {
        $asistencia = Asistencia::with('membresia.miembro')->findOrFail($idAsistencia);

        if ($asistencia->fechaHoraFin) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'La salida ya fue registrada a las ' . $asistencia->fechaHoraFin->format('H:i'),
            ], 409);
        }

        $asistencia->update(['fechaHoraFin' => now()]);

        return response()->json([
            'ok'          => true,
            'hora'        => $asistencia->fechaHoraFin->format('H:i'),
            'permanencia' => $this->duracion($asistencia->fechaHoraIngreso, $asistencia->fechaHoraFin),
            'miembro'     => $asistencia->membresia?->miembro
                ? $asistencia->membresia->miembro->nombre . ' ' . $asistencia->membresia->miembro->apellido
                : 'Miembro',
        ]);
    }

    // ================= Helpers =================

    private function fechaConsulta($valor): Carbon
    {
        try {
            return $valor ? Carbon::parse($valor)->startOfDay() : Carbon::today();
        } catch (\Throwable $e) {
            return Carbon::today();
        }
    }

    private function indicadores(): array
    {
        $hoy = Carbon::today();

        return [
            'ingresosHoy' => Asistencia::whereDate('fechaHoraIngreso', $hoy)->count(),
            'sinSalida'   => Asistencia::whereDate('fechaHoraIngreso', $hoy)
                                ->whereNull('fechaHoraFin')->count(),
        ];
    }

    private function listadoDelDia(Carbon $fecha): array
    {
        return Asistencia::with('membresia.miembro')
            ->whereDate('fechaHoraIngreso', $fecha)
            ->orderByDesc('fechaHoraIngreso')
            ->get()
            ->map(function ($a) {
                $miembro = $a->membresia?->miembro;

                return [
                    'idAsistencia' => $a->idAsistencia,
                    'miembro'      => $miembro ? $miembro->nombre . ' ' . $miembro->apellido : 'Miembro no disponible',
                    'codigo'       => $miembro ? 'M-' . str_pad($miembro->idMiembro, 4, '0', STR_PAD_LEFT) : '—',
                    'dni'          => $miembro->dni ?? '—',
                    'fecha'        => $a->fechaHoraIngreso->format('d/m/Y'),
                    'horaIngreso'  => $a->fechaHoraIngreso->format('H:i'),
                    'horaSalida'   => $a->fechaHoraFin?->format('H:i'),
                    'permanencia'  => $a->fechaHoraFin
                        ? $this->duracion($a->fechaHoraIngreso, $a->fechaHoraFin)
                        : $this->duracion($a->fechaHoraIngreso, now()),
                ];
            })->all();
    }

    private function duracion(CarbonInterface $desde, ?CarbonInterface $hasta = null): string
    {
        $min = max(0, (int) $desde->diffInMinutes($hasta));
        $h = intdiv($min, 60);
        $m = $min % 60;

        return $h > 0 ? "{$h} h {$m} min" : "{$m} min";
    }

    private function buscarPorDniOCodigo(string $criterio): ?Miembro
    {
        if (preg_match('/^M-?(\d+)$/i', $criterio, $m)) {
            return Miembro::find((int) $m[1]);
        }
        if (preg_match('/^\d{8}$/', $criterio)) {
            return Miembro::where('dni', $criterio)->first();
        }
        if (ctype_digit($criterio)) {
            return Miembro::find((int) $criterio);
        }
        return null;
    }

    private function membresiaReciente(int $idMiembro): ?Membresia
    {
        return Membresia::with('plan')
            ->where('idMiembro', $idMiembro)
            ->orderByDesc('idMembresia')
            ->first();
    }

    private function marcarVencida(Membresia $membresia): void
    {
        DB::transaction(function () use ($membresia) {
            $membresia->update(['estado' => 'Vencida']);
            HistorialCambio::create([
                'accion'        => 'Vencimiento',
                'valorAnterior' => 'Activa',
                'valorNuevo'    => 'Vencida',
                'fechaHora'     => now(),
                'idMembresia'   => $membresia->idMembresia,
                'idUsuario'     => null, // SISTEMA (CUS-11 FA-01)
            ]);
        });
        $membresia->refresh();
    }
}