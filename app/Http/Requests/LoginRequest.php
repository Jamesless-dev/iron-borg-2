<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombreUsuario' => ['required', 'string'],
            'passwordHash'  => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombreUsuario.required' => 'El campo usuario es obligatorio.',
            'passwordHash.required'  => 'El campo contraseña es obligatorio.',
        ];
    }

    public function ensureIsNotRateLimited(): void
    {
        // Lee la columna directa, fallback a 3 si no existe el registro
        $maxIntentos = \App\Models\ParametroSistema::first()?->intentosMaximos ?? 3;

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxIntentos)) {
            return;
        }

        throw ValidationException::withMessages([
            'nombreUsuario' => "Demasiados intentos fallidos. Su acceso ha sido bloqueado por 15 minutos.",
        ]);
    }

    public function throttleKey(): string
    {
        return $this->input('nombreUsuario') . '|' . $this->ip();
    }
}