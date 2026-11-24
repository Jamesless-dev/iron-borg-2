<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'idPago';

    protected $fillable = [
        'monto', 'metodoPago', 'fechaPago', 'idMembresia', 'idUsuarioRegistra'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fechaPago' => 'datetime',
    ];

    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'idMembresia', 'idMembresia');
    }

    public function usuarioRegistra()
    {
        return $this->belongsTo(Usuario::class, 'idUsuarioRegistra', 'idUsuario');
    }

    // Relación 1 a 1 con ComprobantePago
    public function comprobante()
    {
        return $this->hasOne(ComprobantePago::class, 'idPago', 'idPago');
    }
}