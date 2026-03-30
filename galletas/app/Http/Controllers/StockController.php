<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::with('stock')->active()->get();

        return view('stock.index', compact('products'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $product = Product::find($id);

        if (!$product) {
            return redirect()->route('inventario.index')->with('error', 'Producto no encontrado.');
        }

        Stock::updateOrCreate(
            ['product_id' => $product->id],
            ['quantity'   => $validated['quantity']]
        );

        return redirect()->route('inventario.index')
                         ->with('success', "Stock de {$product->name} actualizado a {$validated['quantity']} ✅");
    }

    /** Resetear todo el stock a 20. */
    public function create()
    {
        Product::all()->each(function ($product) {
            Stock::updateOrCreate(
                ['product_id' => $product->id],
                ['quantity'   => 20]
            );
        });

        return redirect()->route('inventario.index')
                         ->with('success', '📦 Stock reseteado a 20 unidades por sabor.');
    }

    public function store(Request $request)  { return redirect()->route('inventario.index'); }
    public function edit(string $id)         { return redirect()->route('inventario.index'); }
    public function destroy(string $id)      { return redirect()->route('inventario.index'); }
}
