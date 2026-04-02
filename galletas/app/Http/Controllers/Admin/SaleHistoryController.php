<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sale::with('customer')->withCount('items');

        // ── Filtros básicos ──────────────────────────────────────
        if ($request->filled('desde'))      $query->whereDate('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))      $query->whereDate('created_at', '<=', $request->hasta);
        if ($request->filled('estado'))     $query->where('estado', $request->estado);
        if ($request->filled('metodo_pago'))$query->where('metodo_pago', $request->metodo_pago);
        if ($request->filled('tiene_deuda'))$query->where('tiene_deuda', (bool) $request->tiene_deuda);

        // ── Búsqueda por cliente (nombre, teléfono, ID) ──────────
        if ($request->filled('cliente')) {
            $q = $request->cliente;
            $query->whereHas('customer', fn ($sub) =>
                $sub->where('nombre', 'ilike', "%{$q}%")
                    ->orWhere('telefono', 'ilike', "%{$q}%")
            );
        }

        // ── Filtro por galleta específica ────────────────────────
        if ($request->filled('cookie_id')) {
            $query->whereHas('items', fn ($sub) =>
                $sub->where('cookie_id', $request->cookie_id)
            );
        }

        // ── Filtro por monto mínimo / máximo ─────────────────────
        if ($request->filled('monto_min')) $query->where('total', '>=', $request->monto_min);
        if ($request->filled('monto_max')) $query->where('total', '<=', $request->monto_max);

        // ── Ordenamiento ─────────────────────────────────────────
        $orderBy  = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $allowed  = ['created_at', 'total', 'id'];
        if (in_array($orderBy, $allowed)) $query->orderBy($orderBy, $orderDir === 'asc' ? 'asc' : 'desc');

        $ventas = $query->paginate(20)->withQueryString();

        // ── KPIs del período filtrado ─────────────────────────────
        $kpis = Sale::completadas()
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->selectRaw('COUNT(*) as total_ventas, SUM(total) as ingresos, AVG(total) as ticket_promedio')
            ->first();

        // ── Top clientes del período ──────────────────────────────
        $topClientes = Sale::completadas()
            ->with('customer')
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->where('customer_id', '!=', 1) // Excluir mostrador
            ->select('customer_id', DB::raw('COUNT(*) as compras'), DB::raw('SUM(total) as total_gastado'))
            ->groupBy('customer_id')
            ->orderByDesc('total_gastado')
            ->limit(5)
            ->get();

        // ── Galletas para el filtro ───────────────────────────────
        $cookies = \App\Models\Cookie::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('admin.sales.index', compact('ventas', 'kpis', 'topClientes', 'cookies'));
    }

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
        $pdf = Pdf::loadView('admin.sales.pdf', compact('sale'))->setPaper('a4', 'portrait');
        return $pdf->download("venta-{$sale->numero_factura}.pdf");
    }

    public function anular(Request $request, Sale $sale): JsonResponse
    {
        abort_if($sale->estado === 'anulada', 422, 'La venta ya está anulada.');
        $request->validate(['motivo' => 'nullable|string|max:255']);
        $sale->anular($request->motivo ?? 'Anulada desde el historial');
        return response()->json(['success' => true, 'mensaje' => 'Venta anulada correctamente.']);
    }

    // ── API: Top clientes (para widget) ──────────────────────────

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
