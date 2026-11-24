<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';
    protected $primaryKey = 'idAsistencia';

    protected $fillable = [
        'fechaHoraIngreso', 'fechaHoraFin', 'idMembresia', 'idUsuarioRegistra'
    ];

    protected $casts = [
        'fechaHoraIngreso' => 'datetime',
        'fechaHoraFin' => 'datetime',
    ];

    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'idMembresia', 'idMembresia');
    }

    public function usuarioRegistra()
    {
        return $this->belongsTo(Usuario::class, 'idUsuarioRegistra', 'idUsuario');
    }
}