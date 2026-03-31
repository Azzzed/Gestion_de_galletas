<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sale::with('customer')
            ->withCount('items');

        // Filtros
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        if ($request->filled('cliente')) {
            $query->whereHas('customer', fn ($q) =>
                $q->where('nombre', 'ilike', "%{$request->cliente}%")
            );
        }

        $ventas = $query->latest()->paginate(20)->withQueryString();

        // KPIs del período filtrado
        $kpis = Sale::completadas()
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->selectRaw('COUNT(*) as total_ventas, SUM(total) as ingresos, AVG(total) as ticket_promedio')
            ->first();

        return view('admin.sales.index', compact('ventas', 'kpis'));
    }

    public function show(Sale $sale): View
    {
        $sale->load(['customer', 'items.cookie', 'debts']);
        return view('admin.sales.show', compact('sale'));
    }

    /** Detalle de venta como JSON para modal */
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
                'nombre'           => $i->cookie->nombre,
                'tamano'           => $i->cookie->tamano,
                'cantidad'         => $i->cantidad,
                'precio_unitario'  => $i->precio_unitario,
                'subtotal'         => $i->subtotal,
            ]),
        ]);
    }

    /** Exportar venta individual a PDF */
    public function exportarPdf(Sale $sale)
    {
        $sale->load(['customer', 'items.cookie']);

        $pdf = Pdf::loadView('admin.sales.pdf', compact('sale'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("venta-{$sale->numero_factura}.pdf");
    }

    /** Anular una venta */
    public function anular(Request $request, Sale $sale): JsonResponse
    {
        abort_if($sale->estado === 'anulada', 422, 'La venta ya está anulada.');

        $request->validate(['motivo' => 'nullable|string|max:255']);

        $sale->anular($request->motivo ?? 'Anulada desde el historial');

        return response()->json(['success' => true, 'mensaje' => 'Venta anulada correctamente.']);
    }
}
