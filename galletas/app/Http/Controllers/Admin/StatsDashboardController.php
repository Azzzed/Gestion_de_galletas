<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\DeliveryOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsDashboardController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────

    private function isPgsql(): bool
    {
        return DB::getDriverName() === 'pgsql';
    }

    private function startOf(string $date): string { return $date . ' 00:00:00'; }
    private function endOf(string $date): string   { return $date . ' 23:59:59'; }

    private function dateExpr(string $col): string
    {
        return $this->isPgsql() ? "DATE_TRUNC('day', {$col})::date" : "date({$col})";
    }

    private function hourExpr(string $col): string
    {
        return $this->isPgsql()
            ? "EXTRACT(HOUR FROM {$col})::INTEGER"
            : "CAST(strftime('%H', {$col}) AS INTEGER)";
    }

    private function weekExpr(string $col): string
    {
        return $this->isPgsql()
            ? "TO_CHAR({$col}, 'IYYY-IW')"
            : "strftime('%Y-%W', {$col})";
    }

    /** branch_id del usuario actual. Superadmin → null → sin filtro */
    private function branchId(): ?int
    {
        return auth()->user()?->branch_id;
    }

    // ── Index ─────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $kpis  = $this->kpis($desde, $hasta);
        return view('admin.stats.index', compact('kpis', 'desde', 'hasta'));
    }

    // ── KPIs ──────────────────────────────────────────────────────

    private function kpis(string $desde, string $hasta): array
    {
        $branchId = $this->branchId();
        $ttlMin   = ($hasta === today()->toDateString()) ? 2 : 10;
        $cacheKey = "stats_kpis_{$branchId}_{$desde}_{$hasta}";

        return Cache::remember($cacheKey, now()->addMinutes($ttlMin), function () use ($desde, $hasta, $branchId) {

            $vQuery = DB::table('sales')
                ->where('estado', 'completada')
                ->whereNull('deleted_at')
                ->where('created_at', '>=', $this->startOf($desde))
                ->where('created_at', '<=', $this->endOf($hasta));
            if ($branchId) $vQuery->where('branch_id', $branchId);
            $vRow = $vQuery->selectRaw('COUNT(*) as total_ventas, COALESCE(SUM(total), 0) as ingresos')->first();

            $dQuery = DB::table('delivery_orders')
                ->whereNull('deleted_at')
                ->where('created_at', '>=', $this->startOf($desde))
                ->where('created_at', '<=', $this->endOf($hasta));
            if ($branchId) $dQuery->where('branch_id', $branchId);
            $dRow = $dQuery->selectRaw("
                COUNT(*) as total_dom,
                COALESCE(SUM(CASE WHEN payment_status IN ('paid','partial') THEN paid_amount ELSE 0 END), 0) as ingresos_deliv,
                COALESCE(SUM(CASE WHEN delivery_cost_type = 'additional' THEN delivery_cost ELSE 0 END), 0) as costo_envios
            ")->first();

            $topQuery = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('cookies', 'sale_items.cookie_id', '=', 'cookies.id')
                ->where('sales.estado', 'completada')
                ->whereNull('sales.deleted_at')
                ->where('sales.created_at', '>=', $this->startOf($desde))
                ->where('sales.created_at', '<=', $this->endOf($hasta));
            if ($branchId) $topQuery->where('sales.branch_id', $branchId);
            $topCookie = $topQuery
                ->select('cookies.nombre', DB::raw('SUM(sale_items.cantidad) as total'))
                ->groupBy('cookies.id', 'cookies.nombre')
                ->orderByDesc('total')->limit(1)->first();

            $totalVentas    = (int)   ($vRow->total_ventas   ?? 0);
            $ingresosVentas = (float) ($vRow->ingresos       ?? 0);
            $ingresosDeliv  = (float) ($dRow->ingresos_deliv ?? 0);
            $costoEnvios    = (float) ($dRow->costo_envios   ?? 0);

            return [
                'total_ventas'    => $totalVentas,
                'total_ingresos'  => $ingresosVentas + $ingresosDeliv,
                'ingresos_ventas' => $ingresosVentas,
                'ingresos_deliv'  => $ingresosDeliv,
                'costo_envios'    => $costoEnvios,
                'ticket_promedio' => $totalVentas > 0 ? round($ingresosVentas / $totalVentas) : 0,
                'top_cookie'      => $topCookie?->nombre ?? '—',
            ];
        });
    }

    // ── Revenue timeline ──────────────────────────────────────────

    public function revenueTimeline(Request $request): JsonResponse
    {
        $desde    = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta    = $request->get('hasta', today()->toDateString());
        $branchId = $this->branchId();
        $ttlMin   = ($hasta === today()->toDateString()) ? 2 : 10;
        $cacheKey = "stats_revenue_{$branchId}_{$desde}_{$hasta}";

        $result = Cache::remember($cacheKey, now()->addMinutes($ttlMin), function () use ($desde, $hasta, $branchId) {
            $dateExpr = $this->dateExpr('created_at');

            $vQuery = Sale::completadas()
                ->where('created_at', '>=', $this->startOf($desde))
                ->where('created_at', '<=', $this->endOf($hasta));
            if ($branchId) $vQuery->where('branch_id', $branchId);
            $ventasPorDia = $vQuery
                ->selectRaw("{$dateExpr} as fecha, SUM(total) as ingresos, COUNT(*) as ventas")
                ->groupBy('fecha')->orderBy('fecha')->get()->keyBy('fecha');

            $dQuery = DeliveryOrder::where('status', 'delivered')
                ->where('created_at', '>=', $this->startOf($desde))
                ->where('created_at', '<=', $this->endOf($hasta));
            if ($branchId) $dQuery->where('branch_id', $branchId);
            $delivPorDia = $dQuery
                ->selectRaw("{$dateExpr} as fecha, SUM(paid_amount) as ingresos, COUNT(*) as cantidad")
                ->groupBy('fecha')->get()->keyBy('fecha');

            $start  = \Carbon\Carbon::parse($desde);
            $end    = \Carbon\Carbon::parse($hasta);
            $labels = $ingArr = $ventArr = $delivArr = [];

            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->toDateString();
                $labels[]   = $d->format('d/m');
                $ingArr[]   = (float)($ventasPorDia[$key]->ingresos ?? 0) + (float)($delivPorDia[$key]->ingresos ?? 0);
                $ventArr[]  = (int)  ($ventasPorDia[$key]->ventas   ?? 0);
                $delivArr[] = (int)  ($delivPorDia[$key]->cantidad  ?? 0);
            }

            return ['labels' => $labels, 'ingresos' => $ingArr, 'ventas' => $ventArr, 'domicilios' => $delivArr];
        });

        return response()->json($result);
    }

    // ── Top galletas ──────────────────────────────────────────────

    public function topCookies(Request $request): JsonResponse
    {
        $desde    = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta    = $request->get('hasta', today()->toDateString());
        $branchId = $this->branchId();
        $cacheKey = "stats_top_cookies_{$branchId}_{$desde}_{$hasta}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($desde, $hasta, $branchId) {
            $q = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('cookies', 'sale_items.cookie_id', '=', 'cookies.id')
                ->where('sales.estado', 'completada')
                ->where('sales.created_at', '>=', $this->startOf($desde))
                ->where('sales.created_at', '<=', $this->endOf($hasta));
            if ($branchId) $q->where('sales.branch_id', $branchId);

            return $q->select(
                    'cookies.id', 'cookies.nombre',
                    DB::raw('SUM(sale_items.cantidad) as total_vendidas'),
                    DB::raw('SUM(sale_items.subtotal) as total_ingresos')
                )
                ->groupBy('cookies.id', 'cookies.nombre')
                ->orderByDesc('total_vendidas')->limit(8)->get();
        });

        return response()->json([
            'labels'   => $data->pluck('nombre'),
            'vendidas' => $data->pluck('total_vendidas')->map(fn ($v) => (int) $v),
            'ingresos' => $data->pluck('total_ingresos')->map(fn ($v) => (float) $v),
        ]);
    }

    // ── Horas pico ────────────────────────────────────────────────

    public function salesByHour(Request $request): JsonResponse
    {
        $desde    = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta    = $request->get('hasta', today()->toDateString());
        $branchId = $this->branchId();
        $cacheKey = "stats_by_hour_{$branchId}_{$desde}_{$hasta}";

        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($desde, $hasta, $branchId) {
            $hourExpr = $this->hourExpr('created_at');

            $vQuery = Sale::completadas()
                ->where('created_at', '>=', $this->startOf($desde))
                ->where('created_at', '<=', $this->endOf($hasta));
            if ($branchId) $vQuery->where('branch_id', $branchId);
            $ventasHora = $vQuery
                ->selectRaw("{$hourExpr} as hora, COUNT(*) as ventas, SUM(total) as ingresos")
                ->groupBy('hora')->orderBy('hora')->get()->keyBy('hora');

            $dQuery = DeliveryOrder::where('created_at', '>=', $this->startOf($desde))
                ->where('created_at', '<=', $this->endOf($hasta));
            if ($branchId) $dQuery->where('branch_id', $branchId);
            $delivHora = $dQuery
                ->selectRaw("{$hourExpr} as hora, COUNT(*) as cantidad")
                ->groupBy('hora')->get()->keyBy('hora');

            $labels = $ventas = $ingresos = [];
            for ($h = 0; $h < 24; $h++) {
                $labels[]   = sprintf('%02d:00', $h);
                $ventas[]   = ((int)($ventasHora[$h]->ventas   ?? 0)) + ((int)($delivHora[$h]->cantidad ?? 0));
                $ingresos[] = (float)($ventasHora[$h]->ingresos ?? 0);
            }

            return compact('labels', 'ventas', 'ingresos');
        });

        return response()->json($result);
    }

    // ── Métodos de pago ───────────────────────────────────────────

    public function byPaymentMethod(Request $request): JsonResponse
    {
        $desde    = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta    = $request->get('hasta', today()->toDateString());
        $branchId = $this->branchId();
        $cacheKey = "stats_by_payment_{$branchId}_{$desde}_{$hasta}";

        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($desde, $hasta, $branchId) {
            $q = Sale::completadas()
                ->where('created_at', '>=', $this->startOf($desde))
                ->where('created_at', '<=', $this->endOf($hasta));
            if ($branchId) $q->where('branch_id', $branchId);
            $data = $q->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as ventas'))
                ->groupBy('metodo_pago')->get();

            $dQuery = DeliveryOrder::whereIn('payment_status', ['paid', 'partial'])
                ->where('created_at', '>=', $this->startOf($desde))
                ->where('created_at', '<=', $this->endOf($hasta));
            if ($branchId) $dQuery->where('branch_id', $branchId);
            $delivTotal = (float) $dQuery->sum('paid_amount');

            $labelMap = ['efectivo' => 'Efectivo', 'transferencia' => 'Nequi/PSE', 'tarjeta' => 'Tarjeta'];
            $labels  = $data->map(fn ($r) => $labelMap[$r->metodo_pago] ?? ucfirst($r->metodo_pago))->values()->toArray();
            $totales = $data->pluck('total')->map(fn ($v) => (float) $v)->values()->toArray();

            if ($delivTotal > 0) { $labels[] = '🛵 Domicilios'; $totales[] = $delivTotal; }

            return ['labels' => $labels, 'totales' => $totales];
        });

        return response()->json($result);
    }

    // ── Tendencia semanal ─────────────────────────────────────────

    public function weeklyTrend(): JsonResponse
    {
        $branchId = $this->branchId();
        $cacheKey = "stats_weekly_trend_{$branchId}";

        $result = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($branchId) {
            $weekExpr = $this->weekExpr('created_at');
            $q = Sale::completadas()->where('created_at', '>=', now()->subWeeks(8));
            if ($branchId) $q->where('branch_id', $branchId);
            $data = $q->selectRaw("{$weekExpr} as semana, SUM(total) as ingresos, COUNT(*) as ventas")
                ->groupBy('semana')->orderBy('semana')->get();
            return [
                'labels'   => $data->pluck('semana'),
                'ingresos' => $data->pluck('ingresos')->map(fn ($v) => (float) $v),
                'ventas'   => $data->pluck('ventas'),
            ];
        });
        return response()->json($result);
    }

    // ── Resumen domicilios hoy ────────────────────────────────────

    public function deliverySummary(): JsonResponse
    {
        $branchId = $this->branchId();
        $cacheKey = "stats_delivery_today_{$branchId}";

        $result = Cache::remember($cacheKey, now()->addMinutes(1), function () use ($branchId) {
            $q = DeliveryOrder::where('created_at', '>=', today()->startOfDay())
                ->where('created_at', '<=', today()->endOfDay());
            if ($branchId) $q->where('branch_id', $branchId);

            $counts = (clone $q)
                ->selectRaw('status, COUNT(*) as count, SUM(total) as total, SUM(paid_amount) as cobrado')
                ->groupBy('status')->get()->keyBy('status');

            $costoEnvio = (float) (clone $q)
                ->where('delivery_cost_type', 'additional')
                ->sum('delivery_cost');

            return [
                'scheduled'   => (int)  ($counts['scheduled']->count   ?? 0),
                'dispatched'  => (int)  ($counts['dispatched']->count  ?? 0),
                'delivered'   => (int)  ($counts['delivered']->count   ?? 0),
                'revenue'     => (float)($counts['delivered']->cobrado ?? 0),
                'costo_envio' => $costoEnvio,
            ];
        });
        return response()->json($result);
    }
}