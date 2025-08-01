<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch' => [
                'id' => $this->batch->id ?? null,
                'name' => $this->batch->name ?? null,
                'animal_type' => [
                    'id' => $this->batch->animalType->id ?? null,
                    'name' => $this->batch->animalType->name ?? null,
                ] ?? null,
                'producer' => [
                    'id' => $this->batch->producer->id ?? null,
                    'name' => $this->batch->producer->name ?? null,
                    'email' => $this->batch->producer->email ?? null,
                ] ?? null,
                'quantity_available' => $this->batch->quantity_available ?? null,
                'unit_price_ars' => $this->batch->unit_price_ars ?? null,
                'unit_price_usd' => $this->batch->unit_price_usd ?? null,
                'created_at' => $this->batch->created_at ?? null,
            ] ?? null,
            'quantity_sold' => $this->quantity_sold,
            'unit_price_ars' => $this->unit_price_ars,
            'unit_price_usd' => $this->unit_price_usd,
            'total_amount_ars' => $this->total_amount_ars,
            'total_amount_usd' => $this->total_amount_usd,
            'sale_date' => $this->sale_date ? Carbon::parse($this->sale_date)->format('Y-m-d H:i:s') : null,
            'sale_date_formatted' => $this->sale_date ? Carbon::parse($this->sale_date)->format('d/m/Y H:i') : null,
            'buyer_name' => $this->buyer_name,
            'buyer_contact' => $this->buyer_contact,
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->getPaymentMethodLabel(),
            'notes' => $this->notes,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Obtener la etiqueta del método de pago
     */
    private function getPaymentMethodLabel(): ?string
    {
        $labels = [
            'cash' => 'Efectivo',
            'transfer' => 'Transferencia',
            'check' => 'Cheque',
            'credit' => 'Crédito',
        ];

        return $labels[$this->payment_method] ?? $this->payment_method;
    }
} 