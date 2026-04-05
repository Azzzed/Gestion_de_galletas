<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre', 'slug', 'direccion', 'telefono', 'ciudad', 'color', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    // ── Relaciones ───────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }

    public function vendedores(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'vendedor');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function cookies(): HasMany
    {
        return $this->hasMany(Cookie::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    // ── Helpers ──────────────────────────────────────────────────

    /** Crea el cliente de mostrador para esta sucursal */
    public function crearClienteMostrador(): Customer
    {
        return Customer::withoutGlobalScopes()->firstOrCreate(
            ['branch_id' => $this->id, 'nombre' => 'Cliente de Mostrador'],
            ['activo' => true]
        );
    }

    /** ID del cliente de mostrador de esta sucursal */
    public function mostradorId(): int
    {
        return Customer::withoutGlobalScopes()
            ->where('branch_id', $this->id)
            ->where('nombre', 'Cliente de Mostrador')
            ->value('id') ?? 1;
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
