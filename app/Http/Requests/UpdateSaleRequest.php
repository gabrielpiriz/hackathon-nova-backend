<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Batch;
use App\Models\Sale;

class UpdateSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $saleId = $this->route('sale');
        $sale = is_numeric($saleId) ? Sale::find($saleId) : $saleId;
        
        return [
            'batch_id' => [
                'sometimes',
                'integer',
                Rule::exists('batches', 'id')
            ],
            'quantity_sold' => [
                'sometimes',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($sale) {
                    if ($sale) {
                        $batchId = $this->input('batch_id', $sale->batch_id);
                        $originalQuantity = $sale->quantity_sold;
                        $quantityDifference = $value - $originalQuantity;
                        
                        if ($quantityDifference > 0) {
                            $batch = Batch::find($batchId);
                            if ($batch && $batch->quantity_available < $quantityDifference) {
                                $fail('La cantidad adicional excede el stock disponible del lote.');
                            }
                        }
                    }
                }
            ],
            'unit_price_ars' => [
                'sometimes',
                'numeric',
                'min:0',
                'decimal:0,2'
            ],
            'unit_price_usd' => [
                'sometimes',
                'numeric',
                'min:0',
                'decimal:0,2'
            ],
            'total_amount_ars' => [
                'sometimes',
                'numeric',
                'min:0',
                'decimal:0,2'
            ],
            'total_amount_usd' => [
                'sometimes',
                'numeric',
                'min:0',
                'decimal:0,2'
            ],
            'sale_date' => [
                'sometimes',
                'date',
                'before_or_equal:today'
            ],
            'buyer_name' => [
                'sometimes',
                'string',
                'max:255'
            ],
            'buyer_contact' => [
                'nullable',
                'string',
                'max:255'
            ],
            'payment_method' => [
                'nullable',
                Rule::in(['cash', 'transfer', 'check', 'credit'])
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'batch_id.integer' => 'El ID del lote debe ser un número entero.',
            'batch_id.exists' => 'El lote especificado no existe.',
            'quantity_sold.integer' => 'La cantidad vendida debe ser un número entero.',
            'quantity_sold.min' => 'La cantidad vendida debe ser al menos 1.',
            'unit_price_ars.numeric' => 'El precio unitario en pesos debe ser un número.',
            'unit_price_ars.min' => 'El precio unitario en pesos no puede ser negativo.',
            'unit_price_usd.numeric' => 'El precio unitario en dólares debe ser un número.',
            'unit_price_usd.min' => 'El precio unitario en dólares no puede ser negativo.',
            'total_amount_ars.numeric' => 'El monto total en pesos debe ser un número.',
            'total_amount_ars.min' => 'El monto total en pesos no puede ser negativo.',
            'total_amount_usd.numeric' => 'El monto total en dólares debe ser un número.',
            'total_amount_usd.min' => 'El monto total en dólares no puede ser negativo.',
            'sale_date.date' => 'La fecha de venta debe ser una fecha válida.',
            'sale_date.before_or_equal' => 'La fecha de venta no puede ser futura.',
            'buyer_name.string' => 'El nombre del comprador debe ser texto.',
            'buyer_name.max' => 'El nombre del comprador no puede exceder 255 caracteres.',
            'buyer_contact.string' => 'El contacto del comprador debe ser texto.',
            'buyer_contact.max' => 'El contacto del comprador no puede exceder 255 caracteres.',
            'payment_method.in' => 'El método de pago debe ser: cash, transfer, check o credit.',
            'notes.string' => 'Las notas deben ser texto.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'batch_id' => 'ID del lote',
            'quantity_sold' => 'cantidad vendida',
            'unit_price_ars' => 'precio unitario en pesos',
            'unit_price_usd' => 'precio unitario en dólares',
            'total_amount_ars' => 'monto total en pesos',
            'total_amount_usd' => 'monto total en dólares',
            'sale_date' => 'fecha de venta',
            'buyer_name' => 'nombre del comprador',
            'buyer_contact' => 'contacto del comprador',
            'payment_method' => 'método de pago',
            'notes' => 'notas',
        ];
    }
} 