<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'branch_id', 'activo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
        ];
    }

    // ── Relaciones ───────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // ── Helpers de rol ───────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    public function isVendedor(): bool
    {
        return $this->role === 'vendedor';
    }

    public function hasBranch(): bool
    {
        return ! is_null($this->branch_id);
    }

    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'superadmin' => '👑 Super Admin',
            'admin'      => '🏪 Admin Sucursal',
            'vendedor'   => '🛒 Vendedor',
            default      => $this->role,
        };
    }

    /**
     * ID del cliente de mostrador para la sucursal del usuario.
     * Usado en el POS para la venta por defecto.
     */
    public function mostradorCustomerId(): int
    {
        if (! $this->branch_id) return 1;
        return $this->branch?->mostradorId() ?? 1;
    }
}
