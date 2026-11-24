<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarPagoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'idMembresia' => ['required', 'exists:membresias,idMembresia'],
            'monto'       => ['required', 'numeric', 'gt:0'], // Corregido: era min:0
            'metodoPago'  => ['required', 'in:Efectivo,Tarjeta,Transferencia'],
        ];
    }

    public function messages(): array
    {
        return [
            'monto.required' => 'El monto es obligatorio.',
            'monto.gt'       => 'El monto debe ser mayor a cero (S/ 0.01 mínimo).',
            'metodoPago.required' => 'Debe seleccionar un método de pago.',
        ];
    }
}