<?php

namespace App\Http\Controllers;

use App\Services\JsonStorage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        // ── Ventas del día ──
        $sales = JsonStorage::getSalesByDate($date);

        // ── Totales por método de pago ──
        $totalEfectivo  = $sales->where('payment_method', 'efectivo')->sum('total');
        $totalNequi     = $sales->where('payment_method', 'nequi')->sum('total');
        $totalDaviplata = $sales->where('payment_method', 'daviplata')->sum('total');
        $totalGeneral   = $totalEfectivo + $totalNequi + $totalDaviplata;

        // ── Conteos ──
        $totalVentas      = $sales->count();
        $ventasIndividual = $sales->where('sale_type', 'individual')->count();
        $ventasBowl       = $sales->where('sale_type', 'bowl')->count();

        // ── Ranking de galletas más vendidas ──
        $ranking = $sales
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

        // ── Últimas ventas ──
        $latestSales = $sales
            ->sortByDesc(fn($s) => $s->created_at)
            ->take(20)
            ->values();

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
            'latestSales'
        ));
    }
}