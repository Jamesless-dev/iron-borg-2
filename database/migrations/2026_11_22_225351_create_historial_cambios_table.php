<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('historial_cambios', function (Blueprint $table) {
            $table->id('idHistorial');
            $table->string('accion', 50);
            $table->string('valorAnterior')->nullable();
            $table->string('valorNuevo')->nullable();
            $table->timestamp('fechaHora')->useCurrent();

            $table->foreignId('idMembresia')->constrained('membresias', 'idMembresia');
            $table->foreignId('idUsuario')->nullable()->constrained('usuarios', 'idUsuario');
            
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('historial_cambios'); }
};