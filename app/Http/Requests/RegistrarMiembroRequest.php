<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarMiembroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => ['required', 'digits:8', 'unique:miembros,dni'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'digits:9'],
            'idPlan' => ['required', 'exists:planes,idPlan'],
            'fechaInicio' => ['nullable', 'date'], // ← Cambiado: ahora es opcional
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.unique' => 'Este DNI ya se encuentra registrado en el sistema.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 100 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no debe exceder los 100 caracteres.',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos.',
            'idPlan.required' => 'Debe seleccionar un plan de membresía.',
            'idPlan.exists' => 'El plan seleccionado no es válido.',
        ];
    }
}