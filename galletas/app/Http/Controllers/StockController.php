<?php

namespace App\Http\Controllers;

use App\Services\JsonStorage;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Lista de productos con stock.
     */
    public function index()
    {
        $products = JsonStorage::getProducts();

        return view('stock.index', compact('products'));
    }

    /**
     * Actualizar stock de un producto.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $product = JsonStorage::findProduct((int) $id);

        if (!$product) {
            return redirect()->route('inventario.index')
                             ->with('error', 'Producto no encontrado.');
        }

        JsonStorage::updateProductStock((int) $id, $validated['quantity']);

        return redirect()->route('inventario.index')
                         ->with('success', "Stock de {$product->name} actualizado a {$validated['quantity']} ✅");
    }

    /**
     * Resetear todo el stock a 20.
     */
    public function create()
    {
        JsonStorage::resetStock(20);

        return redirect()->route('inventario.index')
                         ->with('success', '📦 Stock reseteado a 20 unidades por sabor.');
    }

    /**
     * No necesitamos store, edit, destroy.
     */
    public function store(Request $request)  { return redirect()->route('inventario.index'); }
    public function edit(string $id)         { return redirect()->route('inventario.index'); }
    public function destroy(string $id)      { return redirect()->route('inventario.index'); }
}