<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'numero_factura', 'subtotal',
        'descuento', 'descuento_porcentaje', 'total',
        'metodo_pago', 'metodos_pago', 'estado',
        'tiene_deuda', 'notas', 'cajero_id',
    ];

    protected $casts = [
        'subtotal'             => 'decimal:2',
        'descuento'            => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'total'                => 'decimal:2',
        'metodos_pago'         => 'array',
        'tiene_deuda'          => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (empty($sale->numero_factura)) {
                $ultimo = static::withTrashed()->max('id') ?? 0;
                $sale->numero_factura = 'CC-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relaciones ───────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    // ── Accessors ────────────────────────────────────────────────

    public function getTotalFormateadoAttribute(): string
    {
        return '$' . number_format($this->total, 0, ',', '.');
    }

    public function getEsMostradorAttribute(): bool
    {
        return $this->customer_id === Customer::MOSTRADOR_ID;
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('cantidad');
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeCompletadas(Builder $query): Builder
    {
        return $query->where('estado', 'completada');
    }

    public function scopePorPeriodo(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }

    public function scopeDeMostrador(Builder $query): Builder
    {
        return $query->where('customer_id', Customer::MOSTRADOR_ID);
    }

    public function scopeDeClientesEspecificos(Builder $query): Builder
    {
        return $query->where('customer_id', '!=', Customer::MOSTRADOR_ID);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function recalcularTotales(): void
    {
        $subtotal  = $this->items->sum('subtotal');
        $descuento = round($subtotal * ($this->descuento_porcentaje / 100), 2);
        $this->update([
            'subtotal'  => $subtotal,
            'descuento' => $descuento,
            'total'     => $subtotal - $descuento,
        ]);
    }

    public function anular(string $motivo = ''): void
    {
        $this->update(['estado' => 'anulada', 'notas' => $motivo]);
    }
}
