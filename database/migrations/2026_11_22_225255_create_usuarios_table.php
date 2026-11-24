<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('idUsuario');
            $table->string('nombreUsuario', 50)->unique();
            $table->string('passwordHash'); // RNF-01: bcrypt
            $table->enum('rol', ['Administrador', 'Recepcionista']);
            $table->enum('estadoCuenta', ['Activa', 'Bloqueada'])->default('Activa');
            $table->integer('intentosFallidos')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('usuarios'); }
};