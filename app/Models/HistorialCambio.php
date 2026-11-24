<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialCambio extends Model
{
    use HasFactory;

    protected $table = 'historial_cambios';
    protected $primaryKey = 'idHistorial';

    protected $fillable = [
    'accion', 'valorAnterior', 'valorNuevo', 'detalle', 'fechaHora', 'idMembresia', 'idUsuario'
];

    protected $casts = [
        'fechaHora' => 'datetime',
    ];

    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'idMembresia', 'idMembresia');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }
}