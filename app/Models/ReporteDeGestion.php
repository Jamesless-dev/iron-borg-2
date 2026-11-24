<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteDeGestion extends Model
{
    use HasFactory;

    protected $table = 'reporte_gestiones';
    protected $primaryKey = 'idReporte';

    protected $fillable = [
    'fechaDesde', 'fechaHasta', 'totalIngresos',
    'membresiasActivas', 'membresiasVencidas', 'totalAsistencias',
    'fechaGeneracion', 'estado', 'idUsuarioGenera'
];

    protected $casts = [
    'fechaDesde' => 'date',
    'fechaHasta' => 'date',
    'fechaGeneracion' => 'datetime',
    'totalIngresos' => 'decimal:2',
];

    public function usuarioGenera()
    {
        return $this->belongsTo(Usuario::class, 'idUsuarioGenera', 'idUsuario');
    }
}