<?php

namespace App\Http\Controllers;

use App\Services\JsonStorage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $sales = JsonStorage::getSalesByDate($date);

        // Excluir pagos de deuda del conteo normal
        $normalSales = $sales->filter(fn($s) => ($s->sale_type ?? '') !== 'debt_payment');

        $totalEfectivo  = $sales->where('payment_method', 'efectivo')->sum('total');
        $totalNequi     = $sales->where('payment_method', 'nequi')->sum('total');
        $totalDaviplata = $sales->where('payment_method', 'daviplata')->sum('total');
        $totalGeneral   = $totalEfectivo + $totalNequi + $totalDaviplata;

        $totalVentas      = $normalSales->count();
        $ventasIndividual = $normalSales->where('sale_type', 'individual')->count();
        $ventasBowl       = $normalSales->where('sale_type', 'bowl')->count();

        // Pagos de deudas del día
        $debtPayments = JsonStorage::getDebtPaymentsByDate($date);
        $totalDebtPayments = $debtPayments->sum('total');

        // Ranking
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

        $latestSales = $normalSales->sortByDesc(fn($s) => $s->created_at)->take(20)->values();

        // Total deudas pendientes
        $totalPendingDebts = JsonStorage::getTotalPendingDebts();

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
