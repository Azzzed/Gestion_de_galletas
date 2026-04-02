<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cookie;
use App\Models\DeliveryOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        // KPIs del período
        $kpis = $this->kpis($desde, $hasta);

        return view('admin.stats.index', compact('kpis', 'desde', 'hasta'));
    }

    // ── API: Galletas más vendidas ────────────────────────────────

    public function topCookies(Request $request): JsonResponse
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $limit = (int) $request->get('limit', 8);

        $data = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('cookies', 'sale_items.cookie_id', '=', 'cookies.id')
            ->where('sales.estado', 'completada')
            ->whereDate('sales.created_at', '>=', $desde)
            ->whereDate('sales.created_at', '<=', $hasta)
            ->select(
                'cookies.id',
                'cookies.nombre',
                DB::raw('SUM(sale_items.cantidad) as total_vendidas'),
                DB::raw('SUM(sale_items.subtotal) as total_ingresos'),
            )
            ->groupBy('cookies.id', 'cookies.nombre')
            ->orderByDesc('total_vendidas')
            ->limit($limit)
            ->get();

        return response()->json([
            'labels'   => $data->pluck('nombre'),
            'vendidas' => $data->pluck('total_vendidas'),
            'ingresos' => $data->pluck('total_ingresos')->map(fn ($v) => (float) $v),
        ]);
    }

    // ── API: Ingresos por día (línea de tiempo) ───────────────────

    public function revenueTimeline(Request $request): JsonResponse
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        // SQLite compatible
        $data = Sale::completadas()
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->select(
                DB::raw("date(created_at) as dia"),
                DB::raw('SUM(total) as ingresos'),
                DB::raw('COUNT(*) as ventas'),
            )
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        // Completar días sin ventas
        $start    = \Carbon\Carbon::parse($desde);
        $end      = \Carbon\Carbon::parse($hasta);
        $all      = [];
        $labels   = [];
        $ingresos = [];
        $ventas   = [];

        $byDay = $data->keyBy('dia');

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key       = $d->toDateString();
            $labels[]  = $d->format('d/m');
            $ingresos[]= (float) ($byDay[$key]->ingresos ?? 0);
            $ventas[]  = (int)   ($byDay[$key]->ventas ?? 0);
        }

        return response()->json(compact('labels', 'ingresos', 'ventas'));
    }

    // ── API: Ventas por hora del día ──────────────────────────────

    public function salesByHour(Request $request): JsonResponse
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        // SQLite: strftime('%H', ...) devuelve la hora como '00'..'23'
        $data = Sale::completadas()
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->select(
                DB::raw("CAST(strftime('%H', created_at) AS INTEGER) as hora"),
                DB::raw('COUNT(*) as ventas'),
                DB::raw('SUM(total) as ingresos'),
            )
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->keyBy('hora');

        $labels   = [];
        $ventas   = [];
        $ingresos = [];

        for ($h = 0; $h < 24; $h++) {
            $labels[]  = sprintf('%02d:00', $h);
            $ventas[]  = (int)   ($data[$h]->ventas ?? 0);
            $ingresos[]= (float) ($data[$h]->ingresos ?? 0);
        }

        return response()->json(compact('labels', 'ventas', 'ingresos'));
    }

    // ── API: Ventas por método de pago ────────────────────────────

    public function byPaymentMethod(Request $request): JsonResponse
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        $data = Sale::completadas()
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as ventas'))
            ->groupBy('metodo_pago')
            ->get();

        $labelMap = ['efectivo' => 'Efectivo', 'transferencia' => 'Nequi/PSE', 'tarjeta' => 'Tarjeta'];

        return response()->json([
            'labels'  => $data->map(fn ($r) => $labelMap[$r->metodo_pago] ?? $r->metodo_pago),
            'totales' => $data->pluck('total')->map(fn ($v) => (float) $v),
            'ventas'  => $data->pluck('ventas'),
        ]);
    }

    // ── API: Tendencia semanal (últimas 8 semanas) ────────────────

    public function weeklyTrend(): JsonResponse
    {
        $data = Sale::completadas()
            ->where('created_at', '>=', now()->subWeeks(8))
            ->select(
                DB::raw("strftime('%Y-%W', created_at) as semana"),
                DB::raw('SUM(total) as ingresos'),
                DB::raw('COUNT(*) as ventas'),
            )
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();

        return response()->json([
            'labels'   => $data->pluck('semana'),
            'ingresos' => $data->pluck('ingresos')->map(fn ($v) => (float) $v),
            'ventas'   => $data->pluck('ventas'),
        ]);
    }

    // ── API: Domicilios por estado (hoy) ─────────────────────────

    public function deliverySummary(): JsonResponse
    {
        $counts = DeliveryOrder::whereDate('created_at', today())
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return response()->json([
            'scheduled'  => (int) ($counts['scheduled']->count ?? 0),
            'dispatched' => (int) ($counts['dispatched']->count ?? 0),
            'delivered'  => (int) ($counts['delivered']->count ?? 0),
            'revenue'    => (float) ($counts['delivered']->total ?? 0),
        ]);
    }

    // ── KPIs privados ─────────────────────────────────────────────

    private function kpis(string $desde, string $hasta): array
    {
        $base = Sale::completadas()
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta);

        $totalVentas   = (clone $base)->count();
        $totalIngresos = (clone $base)->sum('total');
        $ticketProm    = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;

        $topCookie = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('cookies', 'sale_items.cookie_id', '=', 'cookies.id')
            ->where('sales.estado', 'completada')
            ->whereDate('sales.created_at', '>=', $desde)
            ->whereDate('sales.created_at', '<=', $hasta)
            ->select('cookies.nombre', DB::raw('SUM(sale_items.cantidad) as total'))
            ->groupBy('cookies.nombre')
            ->orderByDesc('total')
            ->first();

        $domicilios = DeliveryOrder::whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->count();

        return [
            'total_ventas'   => $totalVentas,
            'total_ingresos' => (float) $totalIngresos,
            'ticket_promedio'=> round($ticketProm),
            'top_cookie'     => $topCookie?->nombre ?? '—',
            'domicilios'     => $domicilios,
        ];
    }
}
