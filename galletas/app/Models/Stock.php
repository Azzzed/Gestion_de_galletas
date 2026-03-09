<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // ── Relación ──

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ── Métodos de negocio ──

    /**
     * Descontar unidades del stock.
     */
    public function decrement_stock(int $amount): bool
    {
        if ($this->quantity < $amount) {
            return false;
        }

        $this->decrement('quantity', $amount);
        return true;
    }

    /**
     * Agregar unidades al stock.
     */
    public function increment_stock(int $amount): void
    {
        $this->increment('quantity', $amount);
    }

    /**
     * ¿Hay stock suficiente?
     */
    public function hasEnough(int $amount): bool
    {
        return $this->quantity >= $amount;
    }
}