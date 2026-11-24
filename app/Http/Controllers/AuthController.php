<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest; 
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request) 
    {
        $request->ensureIsNotRateLimited();

        $usuario = Usuario::where('nombreUsuario', $request->nombreUsuario)->first();

        if (!$usuario) {
            RateLimiter::hit($request->throttleKey());
            return back()->withErrors(['auth' => 'Usuario o contraseña incorrectos.']);
        }

        if ($usuario->estadoCuenta === 'Bloqueada') {
            return back()->withErrors(['auth' => 'Cuenta bloqueada. Comuníquese con el administrador.']);
        }

        if (!Hash::check($request->passwordHash, $usuario->passwordHash)) {
            RateLimiter::hit($request->throttleKey());
            
            $usuario->increment('intentosFallidos');
            if ($usuario->intentosFallidos >= 3) {
                $usuario->update(['estadoCuenta' => 'Bloqueada']);
                return back()->withErrors(['auth' => 'Cuenta bloqueada por 3 intentos fallidos.']);
            }
            
            return back()->withErrors(['auth' => 'Usuario o contraseña incorrectos.']);
        }

        RateLimiter::clear($request->throttleKey());
        $usuario->update(['intentosFallidos' => 0]);
        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}