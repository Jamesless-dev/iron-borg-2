<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id('idAsistencia');
            $table->timestamp('fechaHoraIngreso')->useCurrent();
            $table->timestamp('fechaHoraFin')->nullable();
            
            $table->foreignId('idMembresia')->constrained('membresias', 'idMembresia');
            $table->foreignId('idUsuarioRegistra')->nullable()->constrained('usuarios', 'idUsuario');
            
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('asistencias'); }
};