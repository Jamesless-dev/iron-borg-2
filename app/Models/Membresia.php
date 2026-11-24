<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membresia extends Model
{
    use HasFactory;

    protected $table = 'membresias';
    protected $primaryKey = 'idMembresia';

    protected $fillable = [
        'fechaInicio', 'fechaFin', 'costo', 'saldoPendiente', 'estado', 'idMiembro', 'idPlan'
    ];

    protected $casts = [
        'fechaInicio' => 'date',
        'fechaFin' => 'date',
        'costo' => 'decimal:2',
        'saldoPendiente' => 'decimal:2',
    ];

    public function miembro()
    {
        return $this->belongsTo(Miembro::class, 'idMiembro', 'idMiembro');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'idPlan', 'idPlan');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'idMembresia', 'idMembresia');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'idMembresia', 'idMembresia');
    }

    public function historialCambios()
    {
        return $this->hasMany(HistorialCambio::class, 'idMembresia', 'idMembresia');
    }
}