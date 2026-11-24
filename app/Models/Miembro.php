<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Miembro extends Model
{
    use HasFactory;

    protected $table = 'miembros';
    protected $primaryKey = 'idMiembro';

    protected $fillable = [
        'nombre', 'apellido', 'dni', 'telefono', 'fechaRegistro', 'idUsuarioRegistra'
    ];

    protected $casts = [
        'fechaRegistro' => 'date',
    ];

    // Relaciones
    public function usuarioRegistra()
    {
        return $this->belongsTo(Usuario::class, 'idUsuarioRegistra', 'idUsuario');
    }

    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'idMiembro', 'idMiembro');
    }

    /**
     * Última membresía registrada del miembro (de cualquier estado).
     * La vista muestra su estado (Activa / Vencida / Cancelada); si no hay
     * ninguna, muestra «Sin membresía». Se ordena por idMembresia para que
     * una membresía cancelada con fecha de inicio futura no oculte la nueva.
     */
    public function membresiaActual()
    {
        return $this->hasOne(Membresia::class, 'idMiembro', 'idMiembro')
                    ->latestOfMany('idMembresia');
    }
}