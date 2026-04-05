<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\BranchAware;
class PromoCode extends Model
{
    use BranchAware;
    protected $fillable = [
        'code',
        'description',
        'type',
        'discount_value',
        'applicable_cookie_ids',
        'min_order_amount',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'discount_value'       => 'decimal:2',
        'min_order_amount'     => 'decimal:2',
        'applicable_cookie_ids'=> 'array',
        'valid_from'           => 'date',
        'valid_until'          => 'date',
        'is_active'            => 'boolean',
    ];

    // ── Accessors ────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'percentage'     => '% Descuento en pedido',
            'fixed_amount'   => 'Monto fijo de descuento',
            'free_delivery'  => 'Domicilio gratis',
            'cookie_discount'=> 'Descuento en galletas específicas',
            default          => $this->type,
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'percentage'     => 'percent',
            'fixed_amount'   => 'attach_money',
            'free_delivery'  => 'local_shipping',
            'cookie_discount'=> 'bakery_dining',
            default          => 'local_offer',
        };
    }

    public function getDiscountLabelAttribute(): string
    {
        return match ($this->type) {
            'percentage'     => number_format($this->discount_value, 0) . '%',
            'fixed_amount'   => '$' . number_format($this->discount_value, 0, ',', '.'),
            'free_delivery'  => 'Envío gratis',
            'cookie_discount'=> number_format($this->discount_value, 0) . '%',
            default          => (string) $this->discount_value,
        };
    }

    public function getIsValidNowAttribute(): bool
    {
        if (! $this->is_active) return false;

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;

        $today = Carbon::today();

        if ($this->valid_from && $today->lt($this->valid_from)) return false;
        if ($this->valid_until && $today->gt($this->valid_until)) return false;

        return true;
    }

    public function getApplicableCookiesAttribute()
    {
        if (empty($this->applicable_cookie_ids)) return collect();
        return Cookie::whereIn('id', $this->applicable_cookie_ids)->get();
    }

    // ── Validación y cálculo del descuento ───────────────────────

    /**
     * Validate the code and calculate the discount.
     *
     * @param float $orderSubtotal  Subtotal of items
     * @param bool  $hasDelivery    Whether the order has delivery
     * @param array $itemIds        Cookie IDs in the cart
     * @return array{valid: bool, message: string, discount_type: string, discount_amount: float}
     */
    public function calculateDiscount(float $orderSubtotal, bool $hasDelivery = false, array $itemIds = []): array
    {
        if (! $this->is_valid_now) {
            return ['valid' => false, 'message' => 'Código inválido o expirado.', 'discount_type' => '', 'discount_amount' => 0];
        }

        if ($orderSubtotal < $this->min_order_amount) {
            return [
                'valid'           => false,
                'message'         => 'El pedido mínimo para este código es $' . number_format($this->min_order_amount, 0, ',', '.'),
                'discount_type'   => '',
                'discount_amount' => 0,
            ];
        }

        $discountAmount = 0;
        $message = '';

        switch ($this->type) {
            case 'percentage':
                $discountAmount = round($orderSubtotal * ($this->discount_value / 100), 2);
                $message = number_format($this->discount_value, 0) . '% de descuento aplicado.';
                break;

            case 'fixed_amount':
                $discountAmount = min($this->discount_value, $orderSubtotal);
                $message = '$' . number_format($discountAmount, 0, ',', '.') . ' de descuento aplicado.';
                break;

            case 'free_delivery':
                if (! $hasDelivery) {
                    return ['valid' => false, 'message' => 'Este código solo aplica a pedidos con domicilio.', 'discount_type' => 'free_delivery', 'discount_amount' => 0];
                }
                $discountAmount = 0; // Se maneja en el controller (delivery_cost = 0)
                $message = 'Domicilio gratis aplicado.';
                break;

            case 'cookie_discount':
                $applicableIds = $this->applicable_cookie_ids ?? [];
                $matchingIds = array_intersect($itemIds, $applicableIds);
                if (empty($matchingIds)) {
                    return ['valid' => false, 'message' => 'Ninguna galleta del pedido aplica a este código.', 'discount_type' => 'cookie_discount', 'discount_amount' => 0];
                }
                $discountAmount = round($orderSubtotal * ($this->discount_value / 100), 2);
                $message = number_format($this->discount_value, 0) . '% de descuento en galletas seleccionadas.';
                break;
        }

        return [
            'valid'           => true,
            'message'         => $message,
            'discount_type'   => $this->type,
            'discount_amount' => $discountAmount,
        ];
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeActivos(Builder $q): Builder { return $q->where('is_active', true); }

    // ── Helpers ──────────────────────────────────────────────────

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
