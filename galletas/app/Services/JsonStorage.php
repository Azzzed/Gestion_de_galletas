<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JsonStorage
{
    // ══════════════════════════════════════════════════════════════
    // ══ CATÁLOGO FIJO DE GALLETAS                               ══
    // ══════════════════════════════════════════════════════════════
    private static array $catalog = [
        1 => [
            'id'         => 1,
            'name'       => 'Nutella',
            'slug'       => 'nutella',
            'price'      => 10000,
            'image_path' => 'images/galletas/nutella',
            'color_hex'  => '#7B3F00',
        ],
        2 => [
            'id'         => 2,
            'name'       => 'Red Velvet',
            'slug'       => 'red-velvet',
            'price'      => 10000,
            'image_path' => 'images/galletas/red-velvet',
            'color_hex'  => '#DC143C',
        ],
        3 => [
            'id'         => 3,
            'name'       => 'Leche Klim',
            'slug'       => 'leche-klim',
            'price'      => 10000,
            'image_path' => 'images/galletas/leche-klim',
            'color_hex'  => '#F5F5DC',
        ],
        4 => [
            'id'         => 4,
            'name'       => 'Pie de Limón',
            'slug'       => 'pie-de-limon',
            'price'      => 10000,
            'image_path' => 'images/galletas/pie-de-limon',
            'color_hex'  => '#FDE047',
        ],
        5 => [
            'id'         => 5,
            'name'       => 'Nucita',
            'slug'       => 'nucita',
            'price'      => 10000,
            'image_path' => 'images/galletas/nucita',
            'color_hex'  => '#E2725B',
        ],
    ];

    // ══════════════════════════════════════════════════════════════
    // ══ RUTAS DE ARCHIVOS JSON                                  ══
    // ══════════════════════════════════════════════════════════════
    private static function stockPath(): string
    {
        return storage_path('app/stock.json');
    }

    private static function salesPath(): string
    {
        return storage_path('app/sales.json');
    }

    private static function debtorsPath(): string
    {
        return storage_path('app/debtors.json');
    }

    private static function debtsPath(): string
    {
        return storage_path('app/debts.json');
    }

    // ══════════════════════════════════════════════════════════════
    // ══ UTILIDADES                                              ══
    // ══════════════════════════════════════════════════════════════
    private static function findImageUrl(string $basePath): ?string
    {
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];

        foreach ($extensions as $ext) {
            $fullPath = $basePath . '.' . $ext;
            if (file_exists(public_path($fullPath))) {
                return asset($fullPath);
            }
        }

        return null;
    }

    private static function readJson(string $path): array
    {
        if (!file_exists($path)) {
            file_put_contents($path, json_encode([], JSON_PRETTY_PRINT));
            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }

    private static function writeJson(string $path, array $data): void
    {
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    // ══════════════════════════════════════════════════════════════
    // ══ STOCK                                                   ══
    // ══════════════════════════════════════════════════════════════
    public static function getStock(): array
    {
        if (!file_exists(self::stockPath())) {
            self::resetStock();
        }

        return json_decode(file_get_contents(self::stockPath()), true);
    }

    public static function saveStock(array $stock): void
    {
        self::writeJson(self::stockPath(), $stock);
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

    // ══════════════════════════════════════════════════════════════
    // ══ PRODUCTOS                                               ══
    // ══════════════════════════════════════════════════════════════
    public static function getCatalog(): array
    {
        return self::$catalog;
    }

    public static function getProducts(): Collection
    {
        $stock = self::getStock();

        return collect(self::$catalog)->map(function ($p) use ($stock) {
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
                'image_url'       => self::findImageUrl($p['image_path']),
            ];
        })->values();
    }

    public static function findProduct(int $id): ?object
    {
        return self::getProducts()->firstWhere('id', $id);
    }

    // ══════════════════════════════════════════════════════════════
    // ══ VENTAS                                                  ══
    // ══════════════════════════════════════════════════════════════
    public static function getAllSales(): array
    {
        return self::readJson(self::salesPath());
    }

    public static function addSale(array $sale): int
    {
        $sales = self::getAllSales();

        $maxId = 0;
        foreach ($sales as $s) {
            if (($s['id'] ?? 0) > $maxId) {
                $maxId = $s['id'];
            }
        }

        $sale['id']         = $maxId + 1;
        $sale['created_at'] = now()->toDateTimeString();

        $sales[] = $sale;

        self::writeJson(self::salesPath(), $sales);

        return $sale['id'];
    }

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
                        'name'       => 'Desconocida',
                        'color_hex'  => '#999999',
                        'image_path' => '',
                    ];

                    return (object) [
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal'   => $item['subtotal'],
                        'product'    => (object) [
                            'name'      => $prod['name'],
                            'color_hex' => $prod['color_hex'],
                            'image_url' => self::findImageUrl($prod['image_path']),
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

    // ══════════════════════════════════════════════════════════════
    // ══ DEUDORES                                                ══
    // ══════════════════════════════════════════════════════════════
    public static function getAllDebtors(): array
    {
        return self::readJson(self::debtorsPath());
    }

    public static function getDebtors(): Collection
    {
        $debtors = self::getAllDebtors();
        $debts   = self::getAllDebts();

        return collect($debtors)->map(function ($d) use ($debts) {
            $debtorDebts = collect($debts)->where('debtor_id', $d['id']);

            $pendingDebts = $debtorDebts->filter(fn($debt) => $debt['status'] !== 'paid');
            $totalPending = $pendingDebts->sum(fn($debt) => $debt['total'] - ($debt['paid_amount'] ?? 0));
            $totalPurchases = $debtorDebts->count();

            $lastPurchase = $debtorDebts->sortByDesc('created_at')->first();
            $lastPurchaseDate = $lastPurchase ? Carbon::parse($lastPurchase['created_at']) : null;

            // Calcular días desde última compra
            $daysSinceLastPurchase = $lastPurchaseDate ? $lastPurchaseDate->diffInDays(now()) : null;

            // Alertas
            $hasAlert = $totalPending > 50000 || ($daysSinceLastPurchase !== null && $daysSinceLastPurchase > 7 && $totalPending > 0);

            return (object) [
                'id'                     => $d['id'],
                'name'                   => $d['name'],
                'phone'                  => $d['phone'] ?? '',
                'notes'                  => $d['notes'] ?? '',
                'total_pending'          => $totalPending,
                'formatted_pending'      => '$' . number_format($totalPending, 0, ',', '.'),
                'total_purchases'        => $totalPurchases,
                'pending_purchases'      => $pendingDebts->count(),
                'last_purchase_date'     => $lastPurchaseDate,
                'days_since_purchase'    => $daysSinceLastPurchase,
                'has_alert'              => $hasAlert,
                'created_at'             => Carbon::parse($d['created_at']),
            ];
        })->values();
    }

    public static function findDebtor(int $id): ?object
    {
        return self::getDebtors()->firstWhere('id', $id);
    }

    public static function createDebtor(array $data): int
    {
        $debtors = self::getAllDebtors();

        $maxId = 0;
        foreach ($debtors as $d) {
            if (($d['id'] ?? 0) > $maxId) {
                $maxId = $d['id'];
            }
        }

        $debtor = [
            'id'         => $maxId + 1,
            'name'       => $data['name'],
            'phone'      => $data['phone'] ?? '',
            'notes'      => $data['notes'] ?? '',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        $debtors[] = $debtor;

        self::writeJson(self::debtorsPath(), $debtors);

        return $debtor['id'];
    }

    public static function updateDebtor(int $id, array $data): bool
    {
        $debtors = self::getAllDebtors();

        foreach ($debtors as &$d) {
            if ($d['id'] === $id) {
                $d['name']       = $data['name'] ?? $d['name'];
                $d['phone']      = $data['phone'] ?? $d['phone'];
                $d['notes']      = $data['notes'] ?? $d['notes'];
                $d['updated_at'] = now()->toDateTimeString();

                self::writeJson(self::debtorsPath(), $debtors);
                return true;
            }
        }

        return false;
    }

    public static function deleteDebtor(int $id): bool
    {
        // Verificar que no tenga deudas pendientes
        $debtor = self::findDebtor($id);
        if (!$debtor || $debtor->total_pending > 0) {
            return false;
        }

        $debtors = self::getAllDebtors();
        $debtors = array_filter($debtors, fn($d) => $d['id'] !== $id);

        self::writeJson(self::debtorsPath(), array_values($debtors));
        return true;
    }

    public static function searchDebtors(string $query): Collection
    {
        $query = strtolower(trim($query));

        if (empty($query)) {
            return self::getDebtors();
        }

        return self::getDebtors()->filter(function ($d) use ($query) {
            return str_contains(strtolower($d->name), $query) ||
                   str_contains(strtolower($d->phone), $query);
        })->values();
    }

    // ══════════════════════════════════════════════════════════════
    // ══ DEUDAS                                                  ══
    // ══════════════════════════════════════════════════════════════
    public static function getAllDebts(): array
    {
        return self::readJson(self::debtsPath());
    }

    public static function getDebts(): Collection
    {
        $catalog = self::$catalog;
        $debtors = collect(self::getAllDebtors())->keyBy('id');

        return collect(self::getAllDebts())->map(function ($debt) use ($catalog, $debtors) {
            $items = collect($debt['items'] ?? [])->map(function ($item) use ($catalog) {
                $prod = $catalog[$item['product_id']] ?? [
                    'name'       => 'Desconocida',
                    'color_hex'  => '#999999',
                    'image_path' => '',
                ];

                return (object) [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal'   => $item['subtotal'],
                    'product'    => (object) [
                        'name'      => $prod['name'],
                        'color_hex' => $prod['color_hex'],
                        'image_url' => self::findImageUrl($prod['image_path']),
                    ],
                ];
            });

            $debtor = $debtors->get($debt['debtor_id']);
            $remaining = $debt['total'] - ($debt['paid_amount'] ?? 0);

            return (object) [
                'id'               => $debt['id'],
                'debtor_id'        => $debt['debtor_id'],
                'debtor_name'      => $debtor['name'] ?? 'Desconocido',
                'items'            => $items,
                'total'            => $debt['total'],
                'paid_amount'      => $debt['paid_amount'] ?? 0,
                'remaining'        => $remaining,
                'status'           => $debt['status'],
                'sale_type'        => $debt['sale_type'],
                'payments'         => collect($debt['payments'] ?? []),
                'formatted_total'  => '$' . number_format($debt['total'], 0, ',', '.'),
                'formatted_paid'   => '$' . number_format($debt['paid_amount'] ?? 0, 0, ',', '.'),
                'formatted_remaining' => '$' . number_format($remaining, 0, ',', '.'),
                'status_label'     => self::getDebtStatusLabel($debt['status']),
                'status_color'     => self::getDebtStatusColor($debt['status']),
                'created_at'       => Carbon::parse($debt['created_at']),
            ];
        })->values();
    }

    public static function getDebtsByDebtor(int $debtorId): Collection
    {
        return self::getDebts()->where('debtor_id', $debtorId)->sortByDesc('created_at')->values();
    }

    public static function findDebt(int $id): ?object
    {
        return self::getDebts()->firstWhere('id', $id);
    }

    public static function createDebt(array $data): int
    {
        $debts = self::getAllDebts();

        $maxId = 0;
        foreach ($debts as $d) {
            if (($d['id'] ?? 0) > $maxId) {
                $maxId = $d['id'];
            }
        }

        $debt = [
            'id'          => $maxId + 1,
            'debtor_id'   => $data['debtor_id'],
            'items'       => $data['items'],
            'total'       => $data['total'],
            'paid_amount' => 0,
            'status'      => 'pending',
            'sale_type'   => $data['sale_type'],
            'payments'    => [],
            'created_at'  => now()->toDateTimeString(),
            'updated_at'  => now()->toDateTimeString(),
        ];

        $debts[] = $debt;

        self::writeJson(self::debtsPath(), $debts);

        return $debt['id'];
    }

    public static function addPayment(int $debtId, int $amount, string $method): bool
    {
        $debts = self::getAllDebts();

        foreach ($debts as &$debt) {
            if ($debt['id'] === $debtId) {
                $debt['paid_amount'] = ($debt['paid_amount'] ?? 0) + $amount;

                $debt['payments'][] = [
                    'amount' => $amount,
                    'method' => $method,
                    'date'   => now()->toDateTimeString(),
                ];

                // Actualizar estado
                if ($debt['paid_amount'] >= $debt['total']) {
                    $debt['status'] = 'paid';
                    $debt['paid_amount'] = $debt['total']; // No exceder
                } else {
                    $debt['status'] = 'partial';
                }

                $debt['updated_at'] = now()->toDateTimeString();

                self::writeJson(self::debtsPath(), $debts);

                // Registrar en ventas si está pagado o agregar pago parcial
                self::addSale([
                    'sale_type'      => 'debt_payment',
                    'total'          => $amount,
                    'payment_method' => $method,
                    'items'          => [],
                    'debt_id'        => $debtId,
                    'notes'          => "Pago de deuda #{$debtId}",
                ]);

                return true;
            }
        }

        return false;
    }

    public static function getTotalPendingDebts(): int
    {
        return self::getDebts()
            ->where('status', '!=', 'paid')
            ->sum('remaining');
    }

    public static function getDebtStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => '🔴 Pendiente',
            'partial' => '🟡 Parcial',
            'paid'    => '🟢 Pagado',
            default   => $status,
        };
    }

    public static function getDebtStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => 'red',
            'partial' => 'yellow',
            'paid'    => 'green',
            default   => 'gray',
        };
    }

    // Obtener pagos del día para el dashboard
    public static function getDebtPaymentsByDate(string $date): Collection
    {
        return collect(self::getAllSales())
            ->filter(fn($s) => 
                str_starts_with($s['created_at'] ?? '', $date) && 
                ($s['sale_type'] ?? '') === 'debt_payment'
            )
            ->map(fn($s) => (object) [
                'total'          => $s['total'],
                'payment_method' => $s['payment_method'],
                'debt_id'        => $s['debt_id'] ?? null,
                'created_at'     => Carbon::parse($s['created_at']),
            ])
            ->values();
    }
}
