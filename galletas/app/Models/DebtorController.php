<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Debtor;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtorController extends Controller
{
    // ── LISTA ────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $query = Debtor::with(['debts'])->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });

        $debtors = $query->get()
            ->sortByDesc(fn($d) => ($d->has_alert ? 1_000_000_000 : 0) + $d->total_pending)
            ->values();

        $totalPending = Debt::where('status', '!=', 'paid')
            ->selectRaw('SUM(total - paid_amount) as total')
            ->value('total') ?? 0;

        return view('deudores.index', compact('debtors', 'search', 'totalPending'));
    }

    // ── CREAR ────────────────────────────────────────────────────

    public function create()
    {
        return view('deudores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $debtor = Debtor::create($validated);

        if ($request->wantsJson()) {
            $debtor->load('debts');
            return response()->json([
                'success' => true,
                'message' => "Deudor '{$debtor->name}' registrado exitosamente",
                'debtor'  => [
                    'id'               => $debtor->id,
                    'name'             => $debtor->name,
                    'phone'            => $debtor->phone,
                    'total_pending'    => 0,
                    'formatted_pending' => '$0',
                    'has_alert'        => false,
                ],
            ]);
        }

        return redirect()->route('deudores.index')
                         ->with('success', "Deudor '{$debtor->name}' registrado exitosamente ✅");
    }

    // ── DETALLE ──────────────────────────────────────────────────

    public function show(string $id)
    {
        $debtor = Debtor::with(['debts.payments'])->find($id);

        if (!$debtor) {
            return redirect()->route('deudores.index')->with('error', 'Deudor no encontrado');
        }

        // Enriquecer debts para la vista (compatible con show.blade.php)
        $debts = $debtor->debts->sortByDesc('created_at')->map(function (Debt $debt) {
            $debt->setAttribute('items', collect($debt->enriched_items));
            return $debt;
        })->values();

        return view('deudores.show', compact('debtor', 'debts'));
    }

    // ── EDITAR ───────────────────────────────────────────────────

    public function edit(string $id)
    {
        $debtor = Debtor::find($id);

        if (!$debtor) {
            return redirect()->route('deudores.index')->with('error', 'Deudor no encontrado');
        }

        return view('deudores.edit', compact('debtor'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $debtor = Debtor::find($id);

        if (!$debtor) {
            return redirect()->route('deudores.index')->with('error', 'Deudor no encontrado');
        }

        $debtor->update($validated);

        return redirect()->route('deudores.show', $id)
                         ->with('success', 'Deudor actualizado exitosamente ✅');
    }

    // ── ELIMINAR ─────────────────────────────────────────────────

    public function destroy(string $id)
    {
        $debtor = Debtor::with('debts')->find($id);

        if (!$debtor) {
            return redirect()->route('deudores.index')->with('error', 'Deudor no encontrado');
        }

        if ($debtor->total_pending > 0) {
            return redirect()->route('deudores.show', $id)
                             ->with('error', 'No se puede eliminar un deudor con deudas pendientes');
        }

        $debtor->delete();

        return redirect()->route('deudores.index')
                         ->with('success', "Deudor '{$debtor->name}' eliminado exitosamente");
    }

    // ── REGISTRAR PAGO ───────────────────────────────────────────

    public function registerPayment(Request $request, string $debtId)
    {
        $validated = $request->validate([
            'amount'         => 'required|integer|min:1000',
            'payment_method' => 'required|in:efectivo,nequi,daviplata',
        ]);

        $debt = Debt::find($debtId);

        if (!$debt) {
            return response()->json(['success' => false, 'message' => 'Deuda no encontrada'], 404);
        }

        if ($debt->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Esta deuda ya está pagada'], 422);
        }

        $amount = min($validated['amount'], $debt->remaining);

        DB::transaction(function () use ($debt, $amount, $validated) {
            DebtPayment::create([
                'debt_id'        => $debt->id,
                'amount'         => $amount,
                'payment_method' => $validated['payment_method'],
            ]);

            $newPaid = $debt->paid_amount + $amount;
            $status  = $newPaid >= $debt->total ? 'paid' : 'partial';

            $debt->update([
                'paid_amount' => min($newPaid, $debt->total),
                'status'      => $status,
            ]);

            // Registrar en ventas para el dashboard
            Sale::create([
                'sale_type'      => 'debt_payment',
                'total'          => $amount,
                'payment_method' => $validated['payment_method'],
                'debt_id'        => $debt->id,
                'notes'          => "Pago de deuda #{$debt->id}",
            ]);
        });

        $debt->refresh()->load('payments');

        return response()->json([
            'success'   => true,
            'message'   => $debt->status === 'paid'
                           ? '🎉 ¡Deuda pagada completamente!'
                           : '✅ Abono registrado exitosamente',
            'debt'      => $debt,
            'paid'      => '$' . number_format($amount, 0, ',', '.'),
            'remaining' => $debt->formatted_remaining,
        ]);
    }

    // ── API: LISTA DEUDORES ──────────────────────────────────────

    public function apiList()
    {
        $debtors = Debtor::with('debts')->get()->map(fn($d) => [
            'id'               => $d->id,
            'name'             => $d->name,
            'phone'            => $d->phone,
            'total_pending'    => $d->total_pending,
            'formatted_pending' => $d->formatted_pending,
            'has_alert'        => $d->has_alert,
        ]);

        return response()->json($debtors);
    }

    // ── API: CREAR DEUDA (FIADO DESDE POS) ──────────────────────

    public function createDebt(Request $request)
    {
        $validated = $request->validate([
            'debtor_id'          => 'required|integer|exists:debtors,id',
            'sale_type'          => 'required|in:individual,bowl',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|min:1|max:5',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        if ($validated['sale_type'] === 'bowl') {
            $totalItems = collect($validated['items'])->sum('quantity');
            if ($totalItems !== 6) {
                return response()->json(['success' => false, 'message' => 'El Bowl debe contener exactamente 6 galletas.'], 422);
            }
        }

        $debtItems = [];
        $total     = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 422);
            }

            $stock = Stock::where('product_id', $product->id)->first();
            if (!$stock || $stock->quantity < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente para {$product->name}. Disponible: " . ($stock?->quantity ?? 0),
                ], 422);
            }

            $subtotal    = $product->price * $item['quantity'];
            $debtItems[] = [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $product->price,
                'subtotal'   => $subtotal,
            ];
            $total += $subtotal;
        }

        if ($validated['sale_type'] === 'bowl') {
            $total = 60000;
        }

        $debtor = Debtor::find($validated['debtor_id']);

        DB::transaction(function () use ($validated, $debtItems, $total) {
            Debt::create([
                'debtor_id' => $validated['debtor_id'],
                'items'     => $debtItems,
                'total'     => $total,
                'sale_type' => $validated['sale_type'],
            ]);

            foreach ($validated['items'] as $item) {
                Stock::where('product_id', $item['product_id'])
                     ->decrement('quantity', $item['quantity']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "📝 Venta fiada a {$debtor->name} por $" . number_format($total, 0, ',', '.'),
            'total'   => '$' . number_format($total, 0, ',', '.'),
        ]);
    }
}
