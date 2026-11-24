<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parametro_sistema', function (Blueprint $table) {
            $table->id('idParametro');
            $table->integer('intentosMaximos')->default(3); 
            $table->integer('minutosInactividad')->default(15); 
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametro_sistema');
    }
};
