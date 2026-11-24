<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comprobante_pagos', function (Blueprint $table) {
            $table->string('numero', 20)->primary(); 
            $table->timestamp('fechaEmision')->useCurrent();
            $table->decimal('monto', 10, 2);

            $table->foreignId('idPago')->unique()->constrained('pagos', 'idPago');
            
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('comprobante_pagos'); }
};