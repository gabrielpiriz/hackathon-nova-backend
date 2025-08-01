<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'producer_id',
        'animal_type_id',
        'quantity',
        'age_months',
        'average_weight_kg',
        'suggested_price_ars',
        'suggested_price_usd',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'suggested_price_ars' => 'decimal:2',
        'suggested_price_usd' => 'decimal:2',
        'average_weight_kg' => 'decimal:2',
        'age_months' => 'integer',
        'quantity' => 'integer',
    ];

    /**
     * Relación: Un lote pertenece a un productor
     */
    public function producer()
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    /**
     * Relación: Un lote pertenece a un tipo de animal
     */
    public function animalType()
    {
        return $this->belongsTo(AnimalType::class);
    }

    /**
     * Relación: Un lote puede tener muchas ventas
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relación: Un lote puede tener muchos registros de precio histórico
     */
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Scope para lotes disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope para lotes vendidos
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }
}
