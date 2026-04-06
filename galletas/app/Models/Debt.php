<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Debt;

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

      public function registerPayment(Request $request, DeliveryOrder $delivery): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
 
        $newPaid = $delivery->paid_amount + min((float) $request->amount, $delivery->remaining);
        $status  = $newPaid >= $delivery->total ? 'paid' : 'partial';
 
        $delivery->update([
            'paid_amount'    => min($newPaid, $delivery->total),
            'payment_status' => $status,
        ]);
 
        // ✅ FIX: Si quedó completamente pagado, marcar el Debt asociado como pagado
        // (aplica cuando el domicilio fue registrado como "fiado/debt")
        if ($status === 'paid' && $delivery->customer_id) {
            \App\Models\Debt::withoutGlobalScopes()
                ->where('customer_id', $delivery->customer_id)
                ->where('estado', '!=', 'pagada')
                ->whereRaw("notas LIKE ?", ['%DOM-' . str_pad($delivery->id, 4, '0', STR_PAD_LEFT) . '%'])
                ->each(function ($debt) use ($delivery) {
                    $debt->update([
                        'monto_pagado'    => $debt->monto_original,
                        'monto_pendiente' => 0,
                        'estado'          => 'pagada',
                    ]);
                });
        }
 
        return response()->json([
            'success'          => true,
            'message'          => $status === 'paid' ? '🎉 ¡Pago completo! Deuda saldada.' : 'Abono registrado.',
            'payment_status'   => $status,
            'paid_amount'      => $delivery->fresh()->paid_amount,
        ]);
    }
}
