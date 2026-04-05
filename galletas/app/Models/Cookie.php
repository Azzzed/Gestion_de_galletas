<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Traits\BranchAware;

class Cookie extends Model
{
    use SoftDeletes;
    use BranchAware;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'rellenos',
        'tamano',
        'imagen_path',
        'stock',
        'pausado',
        'activo',
    ];

    protected $casts = [
        'precio'   => 'decimal:2',
        'rellenos' => 'array',
        'pausado'  => 'boolean',
        'activo'   => 'boolean',
        'stock'    => 'integer',
    ];

    // ── Relaciones ───────────────────────────────────────────────

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // ── Accessors ────────────────────────────────────────────────

    public function getPrecioFormateadoAttribute(): string
    {
        return '$' . number_format($this->precio, 0, ',', '.');
    }

    public function getImagenUrlAttribute(): string
    {
        return $this->imagen_path
            ? Storage::url($this->imagen_path)
            : null;
    }

    public function getRellenosTagsAttribute(): string
    {
        return collect($this->rellenos)->implode(', ');
    }

    public function getTamanoLabelAttribute(): string
    {
        return match ($this->tamano) {
            'pequeña' => '🍪 Pequeña',
            'grande'  => '🍪🍪🍪 Grande',
            default   => '🍪🍪 Mediana',
        };
    }

    public function getDisponiblePosAttribute(): bool
    {
        return $this->activo && ! $this->pausado && $this->stock > 0;
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeDisponiblePos(Builder $query): Builder
    {
        return $query->where('activo', true)
                     ->where('pausado', false)
                     ->where('stock', '>', 0);
    }

    public function scopePausadas(Builder $query): Builder
    {
        return $query->where('pausado', true);
    }

    public function scopeBuscar(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nombre', 'ilike', "%{$term}%")
              ->orWhereRaw("rellenos::text ilike ?", ["%{$term}%"]);
        });
    }

    public function scopePorTamano(Builder $query, string $tamano): Builder
    {
        return $query->where('tamano', $tamano);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function tieneVentasAsociadas(): bool
    {
        return $this->saleItems()->exists();
    }
}
