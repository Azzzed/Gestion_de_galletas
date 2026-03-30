<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        // ── Totales por método de pago ──────────────────────────
        $salesByMethod = Sale::whereDate('created_at', $date)
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $totalEfectivo  = (int) ($salesByMethod['efectivo']  ?? 0);
        $totalNequi     = (int) ($salesByMethod['nequi']     ?? 0);
        $totalDaviplata = (int) ($salesByMethod['daviplata'] ?? 0);
        $totalGeneral   = $totalEfectivo + $totalNequi + $totalDaviplata;

        // ── Conteos de ventas normales ──────────────────────────
        $normalSales  = Sale::with(['items.product'])
            ->whereDate('created_at', $date)
            ->whereIn('sale_type', ['individual', 'bowl'])
            ->get();

        $totalVentas      = $normalSales->count();
        $ventasIndividual = $normalSales->where('sale_type', 'individual')->count();
        $ventasBowl       = $normalSales->where('sale_type', 'bowl')->count();

        // ── Pagos de deudas del día ─────────────────────────────
        $totalDebtPayments = (int) Sale::whereDate('created_at', $date)
            ->where('sale_type', 'debt_payment')
            ->sum('total');

        // ── Ranking de galletas ─────────────────────────────────
        $ranking = $normalSales
            ->flatMap(fn($s) => $s->items)
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                return (object) [
                    'product_id'     => $productId,
                    'total_vendidas' => $items->sum('quantity'),
                    'product'        => $items->first()->product,
                ];
            })
            ->sortByDesc('total_vendidas')
            ->values();

        $latestSales = $normalSales->sortByDesc('created_at')->take(20)->values();

        // ── Total deudas pendientes ─────────────────────────────
        $totalPendingDebts = (int) Debt::where('status', '!=', 'paid')
            ->selectRaw('SUM(total - paid_amount) as total')
            ->value('total') ?? 0;

        return view('dashboard.index', compact(
            'date',
            'totalEfectivo',
            'totalNequi',
            'totalDaviplata',
            'totalGeneral',
            'totalVentas',
            'ventasIndividual',
            'ventasBowl',
            'ranking',
            'latestSales',
            'totalPendingDebts',
            'totalDebtPayments'
        ));
    }
}
