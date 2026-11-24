<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarMiembroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // RN-OP-01 no restringe esta acción por rol
    }

    public function rules(): array
    {
        return [
            // El DNI debe seguir siendo único, ignorando al propio miembro (RF-05)
            'dni' => [
                'required',
                'digits:8',
                Rule::unique('miembros', 'dni')->ignore($this->route('idMiembro'), 'idMiembro'),
            ],
            'nombre'   => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'digits:9'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required'      => 'El DNI es obligatorio.',
            'dni.digits'        => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.unique'        => 'Este DNI ya pertenece a otro miembro registrado.',
            'nombre.required'   => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.digits'   => 'El teléfono debe tener 9 dígitos.',
        ];
    }
}