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
        'batch_id',
        'date',
        'price_ars',
        'price_usd',
        'market_trend',
        'source',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'price_ars' => 'decimal:2',
        'price_usd' => 'decimal:2',
    ];

    /**
     * Relación: Un registro de precio histórico pertenece a un lote
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Scope para obtener datos recientes
     */
    public function scopeRecent($query, $months = 12)
    {
        return $query->where('date', '>=', now()->subMonths($months));
    }

    /**
     * Scope para obtener historial de un lote específico
     */
    public function scopeForBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope para obtener tendencia de precios
     */
    public function scopeWithTrend($query, $trend)
    {
        return $query->where('market_trend', $trend);
    }
}
