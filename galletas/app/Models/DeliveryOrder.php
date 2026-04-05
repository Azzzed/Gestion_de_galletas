<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\BranchAware;
class DeliveryOrder extends Model
{
    use BranchAware;
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'delivery_neighborhood',
        'delivery_cost_type',
        'delivery_cost',
        'items',
        'subtotal',
        'discount_amount',
        'total',
        'payment_method',
        'payment_status',
        'paid_amount',
        'status',
        'promo_code',
        'notes',
        'scheduled_at',
        'cajero_id',
    ];

    protected $casts = [
        'items'          => 'array',
        'subtotal'       => 'decimal:2',
        'discount_amount'=> 'decimal:2',
        'total'          => 'decimal:2',
        'delivery_cost'  => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'scheduled_at'   => 'datetime',
    ];

    // ── Relaciones ───────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // ── Accessors ────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled'  => 'Agendado',
            'dispatched' => 'En Despacho',
            'delivered'  => 'Entregado',
            'cancelled'  => 'Cancelado',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled'  => 'amber',
            'dispatched' => 'blue',
            'delivered'  => 'green',
            'cancelled'  => 'red',
            default      => 'gray',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'scheduled'  => 'schedule',
            'dispatched' => 'local_shipping',
            'delivered'  => 'check_circle',
            'cancelled'  => 'cancel',
            default      => 'help',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'    => 'Pagado',
            'pending' => 'Pendiente',
            'partial' => 'Parcial',
            default   => $this->payment_status,
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash_on_delivery' => 'Paga al llegar',
            'transfer'         => 'Transferencia',
            default            => $this->payment_method,
        };
    }

    public function getDeliveryCostTypeLabelAttribute(): string
    {
        return match ($this->delivery_cost_type) {
            'free'       => 'Gratis',
            'additional' => 'Cobrado al cliente',
            'business'   => 'Lo asume el negocio',
            default      => $this->delivery_cost_type,
        };
    }

    public function getTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->total, 0, ',', '.');
    }

    public function getSubtotalFormattedAttribute(): string
    {
        return '$' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getDeliveryCostFormattedAttribute(): string
    {
        return '$' . number_format($this->delivery_cost, 0, ',', '.');
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->customer?->nombre ?? $this->customer_name ?? 'Cliente sin nombre';
    }

    public function getDisplayPhoneAttribute(): string
    {
        return $this->customer?->telefono ?? $this->customer_phone ?? '—';
    }

    // Items enriquecidos con datos de la cookie
    public function getEnrichedItemsAttribute(): array
    {
        return collect($this->items ?? [])->map(function ($item) {
            $cookie = Cookie::find($item['cookie_id'] ?? 0);
            return array_merge($item, [
                'nombre'     => $item['nombre'] ?? $cookie?->nombre ?? 'Desconocida',
                'imagen_url' => $cookie?->imagen_url,
            ]);
        })->toArray();
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeScheduled(Builder $q): Builder  { return $q->where('status', 'scheduled'); }
    public function scopeDispatched(Builder $q): Builder { return $q->where('status', 'dispatched'); }
    public function scopeDelivered(Builder $q): Builder  { return $q->where('status', 'delivered'); }
    public function scopeActive(Builder $q): Builder     { return $q->whereIn('status', ['scheduled', 'dispatched']); }

    public function scopeToday(Builder $q): Builder
    {
        return $q->whereDate('created_at', today());
    }
}
