<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'sale_type',
        'total',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'total' => 'integer',
    ];

    // ── Relación ──

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // ── Accessors ──

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 0, ',', '.');
    }

    public function getSaleTypeLabelAttribute(): string
    {
        return $this->sale_type === 'bowl' ? 'Bowl de 6' : 'Individual';
    }

    public function getPaymentLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'efectivo'  => '💵 Efectivo',
            'nequi'     => '💜 Nequi',
            'daviplata' => '🧡 Daviplata',
            default     => $this->payment_method,
        };
    }

    // ── Scopes ──

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByPayment($query, string $method)
    {
        return $query->where('payment_method', $method);
    }
}