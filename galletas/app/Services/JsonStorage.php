<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JsonStorage
{
    // ══════════════════════════════════════════════
    // ══ CATÁLOGO FIJO DE GALLETAS (sin BD)     ══
    // ══════════════════════════════════════════════
    private static array $catalog = [
        1 => [
            'id'         => 1,
            'name'       => 'Nutella',
            'slug'       => 'nutella',
            'price'      => 10000,
            'image_path' => 'images/galletas/nutella.jpg',
            'color_hex'  => '#7B3F00',
        ],
        2 => [
            'id'         => 2,
            'name'       => 'Red Velvet',
            'slug'       => 'red-velvet',
            'price'      => 10000,
            'image_path' => 'images/galletas/red-velvet.jpg',
            'color_hex'  => '#DC143C',
        ],
        3 => [
            'id'         => 3,
            'name'       => 'Leche Klim',
            'slug'       => 'leche-klim',
            'price'      => 10000,
            'image_path' => 'images/galletas/leche-klim.jpg',
            'color_hex'  => '#F5F5DC',
        ],
        4 => [
            'id'         => 4,
            'name'       => 'Pie de Limón',
            'slug'       => 'pie-de-limon',
            'price'      => 10000,
            'image_path' => 'images/galletas/pie-de-limon.jpg',
            'color_hex'  => '#FDE047',
        ],
        5 => [
            'id'         => 5,
            'name'       => 'Nucita',
            'slug'       => 'nucita',
            'price'      => 10000,
            'image_path' => 'images/galletas/nucita.jpg',
            'color_hex'  => '#E2725B',
        ],
    ];

    // ══════════════════════════════════════════════
    // ══ RUTAS DE ARCHIVOS JSON                 ══
    // ══════════════════════════════════════════════
    private static function stockPath(): string
    {
        return storage_path('app/stock.json');
    }

    private static function salesPath(): string
    {
        return storage_path('app/sales.json');
    }

    // ══════════════════════════════════════════════
    // ══ STOCK — Lectura / Escritura            ══
    // ══════════════════════════════════════════════
    public static function getStock(): array
    {
        if (!file_exists(self::stockPath())) {
            self::resetStock();
        }

        return json_decode(file_get_contents(self::stockPath()), true);
    }

    public static function saveStock(array $stock): void
    {
        file_put_contents(
            self::stockPath(),
            json_encode($stock, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }

    public static function resetStock(int $default = 20): void
    {
        $stock = [];
        foreach (self::$catalog as $id => $p) {
            $stock[$id] = $default;
        }
        self::saveStock($stock);
    }

    public static function updateProductStock(int $productId, int $quantity): void
    {
        $stock = self::getStock();
        $stock[$productId] = max(0, $quantity);
        self::saveStock($stock);
    }

    // ══════════════════════════════════════════════
    // ══ PRODUCTOS — Devuelve objetos completos ══
    // ══════════════════════════════════════════════
    public static function getProducts(): Collection
    {
        $stock = self::getStock();

        return collect(self::$catalog)->map(function ($p) use ($stock) {
            $imageExists = file_exists(public_path($p['image_path']));

            return (object) [
                'id'              => $p['id'],
                'name'            => $p['name'],
                'slug'            => $p['slug'],
                'price'           => $p['price'],
                'image_path'      => $p['image_path'],
                'color_hex'       => $p['color_hex'],
                'is_active'       => true,
                'available_stock' => $stock[$p['id']] ?? 0,
                'formatted_price' => '$' . number_format($p['price'], 0, ',', '.'),
                'image_url'       => $imageExists ? asset($p['image_path']) : null,
            ];
        })->values();
    }

    public static function findProduct(int $id): ?object
    {
        return self::getProducts()->firstWhere('id', $id);
    }

    // ══════════════════════════════════════════════
    // ══ VENTAS — Persistencia en JSON          ══
    // ══════════════════════════════════════════════
    public static function getAllSales(): array
    {
        if (!file_exists(self::salesPath())) {
            file_put_contents(self::salesPath(), json_encode([], JSON_PRETTY_PRINT));
            return [];
        }

        return json_decode(file_get_contents(self::salesPath()), true) ?? [];
    }

    public static function addSale(array $sale): int
    {
        $sales = self::getAllSales();

        // Generar ID incremental
        $maxId = 0;
        foreach ($sales as $s) {
            if (($s['id'] ?? 0) > $maxId) {
                $maxId = $s['id'];
            }
        }

        $sale['id']         = $maxId + 1;
        $sale['created_at'] = now()->toDateTimeString();

        $sales[] = $sale;

        file_put_contents(
            self::salesPath(),
            json_encode($sales, JSON_PRETTY_PRINT),
            LOCK_EX
        );

        return $sale['id'];
    }

    // ══════════════════════════════════════════════
    // ══ VENTAS — Consultas por fecha           ══
    // ══════════════════════════════════════════════
    public static function getSalesByDate(string $date): Collection
    {
        $catalog = self::$catalog;

        $paymentLabels = [
            'efectivo'  => '💵 Efectivo',
            'nequi'     => '💜 Nequi',
            'daviplata' => '🧡 Daviplata',
        ];

        return collect(self::getAllSales())
            ->filter(fn($s) => str_starts_with($s['created_at'] ?? '', $date))
            ->map(function ($s) use ($catalog, $paymentLabels) {

                $items = collect($s['items'] ?? [])->map(function ($item) use ($catalog) {
                    $prod = $catalog[$item['product_id']] ?? [
                        'name'      => 'Desconocida',
                        'color_hex' => '#999999',
                    ];

                    return (object) [
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal'   => $item['subtotal'],
                        'product'    => (object) [
                            'name'      => $prod['name'],
                            'color_hex' => $prod['color_hex'],
                        ],
                    ];
                });

                return (object) [
                    'id'              => $s['id'],
                    'sale_type'       => $s['sale_type'],
                    'total'           => $s['total'],
                    'payment_method'  => $s['payment_method'],
                    'created_at'      => Carbon::parse($s['created_at']),
                    'items'           => $items,
                    'sale_type_label' => $s['sale_type'] === 'bowl' ? 'Bowl de 6' : 'Individual',
                    'payment_label'   => $paymentLabels[$s['payment_method']] ?? $s['payment_method'],
                    'formatted_total' => '$' . number_format($s['total'], 0, ',', '.'),
                ];
            })
            ->values();
    }
}