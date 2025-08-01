<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relación: Un tipo de animal puede tener muchos lotes
     */
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * Relación: Un tipo de animal puede tener muchos registros de precio histórico
     */
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }
}
