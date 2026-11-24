<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Membresia;
use App\Models\HistorialCambio;



Schedule::call(function () {
    $membresiasVencidas = Membresia::where('estado', 'Activa')
        ->where('fechaFin', '<', now()->toDateString())
        ->get();

    foreach ($membresiasVencidas as $membresia) {
        $membresia->update(['estado' => 'Vencida']);
        
        HistorialCambio::create([
            'accion' => 'Vencimiento',
            'valorAnterior' => 'Activa',
            'valorNuevo' => 'Vencida',
            'fechaHora' => now(),
            'idMembresia' => $membresia->idMembresia,
            'idUsuario' => null, 
        ]);
    }
})->dailyAt('00:00');

Schedule::command('db:backup')->dailyAt('02:00'); 
Schedule::command('membresias:vencer')->dailyAt('00:00');
Schedule::command('db:respaldar')->dailyAt('02:00');
// CUS-20: todos los días a las 00:00
Schedule::command('membresias:vencer')->dailyAt('00:00')->withoutOverlapping();

// CUS-21: todos los días a las 02:00
Schedule::command('db:respaldar')->dailyAt('02:00')->withoutOverlapping();