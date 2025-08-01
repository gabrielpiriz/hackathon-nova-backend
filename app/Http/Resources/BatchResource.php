<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
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
            'producer' => [
                'id' => $this->producer->id,
                'full_name' => $this->producer->full_name,
                'email' => $this->producer->email,
            ],
            'animal_type' => [
                'id' => $this->animalType->id,
                'name' => $this->animalType->name,
                'description' => $this->animalType->description,
            ],
            'quantity' => $this->quantity,
            'age_months' => $this->age_months,
            'average_weight_kg' => number_format($this->average_weight_kg, 2, '.', ''),
            'suggested_price_ars' => number_format($this->suggested_price_ars, 2, '.', ''),
            'suggested_price_usd' => number_format($this->suggested_price_usd, 2, '.', ''),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at->format('d/m/Y H:i'),
            'sales_count' => $this->sales->count(),
            'total_sold' => $this->sales->sum('quantity_sold'),
            'remaining_quantity' => $this->quantity - $this->sales->sum('quantity_sold'),
        ];
    }

    /**
     * Get the status label in Spanish
     *
     * @return string
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'available' => 'Disponible',
            'sold' => 'Vendido',
            'reserved' => 'Reservado',
            default => 'Desconocido'
        };
    }
}
