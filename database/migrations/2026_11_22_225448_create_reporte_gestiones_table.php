<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reporte_gestiones', function (Blueprint $table) {
            $table->id('idReporte');
            $table->date('fechaDesde');
            $table->date('fechaHasta');
            $table->decimal('totalIngresos', 12, 2)->default(0);
            $table->integer('membresiasActivas')->default(0);
            $table->integer('membresiasVencidas')->default(0);
            $table->integer('totalAsistencias')->default(0);

            $table->foreignId('idUsuarioGenera')->constrained('usuarios', 'idUsuario');
            
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('reporte_gestiones'); }
};