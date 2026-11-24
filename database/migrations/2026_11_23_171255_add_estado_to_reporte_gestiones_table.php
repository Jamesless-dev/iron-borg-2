
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('reporte_gestiones', function (Blueprint $table) {
            $table->timestamp('fechaGeneracion')->nullable()->after('totalAsistencias');
            $table->string('estado', 20)->default('Generado')->after('fechaGeneracion');
        });
    }
    public function down(): void {
        Schema::table('reporte_gestiones', function (Blueprint $table) {
            $table->dropColumn(['fechaGeneracion', 'estado']);
        });
    }
};