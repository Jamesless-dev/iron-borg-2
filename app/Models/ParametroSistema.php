<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametroSistema extends Model
{
    protected $table = 'parametro_sistema';
    protected $primaryKey = 'idParametro';

    // Estos son los campos que Laravel puede actualizar
    protected $fillable = [
        'intentosMaximos',
        'minutosInactividad',
    ];

    protected $casts = [
        'intentosMaximos' => 'integer',
        'minutosInactividad' => 'integer',
    ];
}