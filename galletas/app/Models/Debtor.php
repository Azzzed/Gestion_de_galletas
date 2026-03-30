<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debtor extends Model
{
    protected $fillable = ['name', 'phone', 'notes'];

    // ── Relaciones ──────────────────────────────────────────────

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    // ── Accessors (calculados sobre relaciones cargadas) ─────────

    /**
     * Total pendiente en COP.
     * Requiere: withSum o eager load de debts.
     */
    public function getTotalPendingAttribute(): int
    {
        return $this->debts
            ->where('status', '!=', 'paid')
            ->sum(fn($d) => $d->total - $d->paid_amount);
    }

    public function getFormattedPendingAttribute(): string
    {
        return '$' . number_format($this->total_pending, 0, ',', '.');
    }

    public function getTotalPurchasesAttribute(): int
    {
        return $this->debts->count();
    }

    public function getPendingPurchasesAttribute(): int
    {
        return $this->debts->where('status', '!=', 'paid')->count();
    }

    public function getLastPurchaseDateAttribute(): ?Carbon
    {
        $last = $this->debts->sortByDesc('created_at')->first();
        return $last ? $last->created_at : null;
    }

    public function getDaysSincePurchaseAttribute(): ?int
    {
        return $this->last_purchase_date
            ? (int) $this->last_purchase_date->diffInDays(now())
            : null;
    }

    public function getHasAlertAttribute(): bool
    {
        return $this->total_pending > 50000
            || ($this->days_since_purchase !== null
                && $this->days_since_purchase > 7
                && $this->total_pending > 0);
    }
}
