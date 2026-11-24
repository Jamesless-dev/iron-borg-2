<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('miembros', function (Blueprint $table) {
            $table->id('idMiembro');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('dni', 8)->unique(); 
            $table->string('telefono', 20)->nullable();
            $table->date('fechaRegistro');
            $table->foreignId('idUsuarioRegistra')->constrained('usuarios', 'idUsuario');
            
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('miembros'); }
};