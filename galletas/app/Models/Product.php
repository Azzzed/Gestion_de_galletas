<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'image_path',
        'color_hex',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'integer',
        'is_active' => 'boolean',
    ];

    // ── Relaciones ──

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // ── Accessors ──

    /**
     * Cantidad actual en inventario.
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->stock?->quantity ?? 0;
    }

    /**
     * Precio formateado en COP: "$10.000"
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 0, ',', '.');
    }

    /**
     * URL de imagen o null.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path && file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }

        return null;
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithAvailableStock($query)
    {
        return $query->with('stock')->active();
    }
}