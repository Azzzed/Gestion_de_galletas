<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    protected $fillable = [
        'debtor_id', 'items', 'total', 'paid_amount', 'status', 'sale_type',
    ];

    protected $casts = [
        'items'       => 'array',
        'total'       => 'integer',
        'paid_amount' => 'integer',
    ];

    // ── Relaciones ──────────────────────────────────────────────

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    // ── Accessors ───────────────────────────────────────────────

    public function getRemainingAttribute(): int
    {
        return $this->total - $this->paid_amount;
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 0, ',', '.');
    }

    public function getFormattedPaidAttribute(): string
    {
        return '$' . number_format($this->paid_amount, 0, ',', '.');
    }

    public function getFormattedRemainingAttribute(): string
    {
        return '$' . number_format($this->remaining, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => '🔴 Pendiente',
            'partial' => '🟡 Parcial',
            'paid'    => '🟢 Pagado',
            default   => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'red',
            'partial' => 'yellow',
            'paid'    => 'green',
            default   => 'gray',
        };
    }

    /**
     * Items enriquecidos con datos del catálogo (sin consulta adicional).
     */
    public function getEnrichedItemsAttribute(): array
    {
        $catalog = [
            1 => ['name' => 'Nutella',      'color_hex' => '#7B3F00', 'image_path' => 'images/galletas/nutella'],
            2 => ['name' => 'Red Velvet',   'color_hex' => '#DC143C', 'image_path' => 'images/galletas/red-velvet'],
            3 => ['name' => 'Leche Klim',   'color_hex' => '#F5F5DC', 'image_path' => 'images/galletas/leche-klim'],
            4 => ['name' => 'Pie de Limón', 'color_hex' => '#FDE047', 'image_path' => 'images/galletas/pie-de-limon'],
            5 => ['name' => 'Nucita',       'color_hex' => '#E2725B', 'image_path' => 'images/galletas/nucita'],
        ];

        return collect($this->items)->map(function ($item) use ($catalog) {
            $prod = $catalog[$item['product_id']] ?? [
                'name' => 'Desconocida', 'color_hex' => '#999', 'image_path' => '',
            ];
            $imageUrl = $this->findImageUrl($prod['image_path']);

            return (object) [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal'   => $item['subtotal'],
                'product'    => (object) [
                    'name'      => $prod['name'],
                    'color_hex' => $prod['color_hex'],
                    'image_url' => $imageUrl,
                ],
            ];
        })->all();
    }

    private function findImageUrl(string $basePath): ?string
    {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $full = $basePath . '.' . $ext;
            if (file_exists(public_path($full))) {
                return asset($full);
            }
        }
        return null;
    }
}
