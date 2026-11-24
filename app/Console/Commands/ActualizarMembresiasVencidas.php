<?php

namespace App\Console\Commands;

use App\Models\Membresia;
use App\Models\HistorialCambio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActualizarMembresiasVencidas extends Command
{
    protected $signature = 'membresias:vencer';
    protected $description = 'CUS-20: Cambia a Vencida las membresías expiradas y registra el historial';

    public function handle(): int
    {
        $hoy = now()->toDateString();

        try {
            $afectadas = Membresia::where('estado', 'Activa')
                ->where('fechaFin', '<', $hoy)
                ->get();

            $total = $afectadas->count();

            if ($total === 0) {
                // CUS-20 FA-01
                Log::info('CUS-20: 0 membresías actualizadas.');
                $this->info('0 membresías actualizadas.');
                return 0;
            }

            DB::beginTransaction();
            foreach ($afectadas as $m) {
                $m->update(['estado' => 'Vencida']);

                // CUS-11 FA-01: usuario = null (SISTEMA)
                HistorialCambio::create([
                    'accion'        => 'Vencimiento',
                    'valorAnterior' => 'Activa',
                    'valorNuevo'    => 'Vencida',
                    'fechaHora'     => now(),
                    'idMembresia'   => $m->idMembresia,
                    'idUsuario'     => null,
                ]);
            }
            DB::commit();

            Log::info("CUS-20: {$total} membresías actualizadas a Vencida.");
            $this->info("{$total} membresías actualizadas a Vencida.");
            return 0;

        } catch (\Throwable $e) {
            DB::rollBack();
            // CUS-20 FA-02
            Log::error('CUS-20: error al actualizar vencidas: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}