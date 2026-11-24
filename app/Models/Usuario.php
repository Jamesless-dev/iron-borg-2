<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'idUsuario';

    public function getAuthPassword()
    {
        return $this->passwordHash;
    }

    protected $fillable = [
        'nombreUsuario',
        'passwordHash',
        'rol',
        'estadoCuenta',
        'intentosFallidos',
    ];

    protected $hidden = [
        'passwordHash', 
    ];

    protected $casts = [
        'intentosFallidos' => 'integer',
    ];

    public function miembrosRegistrados()
    {
        return $this->hasMany(Miembro::class, 'idUsuarioRegistra', 'idUsuario');
    }

    public function pagosRegistrados()
    {
        return $this->hasMany(Pago::class, 'idUsuarioRegistra', 'idUsuario');
    }

    public function asistenciasRegistradas()
    {
        return $this->hasMany(Asistencia::class, 'idUsuarioRegistra', 'idUsuario');
    }

    public function reportesGenerados()
    {
        return $this->hasMany(ReporteDeGestion::class, 'idUsuarioGenera', 'idUsuario');
    }

    public function historialCambios()
    {
        return $this->hasMany(HistorialCambio::class, 'idUsuario', 'idUsuario');
    }
}