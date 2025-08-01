<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'animal_type_id',
        'date',
        'average_price_ars',
        'average_price_usd',
        'market_trend',
        'source',
        'weight_range_min',
        'weight_range_max',
        'age_range_min',
        'age_range_max',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'average_price_ars' => 'decimal:2',
        'average_price_usd' => 'decimal:2',
        'weight_range_min' => 'decimal:2',
        'weight_range_max' => 'decimal:2',
        'age_range_min' => 'integer',
        'age_range_max' => 'integer',
    ];

    /**
     * Relación: Un registro de precio histórico pertenece a un tipo de animal
     */
    public function animalType()
    {
        return $this->belongsTo(AnimalType::class);
    }

    /**
     * Scope para obtener datos recientes
     */
    public function scopeRecent($query, $months = 12)
    {
        return $query->where('date', '>=', now()->subMonths($months));
    }

    /**
     * Scope para filtrar por rango de peso
     */
    public function scopeForWeightRange($query, $minWeight, $maxWeight)
    {
        return $query->where(function ($q) use ($minWeight, $maxWeight) {
            $q->where('weight_range_min', '<=', $maxWeight)
              ->where('weight_range_max', '>=', $minWeight);
        });
    }

    /**
     * Scope para filtrar por rango de edad
     */
    public function scopeForAgeRange($query, $minAge, $maxAge)
    {
        return $query->where(function ($q) use ($minAge, $maxAge) {
            $q->where('age_range_min', '<=', $maxAge)
              ->where('age_range_max', '>=', $minAge);
        });
    }
}
