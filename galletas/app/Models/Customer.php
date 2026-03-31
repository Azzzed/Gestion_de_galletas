<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use SoftDeletes;

    public const MOSTRADOR_ID = 1;

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'email',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ── Relaciones ───────────────────────────────────────────────

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    // ── Accessors ────────────────────────────────────────────────

    public function getEsMostradorAttribute(): bool
    {
        return $this->id === self::MOSTRADOR_ID;
    }

    public function getSaldoPendienteAttribute(): float
    {
        return (float) $this->debts()
            ->whereIn('estado', ['pendiente', 'pagada_parcial'])
            ->sum('monto_pendiente');
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeReales(Builder $query): Builder
    {
        return $query->where('id', '!=', self::MOSTRADOR_ID);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nombre', 'ilike', "%{$term}%")
              ->orWhere('telefono', 'ilike', "%{$term}%");
        });
    }

    // ── Helpers de negocio ───────────────────────────────────────

    public function tieneVentasAsociadas(): bool
    {
        return $this->sales()->withTrashed()->exists();
    }

    /** Top N galletas más compradas por este cliente */
    public function ventasFrecuentes(int $limit = 5)
    {
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('cookies', 'sale_items.cookie_id', '=', 'cookies.id')
            ->where('sales.customer_id', $this->id)
            ->where('sales.estado', 'completada')
            ->select(
                'cookies.id',
                'cookies.nombre',
                'cookies.imagen_path',
                \Illuminate\Support\Facades\DB::raw('SUM(sale_items.cantidad) AS total_comprado')
            )
            ->groupBy('cookies.id', 'cookies.nombre', 'cookies.imagen_path')
            ->orderByDesc('total_comprado')
            ->limit($limit)
            ->get();
    }
}
