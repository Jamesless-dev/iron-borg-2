<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\ComprobantePago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PagoController extends Controller
{
    /** P-06 · Pantalla de registro de pago */
    public function index()
    {
        // CUS-16 FA-02: permite reimprimir comprobantes ya emitidos
        $comprobantes = ComprobantePago::with('pago.membresia.miembro')
            ->whereDate('fechaEmision', Carbon::today())
            ->orderByDesc('fechaEmision')
            ->limit(30)
            ->get()
            ->map(fn ($c) => $this->formatearComprobante($c));

        return view('pagos.index', ['comprobantes' => $comprobantes]);
    }

    /** CUS-08 + CUS-15 paso 2: miembro, membresía, costo y saldo pendiente */
    public function buscar(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '' || (mb_strlen($q) < 3 && !ctype_digit($q) && !preg_match('/^M-?\d+$/i', $q))) {
            return response()->json(['mensaje' => 'Ingrese un DNI, código o al menos 3 letras del nombre.'], 422);
        }

        $miembro = $this->ubicarMiembro($q);

        if (!$miembro) {
            return response()->json(['mensaje' => 'No se encontró ningún miembro con ese dato'], 404);
        }

        $membresia = Membresia::with('plan')
            ->where('idMiembro', $miembro->idMiembro)
            ->orderByDesc('idMembresia')
            ->first();

        if (!$membresia) {
            return response()->json(['mensaje' => 'El miembro no tiene membresía registrada'], 404);
        }

        return response()->json([
            'miembro' => [
                'idMiembro' => $miembro->idMiembro,
                'codigo'    => 'M-' . str_pad($miembro->idMiembro, 4, '0', STR_PAD_LEFT),
                'nombre'    => $miembro->nombre . ' ' . $miembro->apellido,
                'dni'       => $miembro->dni,
                'telefono'  => $miembro->telefono,
            ],
            'membresia' => [
                'idMembresia'    => $membresia->idMembresia,
                'plan'           => $membresia->plan->nombre,
                'costo'          => (float) $membresia->costo,
                'saldoPendiente' => (float) $membresia->saldoPendiente,
                'totalPagado'    => (float) Pago::where('idMembresia', $membresia->idMembresia)->sum('monto'),
                'fechaInicio'    => $membresia->fechaInicio->format('d/m/Y'),
                'fechaFin'       => $membresia->fechaFin->format('d/m/Y'),
                'estado'         => $membresia->estado,
            ],
        ]);
    }

    /** CUS-15 + CUS-16 · Pago y comprobante en UNA sola transacción (RNF-06) */
    public function store(Request $request)
    {
        $request->validate([
            'idMembresia' => ['required', 'integer', 'exists:membresias,idMembresia'],
            'monto'       => ['required', 'numeric', 'gt:0'],   // Lab 14 D-003: gt:0, no min:0
            'metodoPago'  => ['required', 'in:Efectivo,Tarjeta,Transferencia'],
        ], [
            'idMembresia.required' => 'Debe seleccionar al miembro antes de registrar el pago.',
            'monto.required'       => 'El monto es obligatorio.',
            'monto.numeric'        => 'El monto debe ser un valor numérico.',
            'monto.gt'             => 'El monto debe ser mayor a cero.',
            'metodoPago.required'  => 'Seleccione un método de pago.',
            'metodoPago.in'        => 'El método de pago seleccionado no es válido.',
        ]);

        try {
            $resultado = DB::transaction(function () use ($request) {
                // Bloqueo de fila: evita dos cobros simultáneos sobre el mismo saldo
                $membresia = Membresia::with('plan')->lockForUpdate()->findOrFail($request->idMembresia);
                $monto = round((float) $request->monto, 2);
                $saldo = round((float) $membresia->saldoPendiente, 2);

                // CUS-15 FA-02
                if ($monto > $saldo) {
                    throw ValidationException::withMessages([
                        'monto' => 'El monto supera el saldo pendiente (S/ ' . number_format($saldo, 2) . ').',
                    ]);
                }

                $pago = Pago::create([
                    'monto'             => $monto,
                    'metodoPago'        => $request->metodoPago,
                    'fechaPago'         => now(),
                    'idMembresia'       => $membresia->idMembresia,
                    'idUsuarioRegistra' => Auth::id(),
                ]);

                // CUS-16 paso 1: correlativo único. idPago proviene de la secuencia
                // de la BD, por lo que el número es correlativo y no se repite.
                $numero = str_pad((string) $pago->idPago, 6, '0', STR_PAD_LEFT);

                $comprobante = ComprobantePago::create([
                    'numero'       => $numero,
                    'fechaEmision' => now(),
                    'monto'        => $monto,
                    'idPago'       => $pago->idPago,
                ]);

                // RN-CA-01: saldo = costo del plan − total pagado
                $pagado = round((float) Pago::where('idMembresia', $membresia->idMembresia)->sum('monto'), 2);
                $nuevoSaldo = max(0, round((float) $membresia->costo - $pagado, 2));

                $membresia->update(['saldoPendiente' => $nuevoSaldo]);

                return [
                    'comprobante' => $this->formatearComprobante($comprobante->load('pago.membresia.miembro', 'pago.membresia.plan')),
                    'saldoPendiente' => $nuevoSaldo,
                ];
            });

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Pago registrado. Comprobante N.° ' . $resultado['comprobante']['numero']
                             . '. Saldo pendiente: S/ ' . number_format($resultado['saldoPendiente'], 2),
            ] + $resultado);

        } catch (ValidationException $e) {
            throw $e; // 422 con el mensaje en español
        } catch (\Throwable $e) {
            // CUS-16 FA-01: la transacción ya revirtió pago y comprobante
            report($e);
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No se pudo registrar el pago, intente nuevamente.',
            ], 500);
        }
    }

    /** CUS-16 FA-02: devuelve el MISMO comprobante, sin generar uno nuevo */
    public function comprobante($numero)
    {
        $c = ComprobantePago::with('pago.membresia.miembro', 'pago.membresia.plan')
            ->where('numero', $numero)
            ->firstOrFail();

        return response()->json($this->formatearComprobante($c));
    }

    /** CUS-16 paso 4: vista lista para imprimir */
    public function imprimir($numero)
    {
        $c = ComprobantePago::with('pago.membresia.miembro', 'pago.membresia.plan')
            ->where('numero', $numero)
            ->firstOrFail();

        return view('pagos.comprobante', ['c' => $this->formatearComprobante($c)]);
    }

    // ================= Helpers =================

    private function formatearComprobante(ComprobantePago $c): array
    {
        $pago = $c->pago;
        $membresia = $pago?->membresia;
        $miembro = $membresia?->miembro;

        return [
            'numero'      => $c->numero,
            'fechaEmision'=> $c->fechaEmision->format('d/m/Y H:i'),
            'monto'       => (float) $c->monto,
            'montoFmt'    => number_format((float) $c->monto, 2),
            'metodoPago'  => $pago->metodoPago ?? '—',
            'miembro'     => $miembro ? $miembro->nombre . ' ' . $miembro->apellido : 'Miembro no disponible',
            'dni'         => $miembro->dni ?? '—',
            'codigo'      => $miembro ? 'M-' . str_pad($miembro->idMiembro, 4, '0', STR_PAD_LEFT) : '—',
            'plan'        => $membresia?->plan?->nombre ?? '—',
            'vigencia'    => $membresia ? $membresia->fechaInicio->format('d/m/Y') . ' al ' . $membresia->fechaFin->format('d/m/Y') : '—',
            'costo'       => $membresia ? number_format((float) $membresia->costo, 2) : '0.00',
            'saldo'       => $membresia ? number_format((float) $membresia->saldoPendiente, 2) : '0.00',
            'atendidoPor' => Auth::user()->nombreUsuario ?? '—',
        ];
    }

    private function ubicarMiembro(string $q): ?Miembro
    {
        if (preg_match('/^M-?(\d+)$/i', $q, $m)) {
            return Miembro::find((int) $m[1]);
        }
        if (preg_match('/^\d{8}$/', $q)) {
            return Miembro::where('dni', $q)->first();
        }
        if (ctype_digit($q)) {
            return Miembro::find((int) $q);
        }
        return Miembro::where('nombre', 'like', "%{$q}%")
            ->orWhere('apellido', 'like', "%{$q}%")
            ->first();
    }
}