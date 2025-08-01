<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SaleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'total_amount_ars' => $this->collection->sum('total_amount_ars'),
                'total_amount_usd' => $this->collection->sum('total_amount_usd'),
                'total_quantity_sold' => $this->collection->sum('quantity_sold'),
            ],
        ];
    }
} 