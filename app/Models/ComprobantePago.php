<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprobantePago extends Model
{
    use HasFactory;

    protected $table = 'comprobante_pagos';
    
    protected $primaryKey = 'numero';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'numero', 'fechaEmision', 'monto', 'idPago'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fechaEmision' => 'datetime',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'idPago', 'idPago');
    }
}