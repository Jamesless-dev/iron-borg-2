// database/migrations/2026_11_24_000001_add_detalle_to_historial_cambios_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('historial_cambios', function (Blueprint $table) {
            $table->string('detalle', 255)->nullable()->after('valorNuevo');
        });
    }
    public function down(): void {
        Schema::table('historial_cambios', function (Blueprint $table) {
            $table->dropColumn('detalle');
        });
    }
};