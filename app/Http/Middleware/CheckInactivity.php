<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParametroSistema;
use Symfony\Component\HttpFoundation\Response;

class CheckInactivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Leer minutos desde la BD (fallback a 15 si no existe)
            // Lee la columna directa, fallback a 15 si no existe el registro
            $minutos = \App\Models\ParametroSistema::first()?->minutosInactividad ?? 15;
            $limite = $minutos * 60;

            $lastActivity = $request->session()->get('last_activity');
            $now = time();

            if ($lastActivity && ($now - $lastActivity > $limite)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('auth_error', 'Sesión finalizada por inactividad. Por favor, vuelva a iniciar sesión.');
            }

            $request->session()->put('last_activity', $now);
        }

        return $next($request);
    }
}