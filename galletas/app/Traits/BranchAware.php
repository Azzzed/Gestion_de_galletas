<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * BranchAware — Trait para aislar datos por sucursal automáticamente.
 *
 * Usar en: Customer, Cookie, Sale, DeliveryOrder, PromoCode
 *
 * Comportamiento:
 *  - Si el usuario autenticado tiene branch_id → filtra automáticamente
 *  - Si es superadmin (branch_id=null) → sin filtro (ve todo)
 *  - Al crear un registro → asigna branch_id automáticamente
 */
trait BranchAware
{
    protected static function bootBranchAware(): void
    {
        // ── Global scope: filtra por branch_id del usuario logueado ──
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && auth()->user()->branch_id) {
                $builder->where(
                    $builder->getModel()->getTable() . '.branch_id',
                    auth()->user()->branch_id
                );
            }
            // superadmin (branch_id=null) no recibe filtro → ve todo
        });

        // ── Auto-asignar branch_id al crear ──────────────────────────
        static::creating(function ($model) {
            if (empty($model->branch_id) && auth()->check() && auth()->user()->branch_id) {
                $model->branch_id = auth()->user()->branch_id;
            }
        });
    }
}
