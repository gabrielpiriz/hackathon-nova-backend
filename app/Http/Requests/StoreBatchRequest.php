<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Por ahora permitimos a todos los usuarios autenticados
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'animal_type_id' => [
                'required',
                'integer',
                'exists:animal_types,id'
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:10000'
            ],
            'age_months' => [
                'required',
                'integer',
                'min:1',
                'max:120' // Máximo 10 años
            ],
            'average_weight_kg' => [
                'required',
                'numeric',
                'min:0.1',
                'max:2000.00',
                'regex:/^\d+(\.\d{1,2})?$/' // Máximo 2 decimales
            ],
            'suggested_price_ars' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'suggested_price_usd' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'animal_type_id.required' => 'El tipo de animal es obligatorio.',
            'animal_type_id.exists' => 'El tipo de animal seleccionado no existe.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
            'quantity.max' => 'La cantidad no puede exceder 10,000 animales.',
            'age_months.required' => 'La edad en meses es obligatoria.',
            'age_months.min' => 'La edad debe ser al menos 1 mes.',
            'age_months.max' => 'La edad no puede exceder 120 meses (10 años).',
            'average_weight_kg.required' => 'El peso promedio es obligatorio.',
            'average_weight_kg.min' => 'El peso debe ser al menos 0.1 kg.',
            'average_weight_kg.max' => 'El peso no puede exceder 2,000 kg.',
            'average_weight_kg.regex' => 'El peso debe tener máximo 2 decimales.',
            'suggested_price_ars.required' => 'El precio sugerido en ARS es obligatorio.',
            'suggested_price_ars.min' => 'El precio en ARS debe ser mayor a 0.',
            'suggested_price_ars.regex' => 'El precio en ARS debe tener máximo 2 decimales.',
            'suggested_price_usd.required' => 'El precio sugerido en USD es obligatorio.',
            'suggested_price_usd.min' => 'El precio en USD debe ser mayor a 0.',
            'suggested_price_usd.regex' => 'El precio en USD debe tener máximo 2 decimales.',
            'notes.max' => 'Las notas no pueden exceder 1,000 caracteres.'
        ];
    }

    /**
     * Get custom attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'animal_type_id' => 'tipo de animal',
            'quantity' => 'cantidad',
            'age_months' => 'edad en meses',
            'average_weight_kg' => 'peso promedio',
            'suggested_price_ars' => 'precio sugerido ARS',
            'suggested_price_usd' => 'precio sugerido USD',
            'notes' => 'notas'
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}
