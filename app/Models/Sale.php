<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'batch_id',
        'quantity_sold',
        'unit_price_ars',
        'unit_price_usd',
        'total_amount_ars',
        'total_amount_usd',
        'sale_date',
        'buyer_name',
        'buyer_contact',
        'payment_method',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'unit_price_ars' => 'decimal:2',
        'unit_price_usd' => 'decimal:2',
        'total_amount_ars' => 'decimal:2',
        'total_amount_usd' => 'decimal:2',
        'quantity_sold' => 'integer',
        'sale_date' => 'datetime',
    ];

    /**
     * Relación: Una venta pertenece a un lote
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Accessor para obtener el productor a través del lote
     */
    public function getProducerAttribute()
    {
        return $this->batch->producer;
    }

    /**
     * Scope para ventas del mes actual
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('sale_date', Carbon::now()->month)
                    ->whereYear('sale_date', Carbon::now()->year);
    }

    /**
     * Scope para ventas del año actual
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('sale_date', Carbon::now()->year);
    }
}
