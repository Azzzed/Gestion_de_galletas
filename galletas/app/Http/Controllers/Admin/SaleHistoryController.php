<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\DeliveryOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleHistoryController extends Controller
{
    // ════════════════════════════════════════════════════════════
    // INDEX — historial unificado (ventas POS + domicilios entregados)
    // ════════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        // ── 1. Ventas POS ─────────────────────────────────────────
        $salesQuery = Sale::with('customer')->withCount('items');

        if ($request->filled('desde'))       $salesQuery->whereDate('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))       $salesQuery->whereDate('created_at', '<=', $request->hasta);
        if ($request->filled('estado'))      $salesQuery->where('estado', $request->estado);
        if ($request->filled('metodo_pago')) $salesQuery->where('metodo_pago', $request->metodo_pago);
        if ($request->filled('tiene_deuda')) $salesQuery->where('tiene_deuda', (bool) $request->tiene_deuda);
        if ($request->filled('monto_min'))   $salesQuery->where('total', '>=', $request->monto_min);
        if ($request->filled('monto_max'))   $salesQuery->where('total', '<=', $request->monto_max);

        if ($request->filled('cliente')) {
            $q = $request->cliente;
            $salesQuery->whereHas('customer', fn ($sub) =>
                $sub->where('nombre', 'ilike', "%{$q}%")
                    ->orWhere('telefono', 'ilike', "%{$q}%")
            );
        }

        if ($request->filled('cookie_id')) {
            $salesQuery->whereHas('items', fn ($sub) =>
                $sub->where('cookie_id', $request->cookie_id)
            );
        }

        $salesRows = $salesQuery->get()->map(fn ($s) => $this->normalizeSale($s));

        // ── 2. Domicilios ENTREGADOS ──────────────────────────────
        // Solo se incluyen si NO hay filtro de estado='anulada' o cookie_id
        // (esos filtros no aplican a domicilios)
        $incluirDomicilios = ! $request->filled('estado')   // filtro de estado es solo para ventas
                          && ! $request->filled('cookie_id') // domicilios no tienen sale_items
                          && ! $request->filled('tiene_deuda');

        $delivRows = collect();

        if ($incluirDomicilios) {
            $delivQuery = DeliveryOrder::with('customer')->where('status', 'delivered');

            if ($request->filled('desde'))       $delivQuery->whereDate('created_at', '>=', $request->desde);
            if ($request->filled('hasta'))       $delivQuery->whereDate('created_at', '<=', $request->hasta);
            if ($request->filled('monto_min'))   $delivQuery->where('total', '>=', $request->monto_min);
            if ($request->filled('monto_max'))   $delivQuery->where('total', '<=', $request->monto_max);

            if ($request->filled('cliente')) {
                $q = $request->cliente;
                $delivQuery->where(fn ($sub) =>
                    $sub->where('customer_name', 'ilike', "%{$q}%")
                        ->orWhere('customer_phone', 'ilike', "%{$q}%")
                        ->orWhereHas('customer', fn ($r) =>
                            $r->where('nombre', 'ilike', "%{$q}%")
                              ->orWhere('telefono', 'ilike', "%{$q}%")
                        )
                );
            }

            // Filtro por método de pago: mapear contraentrega/transferencia
            if ($request->filled('metodo_pago')) {
                $map = ['efectivo' => 'cash_on_delivery', 'transferencia' => 'transfer'];
                $raw = $map[$request->metodo_pago] ?? null;
                if ($raw) $delivQuery->where('payment_method', $raw);
                else      $delivRows = collect(); // método no existe en domicilios → 0 resultados
            }

            if (! $delivRows->isEmpty() || ! $request->filled('metodo_pago') || isset($raw)) {
                $delivRows = $delivQuery->get()->map(fn ($d) => $this->normalizeDelivery($d));
            }
        }

        // ── 3. Merge, ordenar por fecha, paginar ──────────────────
        $orderDir  = $request->get('order_dir', 'desc');
        $orderBy   = $request->get('order_by', 'created_at');

        $merged = $salesRows->concat($delivRows);

        // Ordenar por la fecha (total también soportado)
        $merged = match (true) {
            $orderBy === 'total' && $orderDir === 'asc'  => $merged->sortBy('_total'),
            $orderBy === 'total' && $orderDir === 'desc' => $merged->sortByDesc('_total'),
            $orderDir === 'asc'                          => $merged->sortBy('_created_at'),
            default                                      => $merged->sortByDesc('_created_at'),
        };
        $merged = $merged->values();

        $perPage = 20;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $slice   = $merged->slice(($page - 1) * $perPage, $perPage)->values();

        $ventas = new LengthAwarePaginator($slice, $merged->count(), $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        // ── 4. KPIs (ventas POS del período) ─────────────────────
        $kpis = Sale::completadas()
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->selectRaw('COUNT(*) as total_ventas, SUM(total) as ingresos, AVG(total) as ticket_promedio')
            ->first();

        // ── 5. Top clientes del período ───────────────────────────
        $topClientes = Sale::completadas()
            ->with('customer')
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->where('customer_id', '!=', 1)
            ->select('customer_id', DB::raw('COUNT(*) as compras'), DB::raw('SUM(total) as total_gastado'))
            ->groupBy('customer_id')
            ->orderByDesc('total_gastado')
            ->limit(5)
            ->get();

        $cookies = \App\Models\Cookie::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('admin.sales.index', compact('ventas', 'kpis', 'topClientes', 'cookies'));
    }

    // ── Normalizar una Sale a fila unificada ──────────────────────
    private function normalizeSale(Sale $s): object
    {
        return (object) [
            '_type'           => 'sale',
            '_created_at'     => $s->created_at,
            '_total'          => (float) $s->total,
            'id'              => $s->id,
            'numero_factura'  => $s->numero_factura,
            'customer'        => $s->customer,
            'created_at'      => $s->created_at,
            'items_count'     => $s->items_count,
            'metodo_pago'     => $s->metodo_pago,
            'total_formateado'=> $s->total_formateado,
            'tiene_deuda'     => $s->tiene_deuda,
            'estado'          => $s->estado,
        ];
    }

    // ── Normalizar un DeliveryOrder a fila unificada ──────────────
    private function normalizeDelivery(DeliveryOrder $d): object
    {
        // Cliente: registrado o anónimo
        $customer = $d->customer
            ?? (object) [
                'nombre'   => $d->customer_name ?? '— Sin nombre —',
                'telefono' => $d->customer_phone ?? null,
            ];

        // Método de pago legible
        $metodo = match ($d->payment_method) {
            'cash_on_delivery' => 'contraentrega',
            'transfer'         => 'transferencia',
            default            => $d->payment_method,
        };

        // Estado del pago → mostrar como "deuda" si está pendiente
        $tienePendiente = in_array($d->payment_status, ['pending', 'partial']);

        return (object) [
            '_type'           => 'delivery',
            '_created_at'     => $d->created_at,
            '_total'          => (float) $d->total,
            'id'              => $d->id,
            'numero_factura'  => 'DOM-' . str_pad($d->id, 4, '0', STR_PAD_LEFT),
            'customer'        => $customer,
            'created_at'      => $d->created_at,
            'items_count'     => count($d->items ?? []),
            'metodo_pago'     => $metodo,
            'total_formateado'=> '$' . number_format($d->total, 0, ',', '.'),
            'tiene_deuda'     => $tienePendiente,
            'estado'          => 'entregado',
        ];
    }

    // ════════════════════════════════════════════════════════════
    // SHOW / DETALLE / PDF / ANULAR  (solo aplican a Sales)
    // ════════════════════════════════════════════════════════════

    public function show(Sale $sale): View
    {
        $sale->load(['customer', 'items.cookie', 'debts']);
        return view('admin.sales.show', compact('sale'));
    }

    public function detalle(Sale $sale): JsonResponse
    {
        $sale->load(['customer', 'items.cookie']);

        return response()->json([
            'id'              => $sale->id,
            'numero_factura'  => $sale->numero_factura,
            'fecha'           => $sale->created_at->format('d/m/Y H:i:s'),
            'estado'          => $sale->estado,
            'metodo_pago'     => $sale->metodo_pago,
            'cliente'         => $sale->customer->nombre,
            'subtotal'        => $sale->subtotal,
            'descuento'       => $sale->descuento,
            'total'           => $sale->total,
            'total_formateado'=> $sale->total_formateado,
            'tiene_deuda'     => $sale->tiene_deuda,
            'notas'           => $sale->notas,
            'items'           => $sale->items->map(fn ($i) => [
                'nombre'          => $i->cookie->nombre,
                'tamano'          => $i->cookie->tamano,
                'cantidad'        => $i->cantidad,
                'precio_unitario' => $i->precio_unitario,
                'subtotal'        => $i->subtotal,
            ]),
        ]);
    }

    public function exportarPdf(Sale $sale)
    {
        $sale->load(['customer', 'items.cookie']);

        $logoPath   = public_path('images/capy-crunch-logo.jpg');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('admin.sales.pdf', compact('sale', 'logoBase64'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("venta-{$sale->numero_factura}.pdf");
    }

    public function anular(Request $request, Sale $sale): JsonResponse
    {
        abort_if($sale->estado === 'anulada', 422, 'La venta ya está anulada.');
        $request->validate(['motivo' => 'nullable|string|max:255']);
        $sale->anular($request->motivo ?? 'Anulada desde el historial');
        return response()->json(['success' => true, 'mensaje' => 'Venta anulada correctamente.']);
    }

    // ── API: Top clientes ─────────────────────────────────────────

    public function topClientes(Request $request): JsonResponse
    {
        $desde = $request->get('desde', now()->subDays(29)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        $top = Sale::completadas()
            ->with('customer')
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('customer_id', '!=', 1)
            ->select('customer_id', DB::raw('COUNT(*) as compras'), DB::raw('SUM(total) as total_gastado'))
            ->groupBy('customer_id')
            ->orderByDesc('total_gastado')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'nombre'        => $s->customer?->nombre ?? '—',
                'compras'       => $s->compras,
                'total_gastado' => (float) $s->total_gastado,
                'formatted'     => '$' . number_format($s->total_gastado, 0, ',', '.'),
            ]);

        return response()->json($top);
    }
}