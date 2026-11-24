<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('planes', function (Blueprint $table) {
            $table->id('idPlan');
            $table->string('nombre', 50)->unique();
            $table->integer('duracionDias'); 
            $table->decimal('costo', 10, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('planes'); }
};