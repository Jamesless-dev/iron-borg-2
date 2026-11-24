<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('idPago');
            $table->decimal('monto', 10, 2);
            $table->enum('metodoPago', ['Efectivo', 'Tarjeta', 'Transferencia']);
            $table->timestamp('fechaPago')->useCurrent();
            
            $table->foreignId('idMembresia')->constrained('membresias', 'idMembresia');
            $table->foreignId('idUsuarioRegistra')->nullable()->constrained('usuarios', 'idUsuario');
            
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pagos'); }
};