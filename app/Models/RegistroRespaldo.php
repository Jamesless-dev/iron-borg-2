<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroRespaldo extends Model
{
    protected $table = 'registro_respaldo';
    protected $primaryKey = 'id_respaldo';

    // ← AGREGAR ESTA LÍNEA: la tabla ya tiene su propio "fecha_hora"
    public $timestamps = false;

    protected $fillable = [
        'id_usuario', 'fecha_hora', 'archivo', 'tamano_mb', 'reintentos', 'estado', 'detalle'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'tamano_mb' => 'decimal:2',
        'reintentos' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'idUsuario');
    }
}