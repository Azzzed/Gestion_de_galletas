<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Vista POS — Catálogo de galletas.
     */
    public function index()
    {
        $products = Product::with('stock')->active()->get();

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

        // Validar Bowl = exactamente 6
        if ($validated['sale_type'] === 'bowl') {
            $totalItems = collect($validated['items'])->sum('quantity');
            if ($totalItems !== 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'El Bowl debe contener exactamente 6 galletas.',
                ], 422);
            }
        }

        // Verificar stock y armar items
        $saleItems = [];
        $total     = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 422);
            }

            $stock = Stock::where('product_id', $product->id)->lockForUpdate()->first();
            if (!$stock || $stock->quantity < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente para {$product->name}. Disponible: " . ($stock?->quantity ?? 0),
                ], 422);
            }

            $unitPrice  = $product->price;
            $subtotal   = $unitPrice * $item['quantity'];
            $saleItems[] = ['product' => $product, 'stock' => $stock, 'item' => $item, 'unit_price' => $unitPrice, 'subtotal' => $subtotal];
            $total      += $subtotal;
        }

        if ($validated['sale_type'] === 'bowl') {
            $total = 60000;
        }

        // Guardar todo en transacción
        $sale = DB::transaction(function () use ($validated, $saleItems, $total) {
            $sale = Sale::create([
                'sale_type'      => $validated['sale_type'],
                'total'          => $total,
                'payment_method' => $validated['payment_method'],
            ]);

            foreach ($saleItems as $entry) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $entry['item']['product_id'],
                    'quantity'   => $entry['item']['quantity'],
                    'unit_price' => $entry['unit_price'],
                    'subtotal'   => $entry['subtotal'],
                ]);

                $entry['stock']->decrement('quantity', $entry['item']['quantity']);
            }

            return $sale;
        });

        return response()->json([
            'success' => true,
            'message' => '¡Venta registrada exitosamente! 🍪',
            'sale_id' => $sale->id,
            'total'   => '$' . number_format($total, 0, ',', '.'),
        ]);
    }
}
