<?php

namespace App\Console\Commands;

use App\Models\RegistroRespaldo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class RespaldarBaseDeDatos extends Command
{
    protected $signature = 'db:respaldar';
    protected $description = 'CUS-21: Genera respaldo diario de la BD, verifica integridad y registra el resultado';

    public function handle(): int
    {
        $ruta = storage_path('app/respaldos');
        if (!File::isDirectory($ruta)) {
            File::makeDirectory($ruta, 0755, true);
        }

        // Verificar espacio libre (mínimo 50 MB)
        $espacioLibre = disk_free_space($ruta);
        if ($espacioLibre !== false && $espacioLibre < 50 * 1024 * 1024) {
            // CUS-21 FA-01
            $registro = RegistroRespaldo::create([
                'fecha_hora' => now(),
                'archivo'    => 'N/A',
                'estado'     => 'Con error',
                'detalle'    => 'Espacio insuficiente en el servidor. No se generó el respaldo.',
            ]);
            Log::alert('CUS-21: espacio insuficiente. id_respaldo=' . $registro->id_respaldo);
            $this->error('Espacio insuficiente. Se alertó al Administrador.');
            return 1;
        }

        $nombre = 'respaldo_' . now()->format('Ymd_His') . '.sql';
        $archivo = "{$ruta}/{$nombre}";

        // Intento 1 + reintento 1 si falla integridad (CUS-21 FA-02)
        $maxIntentos = 2;
        for ($intento = 1; $intento <= $maxIntentos; $intento++) {
            try {
                // Generar dump (PostgreSQL; cambia a mysqldump si usas MySQL)
                $this->ejecutarDump($archivo);

                // Verificar integridad: archivo existe, no vacío y contiene "--"
                if (!$this->verificarIntegridad($archivo)) {
                    if ($intento < $maxIntentos) {
                        $this->warn("Integridad fallida, reintentando (intento {$intento}/{$maxIntentos})...");
                        continue;
                    }
                    throw new \RuntimeException('La verificación de integridad falló tras el reintento.');
                }

                $tamanoMB = round(filesize($archivo) / (1024 * 1024), 2);

                RegistroRespaldo::create([
                    'fecha_hora' => now(),
                    'archivo'    => $nombre,
                    'tamano_mb'  => $tamanoMB,
                    'reintentos' => $intento - 1,
                    'estado'     => 'Exitoso',
                ]);

                Log::info("CUS-21: respaldo exitoso {$nombre} ({$tamanoMB} MB, {$intento} intento(s)).");
                $this->info("Respaldo exitoso: {$nombre} ({$tamanoMB} MB)");
                return 0;

            } catch (\Throwable $e) {
                if ($intento < $maxIntentos) {
                    $this->warn("Error: {$e->getMessage()}. Reintentando...");
                    continue;
                }
                // CUS-21 FA-02: tras reintentar, marcar como Fallido
                RegistroRespaldo::create([
                    'fecha_hora' => now(),
                    'archivo'    => $nombre,
                    'reintentos' => $intento,
                    'estado'     => 'Fallido',
                    'detalle'    => $e->getMessage(),
                ]);
                Log::error('CUS-21: respaldo fallido tras reintento: ' . $e->getMessage());
                $this->error('Respaldo fallido tras reintento: ' . $e->getMessage());
                return 1;
            }
        }

        return 1;
    }

    private function ejecutarDump(string $archivo): void
    {
        $db = env('DB_DATABASE', 'iron_borg');
        $user = env('DB_USERNAME', 'postgres');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '5432');

        // PostgreSQL
        $cmd = "PGPASSWORD=" . escapeshellarg(env('DB_PASSWORD', ''))
             . " pg_dump -h {$host} -p {$port} -U {$user} -Fp -f " . escapeshellarg($archivo) . " {$db} 2>&1";
        exec($cmd, $salida, $codigo);

        if ($codigo !== 0) {
            throw new \RuntimeException('pg_dump falló: ' . implode("\n", $salida));
        }

        // Si usas MySQL, reemplaza por:
        // $cmd = "mysqldump -h {$host} -P {$port} -u {$user} -p" . env('DB_PASSWORD') . " {$db} > " . escapeshellarg($archivo);
    }

    private function verificarIntegridad(string $archivo): bool
    {
        if (!File::exists($archivo)) return false;
        if (File::size($archivo) === 0) return false;
        // Un dump válido contiene al menos un comentario SQL
        $contenido = File::get($archivo);
        return str_contains($contenido, '--') || str_contains($contenido, 'CREATE');
    }
}