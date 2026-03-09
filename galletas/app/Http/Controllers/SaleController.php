<?php

namespace App\Http\Controllers;

use App\Services\JsonStorage;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Vista POS — Catálogo de galletas.
     */
    public function index()
    {
        $products = JsonStorage::getProducts();

        return view('ventas.index', compact('products'));
    }

    /**
     * Registrar una venta.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_type'          => 'required|in:individual,bowl',
            'payment_method'     => 'required|in:efectivo,nequi,daviplata',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|min:1|max:5',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        // ── Validar Bowl = exactamente 6 ──
        if ($validated['sale_type'] === 'bowl') {
            $totalItems = collect($validated['items'])->sum('quantity');
            if ($totalItems !== 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'El Bowl debe contener exactamente 6 galletas.',
                ], 422);
            }
        }

        // ── Leer stock actual ──
        $stock = JsonStorage::getStock();
        $saleItems = [];
        $total = 0;

        foreach ($validated['items'] as $item) {
            $product = JsonStorage::findProduct($item['product_id']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado.',
                ], 422);
            }

            // Verificar stock
            $currentStock = $stock[$item['product_id']] ?? 0;
            if ($currentStock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente para {$product->name}. Disponible: {$currentStock}",
                ], 422);
            }

            $unitPrice = $product->price;
            $subtotal  = $unitPrice * $item['quantity'];

            $saleItems[] = [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $unitPrice,
                'subtotal'   => $subtotal,
            ];

            $total += $subtotal;
        }

        // ── Bowl siempre $60.000 ──
        if ($validated['sale_type'] === 'bowl') {
            $total = 60000;
        }

        // ── Descontar stock ──
        foreach ($validated['items'] as $item) {
            $stock[$item['product_id']] -= $item['quantity'];
        }
        JsonStorage::saveStock($stock);

        // ── Guardar venta en JSON ──
        $saleId = JsonStorage::addSale([
            'sale_type'      => $validated['sale_type'],
            'total'          => $total,
            'payment_method' => $validated['payment_method'],
            'items'          => $saleItems,
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Venta registrada exitosamente! 🍪',
            'sale_id' => $saleId,
            'total'   => '$' . number_format($total, 0, ',', '.'),
        ]);
    }
}
