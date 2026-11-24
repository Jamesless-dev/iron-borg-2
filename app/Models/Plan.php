<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';
    protected $primaryKey = 'idPlan';

    protected $fillable = [
        'nombre', 'duracionDias', 'costo', 'activo'
    ];

    protected $casts = [
        'costo' => 'decimal:2',
        'activo' => 'boolean',
        'duracionDias' => 'integer',
    ];

    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'idPlan', 'idPlan');
    }
}