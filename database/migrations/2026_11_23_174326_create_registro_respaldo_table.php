// database/migrations/2026_11_24_000003_create_registro_respaldo_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('registro_respaldo', function (Blueprint $table) {
            $table->id('id_respaldo');
            $table->unsignedBigInteger('id_usuario')->nullable(); // SISTEMA si es automático
            $table->dateTime('fecha_hora');
            $table->string('archivo', 128);
            $table->decimal('tamano_mb', 10, 2)->nullable();
            $table->unsignedTinyInteger('reintentos')->default(0); // 0, 1 o 2
            $table->string('estado', 15)->default('Exitoso'); // Exitoso, Fallido, Con error
            $table->text('detalle')->nullable(); // mensaje de error o alerta
        });
    }
    public function down(): void {
        Schema::dropIfExists('registro_respaldo');
    }
};