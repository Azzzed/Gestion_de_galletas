<?php

namespace App\Http\Controllers;

use App\Services\JsonStorage;
use Illuminate\Http\Request;

class DebtorController extends Controller
{
    /**
     * Lista de deudores.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        if ($search) {
            $debtors = JsonStorage::searchDebtors($search);
        } else {
            $debtors = JsonStorage::getDebtors();
        }

        // Ordenar: con alerta primero, luego por deuda pendiente
        $debtors = $debtors->sortByDesc(function ($d) {
            return ($d->has_alert ? 1000000000 : 0) + $d->total_pending;
        })->values();

        $totalPending = JsonStorage::getTotalPendingDebts();

        return view('deudores.index', compact('debtors', 'search', 'totalPending'));
    }

    /**
     * Formulario para crear deudor.
     */
    public function create()
    {
        return view('deudores.create');
    }

    /**
     * Guardar nuevo deudor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $id = JsonStorage::createDebtor($validated);

        if ($request->wantsJson()) {
            $debtor = JsonStorage::findDebtor($id);
            return response()->json([
                'success' => true,
                'message' => "Deudor '{$validated['name']}' registrado exitosamente",
                'debtor'  => $debtor,
            ]);
        }

        return redirect()->route('deudores.index')
                         ->with('success', "Deudor '{$validated['name']}' registrado exitosamente ✅");
    }

    /**
     * Ver detalle de un deudor con sus deudas.
     */
    public function show(string $id)
    {
        $debtor = JsonStorage::findDebtor((int) $id);

        if (!$debtor) {
            return redirect()->route('deudores.index')
                             ->with('error', 'Deudor no encontrado');
        }

        $debts = JsonStorage::getDebtsByDebtor((int) $id);

        return view('deudores.show', compact('debtor', 'debts'));
    }

    /**
     * Formulario para editar deudor.
     */
    public function edit(string $id)
    {
        $debtor = JsonStorage::findDebtor((int) $id);

        if (!$debtor) {
            return redirect()->route('deudores.index')
                             ->with('error', 'Deudor no encontrado');
        }

        return view('deudores.edit', compact('debtor'));
    }

    /**
     * Actualizar deudor.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $debtor = JsonStorage::findDebtor((int) $id);

        if (!$debtor) {
            return redirect()->route('deudores.index')
                             ->with('error', 'Deudor no encontrado');
        }

        JsonStorage::updateDebtor((int) $id, $validated);

        return redirect()->route('deudores.show', $id)
                         ->with('success', 'Deudor actualizado exitosamente ✅');
    }

    /**
     * Eliminar deudor (solo si no tiene deudas pendientes).
     */
    public function destroy(string $id)
    {
        $debtor = JsonStorage::findDebtor((int) $id);

        if (!$debtor) {
            return redirect()->route('deudores.index')
                             ->with('error', 'Deudor no encontrado');
        }

        if ($debtor->total_pending > 0) {
            return redirect()->route('deudores.show', $id)
                             ->with('error', 'No se puede eliminar un deudor con deudas pendientes');
        }

        JsonStorage::deleteDebtor((int) $id);

        return redirect()->route('deudores.index')
                         ->with('success', "Deudor '{$debtor->name}' eliminado exitosamente");
    }

    /**
     * Registrar pago de deuda.
     */
    public function registerPayment(Request $request, string $debtId)
    {
        $validated = $request->validate([
            'amount'         => 'required|integer|min:1000',
            'payment_method' => 'required|in:efectivo,nequi,daviplata',
        ]);

        $debt = JsonStorage::findDebt((int) $debtId);

        if (!$debt) {
            return response()->json([
                'success' => false,
                'message' => 'Deuda no encontrada',
            ], 404);
        }

        if ($debt->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Esta deuda ya está pagada',
            ], 422);
        }

        // No permitir pagar más del saldo pendiente
        $maxAmount = $debt->remaining;
        $amount = min($validated['amount'], $maxAmount);

        JsonStorage::addPayment((int) $debtId, $amount, $validated['payment_method']);

        $updatedDebt = JsonStorage::findDebt((int) $debtId);

        return response()->json([
            'success'   => true,
            'message'   => $updatedDebt->status === 'paid' 
                           ? '🎉 ¡Deuda pagada completamente!' 
                           : '✅ Abono registrado exitosamente',
            'debt'      => $updatedDebt,
            'paid'      => '$' . number_format($amount, 0, ',', '.'),
            'remaining' => $updatedDebt->formatted_remaining,
        ]);
    }

    /**
     * API: Obtener lista de deudores (para el modal del POS).
     */
    public function apiList()
    {
        $debtors = JsonStorage::getDebtors()->map(fn($d) => [
            'id'               => $d->id,
            'name'             => $d->name,
            'phone'            => $d->phone,
            'total_pending'    => $d->total_pending,
            'formatted_pending' => $d->formatted_pending,
            'has_alert'        => $d->has_alert,
        ]);

        return response()->json($debtors);
    }

    /**
     * API: Crear deuda (venta fiada desde POS).
     */
    public function createDebt(Request $request)
    {
        $validated = $request->validate([
            'debtor_id'          => 'required|integer',
            'sale_type'          => 'required|in:individual,bowl',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|min:1|max:5',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $debtor = JsonStorage::findDebtor($validated['debtor_id']);

        if (!$debtor) {
            return response()->json([
                'success' => false,
                'message' => 'Deudor no encontrado',
            ], 404);
        }

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

        // Verificar stock y calcular total
        $stock = JsonStorage::getStock();
        $debtItems = [];
        $total = 0;

        foreach ($validated['items'] as $item) {
            $product = JsonStorage::findProduct($item['product_id']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado.',
                ], 422);
            }

            $currentStock = $stock[$item['product_id']] ?? 0;
            if ($currentStock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente para {$product->name}. Disponible: {$currentStock}",
                ], 422);
            }

            $unitPrice = $product->price;
            $subtotal  = $unitPrice * $item['quantity'];

            $debtItems[] = [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $unitPrice,
                'subtotal'   => $subtotal,
            ];

            $total += $subtotal;
        }

        // Bowl siempre $60.000
        if ($validated['sale_type'] === 'bowl') {
            $total = 60000;
        }

        // Descontar stock
        foreach ($validated['items'] as $item) {
            $stock[$item['product_id']] -= $item['quantity'];
        }
        JsonStorage::saveStock($stock);

        // Crear la deuda
        $debtId = JsonStorage::createDebt([
            'debtor_id' => $validated['debtor_id'],
            'items'     => $debtItems,
            'total'     => $total,
            'sale_type' => $validated['sale_type'],
        ]);

        return response()->json([
            'success'  => true,
            'message'  => "📝 Venta fiada a {$debtor->name} por $" . number_format($total, 0, ',', '.'),
            'debt_id'  => $debtId,
            'total'    => '$' . number_format($total, 0, ',', '.'),
        ]);
    }
}
