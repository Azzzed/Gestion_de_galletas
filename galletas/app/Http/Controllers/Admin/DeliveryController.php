<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    /**
     * Detecta el modelo de galleta existente en el proyecto.
     * Ajusta el orden si usas otro nombre de modelo.
     */
    private function galletaModel(): string
    {
        foreach ([\App\Models\Galleta::class, \App\Models\Cookie::class, \App\Models\Producto::class] as $class) {
            if (class_exists($class)) return $class;
        }
        return \App\Models\Galleta::class;
    }

    private function galletaTable(): string
    {
        $model = $this->galletaModel();
        return (new $model)->getTable();
    }

    private function stockField(string $modelClass): string
    {
        $fillable = (new $modelClass)->getFillable();
        foreach (['stock', 'cantidad', 'inventario', 'quantity'] as $f) {
            if (in_array($f, $fillable)) return $f;
        }
        return 'stock';
    }

    private function precioField(object $instance): string
    {
        foreach (['precio', 'price', 'valor', 'costo'] as $f) {
            if (array_key_exists($f, $instance->getAttributes())) return $f;
        }
        return 'precio';
    }

    private function nombreField(object $instance): string
    {
        foreach (['nombre', 'name', 'titulo', 'title'] as $f) {
            if (array_key_exists($f, $instance->getAttributes())) return $f;
        }
        return 'nombre';
    }

    // ── KANBAN ────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $base = DeliveryOrder::with('customer')
            ->when($request->status,         fn ($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->fecha,          fn ($q) => $q->whereDate('created_at', $request->fecha))
            ->when($request->buscar,         fn ($q) => $q->where(function ($s) use ($request) {
                $s->where('customer_name',   'like', "%{$request->buscar}%")
                  ->orWhere('customer_phone','like', "%{$request->buscar}%")
                  ->orWhere('delivery_address','like', "%{$request->buscar}%");
            }));

        $scheduled  = (clone $base)->scheduled()->latest()->get();
        $dispatched = (clone $base)->dispatched()->latest()->get();
        $delivered  = (clone $base)->delivered()
                        ->whereDate('created_at', $request->fecha ?? today())
                        ->latest()->get();

        $today = today()->toDateString();
        $kpis  = [
            'total_orders'    => DeliveryOrder::whereDate('created_at', $today)->count(),
            'delivered_today' => DeliveryOrder::delivered()->whereDate('created_at', $today)->count(),
            'total_revenue'   => DeliveryOrder::where('status', 'delivered')->whereDate('created_at', $today)->sum('total'),
            'pending_payment' => DeliveryOrder::where('payment_status', 'pending')->whereDate('created_at', $today)->sum('total'),
        ];

        return view('admin.deliveries.index', compact('scheduled', 'dispatched', 'delivered', 'kpis', 'request'));
    }

    // ── CAMBIAR ESTADO ────────────────────────────────────────────

    public function updateStatus(Request $request, DeliveryOrder $delivery)
    {
        $request->validate(['status' => 'required|in:scheduled,dispatched,delivered,cancelled']);

        $delivery->update(['status' => $request->status]);

        if ($request->status === 'delivered'
            && $delivery->payment_method === 'cash_on_delivery'
            && $delivery->payment_status === 'pending') {
            $delivery->update(['payment_status' => 'paid', 'paid_amount' => $delivery->total]);
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Estado: ' . $delivery->status_label,
                'new_status'   => $delivery->status,
                'status_label' => $delivery->status_label,
            ]);
        }

        return redirect()
            ->route('admin.deliveries.index')
            ->with('success', "Domicilio #{$delivery->id} → {$delivery->status_label}");
    }

    // ── PAGO ──────────────────────────────────────────────────────

    public function registerPayment(Request $request, DeliveryOrder $delivery): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        $newPaid = $delivery->paid_amount + min((float) $request->amount, $delivery->remaining);
        $status  = $newPaid >= $delivery->total ? 'paid' : 'partial';
        $delivery->update(['paid_amount' => min($newPaid, $delivery->total), 'payment_status' => $status]);

        return response()->json([
            'success' => true,
            'message' => $status === 'paid' ? '¡Pago completo!' : 'Abono registrado.',
        ]);
    }

    // ── DETALLE ───────────────────────────────────────────────────

    public function show(DeliveryOrder $delivery): JsonResponse
    {
        return response()->json([
            'id'                       => $delivery->id,
            'display_name'             => $delivery->display_name,
            'display_phone'            => $delivery->display_phone,
            'delivery_address'         => $delivery->delivery_address,
            'delivery_neighborhood'    => $delivery->delivery_neighborhood,
            'delivery_cost_type_label' => $delivery->delivery_cost_type_label,
            'delivery_cost_formatted'  => $delivery->delivery_cost_formatted,
            'payment_method_label'     => $delivery->payment_method_label,
            'payment_status_label'     => $delivery->payment_status_label,
            'payment_status'           => $delivery->payment_status,
            'status_label'             => $delivery->status_label,
            'status'                   => $delivery->status,
            'subtotal_formatted'       => $delivery->subtotal_formatted,
            'total_formatted'          => $delivery->total_formatted,
            'discount_amount'          => (float) $delivery->discount_amount,
            'promo_code'               => $delivery->promo_code,
            'notes'                    => $delivery->notes,
            'items'                    => $delivery->enriched_items,
            'remaining'                => (float) $delivery->remaining,
            'remaining_formatted'      => '$' . number_format($delivery->remaining, 0, ',', '.'),
            'created_at'               => $delivery->created_at->format('d/m/Y H:i'),
        ]);
    }

    // ── CANCELAR ─────────────────────────────────────────────────

    public function cancel(DeliveryOrder $delivery)
    {
        if (in_array($delivery->status, ['delivered', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'No se puede cancelar.'], 422);
        }

        $modelClass = $this->galletaModel();
        $stockField = $this->stockField($modelClass);

        DB::transaction(function () use ($delivery, $modelClass, $stockField) {
            foreach ($delivery->items ?? [] as $item) {
                $modelClass::where('id', $item['cookie_id'])->increment($stockField, $item['cantidad']);
            }
            $delivery->update(['status' => 'cancelled']);
        });

        if ($delivery->wasChanged() || true) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Domicilio cancelado. Stock restaurado.']);
            }
            return redirect()
                ->route('admin.deliveries.index')
                ->with('success', 'Domicilio cancelado. Stock restaurado.');
        }
    }

    // ════════════════════════════════════════════════════════════
    // STORE — API desde el POS
    // ════════════════════════════════════════════════════════════

    public function store(Request $request): JsonResponse
    {
        $galletaTable = $this->galletaTable();

        $validated = $request->validate([
            'customer_id'           => 'nullable|integer',
            'customer_name'         => 'nullable|string|max:150',
            'customer_phone'        => 'nullable|string|max:20',
            'delivery_address'      => 'required|string|max:500',
            'delivery_neighborhood' => 'nullable|string|max:100',
            'delivery_cost_type'    => 'required|in:free,additional,business',
            'delivery_cost'         => 'required|numeric|min:0',
            'payment_method'        => 'required|in:cash_on_delivery,transfer',
            'items'                 => 'required|array|min:1',
            'items.*.cookie_id'     => "required|integer|exists:{$galletaTable},id",
            'items.*.cantidad'      => 'required|integer|min:1',
            'promo_code'            => 'nullable|string|max:50',
            'notes'                 => 'nullable|string|max:500',
            'scheduled_at'          => 'nullable|date',
            'guardar_direccion'     => 'nullable|boolean',
        ]);

        $modelClass  = $this->galletaModel();
        $stockField  = $this->stockField($modelClass);
        $orderItems  = [];
        $subtotal    = 0;
        $cookieIds   = [];

        foreach ($validated['items'] as $item) {
            $galleta = $modelClass::find($item['cookie_id']);

            if (! $galleta) {
                return response()->json(['success' => false, 'message' => "Producto ID {$item['cookie_id']} no encontrado."], 422);
            }

            $stock = (int) ($galleta->$stockField ?? 999);
            if ($stock < $item['cantidad']) {
                $n = $this->nombreField($galleta);
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente para {$galleta->$n}. Disponible: {$stock}",
                ], 422);
            }

            $pf        = $this->precioField($galleta);
            $nf        = $this->nombreField($galleta);
            $precio    = (float) $galleta->$pf;
            $lineTotal = $precio * $item['cantidad'];

            $orderItems[] = [
                'cookie_id'       => $galleta->id,
                'nombre'          => $galleta->$nf,
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $precio,
                'subtotal'        => $lineTotal,
            ];
            $subtotal    += $lineTotal;
            $cookieIds[]  = $galleta->id;
        }

        // Código promo
        $discountAmount   = 0;
        $appliedPromoCode = null;
        $deliveryCost     = (float) $validated['delivery_cost'];

        if (! empty($validated['promo_code']) && class_exists(PromoCode::class)) {
            $promo = PromoCode::where('code', strtoupper($validated['promo_code']))->first();
            if ($promo) {
                $result = $promo->calculateDiscount($subtotal, true, $cookieIds);
                if ($result['valid']) {
                    $discountAmount   = $result['discount_amount'];
                    $appliedPromoCode = $promo->code;
                    if ($result['discount_type'] === 'free_delivery') $deliveryCost = 0;
                }
            }
        }

        $total = max(0, $subtotal - $discountAmount + $deliveryCost);

        // Guardar dirección en el perfil del cliente si se solicitó
        if (! empty($validated['customer_id']) && ! empty($validated['guardar_direccion'])) {
            try {
                $customer = Customer::find($validated['customer_id']);
                if ($customer) {
                    $nuevaDireccion = [
                        'direccion' => $validated['delivery_address'],
                        'barrio'    => $validated['delivery_neighborhood'] ?? '',
                    ];
                    $direcciones = $customer->direcciones ?? [];
                    // Evitar duplicados exactos
                    $yaExiste = collect($direcciones)->contains(
                        fn ($d) => strtolower(trim($d['direccion'] ?? '')) === strtolower(trim($nuevaDireccion['direccion']))
                    );
                    if (! $yaExiste) {
                        $direcciones[] = $nuevaDireccion;
                        $customer->update(['direcciones' => $direcciones]);
                    }
                }
            } catch (\Throwable $e) {
                // No fallar la orden si guardar dirección falla
            }
        }

        try {
            $delivery = DB::transaction(function () use (
                $validated, $orderItems, $subtotal, $discountAmount,
                $deliveryCost, $total, $appliedPromoCode, $modelClass, $stockField
            ) {
                $order = DeliveryOrder::create([
                    'customer_id'           => $validated['customer_id'] ?? null,
                    'customer_name'         => $validated['customer_name'] ?? null,
                    'customer_phone'        => $validated['customer_phone'] ?? null,
                    'delivery_address'      => $validated['delivery_address'],
                    'delivery_neighborhood' => $validated['delivery_neighborhood'] ?? null,
                    'delivery_cost_type'    => $validated['delivery_cost_type'],
                    'delivery_cost'         => $deliveryCost,
                    'items'                 => $orderItems,
                    'subtotal'              => $subtotal,
                    'discount_amount'       => $discountAmount,
                    'total'                 => $total,
                    'payment_method'        => $validated['payment_method'],
                    'payment_status'        => $validated['payment_method'] === 'transfer' ? 'paid' : 'pending',
                    'paid_amount'           => $validated['payment_method'] === 'transfer' ? $total : 0,
                    'status'                => 'scheduled',
                    'promo_code'            => $appliedPromoCode,
                    'notes'                 => $validated['notes'] ?? null,
                    'scheduled_at'          => $validated['scheduled_at'] ?? null,
                    'cajero_id'             => auth()->id(),
                ]);

                foreach ($orderItems as $item) {
                    $modelClass::where('id', $item['cookie_id'])->decrement($stockField, $item['cantidad']);
                }

                if ($appliedPromoCode) {
                    PromoCode::where('code', $appliedPromoCode)->increment('used_count');
                }

                return $order;
            });

            return response()->json([
                'success'  => true,
                'order_id' => $delivery->id,
                'message'  => "🛵 Domicilio #{$delivery->id} agendado — $" . number_format($total, 0, ',', '.'),
                'total'    => '$' . number_format($total, 0, ',', '.'),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage(),
            ], 500);
        }
    }
}