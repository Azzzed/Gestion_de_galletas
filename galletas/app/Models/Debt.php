<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Debt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'sale_id', 'monto_original',
        'monto_pendiente', 'monto_pagado', 'estado',
        'fecha_vencimiento', 'notas',
    ];

    protected $casts = [
        'monto_original'    => 'decimal:2',
        'monto_pendiente'   => 'decimal:2',
        'monto_pagado'      => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    // ── Relaciones ───────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pendiente', 'pagada_parcial']);
    }

    public function scopeVencidas(Builder $query): Builder
    {
        return $query->pendientes()
                     ->whereNotNull('fecha_vencimiento')
                     ->where('fecha_vencimiento', '<', now()->toDateString());
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function registrarPago(float $monto): void
    {
        $nuevoPagado   = $this->monto_pagado + $monto;
        $nuevoPendiente = max(0, $this->monto_original - $nuevoPagado);

        $this->update([
            'monto_pagado'    => $nuevoPagado,
            'monto_pendiente' => $nuevoPendiente,
            'estado'          => $nuevoPendiente <= 0 ? 'pagada' : 'pagada_parcial',
        ]);
    }
}
