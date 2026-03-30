<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'sale_type',     // individual | bowl | debt_payment
        'total',
        'payment_method',
        'debt_id',
        'notes',
    ];

    protected $casts = [
        'total'   => 'integer',
        'debt_id' => 'integer',
    ];

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
        return match ($this->sale_type) {
            'bowl'         => 'Bowl de 6',
            'debt_payment' => 'Pago de deuda',
            default        => 'Individual',
        };
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

    public function scopeNormal($query)
    {
        return $query->whereIn('sale_type', ['individual', 'bowl']);
    }

    public function scopeDebtPayments($query)
    {
        return $query->where('sale_type', 'debt_payment');
    }
}
