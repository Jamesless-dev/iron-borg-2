<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id('idMembresia');
            $table->date('fechaInicio');
            $table->date('fechaFin');
            $table->decimal('costo', 10, 2);
            $table->decimal('saldoPendiente', 10, 2)->default(0);
            $table->enum('estado', ['Activa', 'Vencida', 'Cancelada'])->default('Activa');
            
            $table->foreignId('idMiembro')->constrained('miembros', 'idMiembro');
            $table->foreignId('idPlan')->constrained('planes', 'idPlan');
            
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('membresias'); }
};