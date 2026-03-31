<?php
// ── SaleItem ─────────────────────────────────────────────────────

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'cookie_id', 'cantidad',
        'precio_unitario', 'descuento_item', 'subtotal', 'notas_item',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento_item'  => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'cantidad'        => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (SaleItem $item) {
            $item->subtotal = round(
                ($item->precio_unitario * $item->cantidad) - $item->descuento_item,
                2
            );
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function cookie(): BelongsTo
    {
        return $this->belongsTo(Cookie::class);
    }

    public function getSubtotalFormateadoAttribute(): string
    {
        return '$' . number_format($this->subtotal, 0, ',', '.');
    }
}
