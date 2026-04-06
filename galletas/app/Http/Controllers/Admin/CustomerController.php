<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Debt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::reales()
            ->withCount(['sales', 'deliveryOrders']);

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        $clientes = $query->orderBy('nombre')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('clientes'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre'    => 'required|string|max:150',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:150',
        ]);

        Customer::create($datos);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente registrado exitosamente.');
    }

    public function show(Customer $customer): View
    {
        $ventas = $customer->sales()
            ->with('items.cookie')
            ->latest()
            ->paginate(8);

        $domicilios = $customer->deliveryOrders()
            ->latest()
            ->paginate(8, ['*'], 'dom_page');

        $totalVentasPOS  = $customer->sales()->completadas()->count();
        $totalDom        = $customer->deliveryOrders()->whereNotIn('status', ['cancelled'])->count();
        $totalCompras    = $totalVentasPOS + $totalDom;

        $totalGastadoPOS = (float) $customer->sales()->completadas()->sum('total');
        $ingresosDom     = (float) $customer->deliveryOrders()
            ->whereNotIn('status', ['cancelled'])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum('paid_amount');
        $totalGastado    = $totalGastadoPOS + $ingresosDom;

        $deudas     = $customer->debts()->with('sale')->pendientes()->latest()->get();
        $saldoTotal = $customer->saldo_pendiente;
        $frecuentes = $customer->ventasFrecuentes(5);

        return view('admin.customers.show', compact(
            'customer', 'ventas', 'domicilios', 'deudas', 'saldoTotal',
            'frecuentes', 'totalGastado', 'totalGastadoPOS', 'ingresosDom',
            'totalVentasPOS', 'totalDom', 'totalCompras',
        ));
    }

    public function edit(Customer $customer): View
    {
        abort_if($customer->es_mostrador, 403, 'No se puede editar el cliente de mostrador.');
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        abort_if($customer->es_mostrador, 403);

        $datos = $request->validate([
            'nombre'    => 'required|string|max:150',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:150',
        ]);

        $customer->update($datos);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente actualizado.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        abort_if($customer->es_mostrador, 403, 'No se puede eliminar el cliente de mostrador.');

        if ($customer->tieneVentasAsociadas()) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar: el cliente tiene ventas asociadas.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente eliminado.');
    }

    public function toggleActivo(Customer $customer): JsonResponse
    {
        abort_if($customer->es_mostrador, 403);
        $customer->update(['activo' => ! $customer->activo]);
        return response()->json(['activo' => $customer->activo]);
    }

    /**
     * ✅ MÉTODO FALTANTE: Registrar abono a una deuda del cliente.
     *
     * POST /admin/customers/debts/{debt}/abono
     * Body JSON: { "monto": 25000 }
     */
        public function registrarAbono(Request $request, Debt $debt): JsonResponse
    {
        $request->validate([
            'monto' => 'required|numeric|min:1',
        ]);
 
        if ($debt->estado === 'pagada') {
            return response()->json([
                'success' => false,
                'message' => 'Esta deuda ya está pagada.',
            ], 422);
        }
 
        $monto = min((float) $request->monto, $debt->monto_pendiente);
 
        // Registrar el pago en la deuda
        $debt->registrarPago($monto);
        $debt->refresh();
 
        // ✅ FIX: Si la deuda quedó pagada y está vinculada a un domicilio
        // → actualizar también delivery_orders.payment_status
        if ($debt->estado === 'pagada' && $debt->notas) {
            // Extraer el ID del domicilio desde las notas (formato: "Domicilio fiado DOM-0001")
            preg_match('/DOM-(\d+)/i', $debt->notas, $matches);
            if (! empty($matches[1])) {
                $deliveryId = (int) $matches[1];
                \App\Models\DeliveryOrder::withoutGlobalScopes()
                    ->where('id', $deliveryId)
                    ->where('payment_status', '!=', 'paid')
                    ->update([
                        'payment_status' => 'paid',
                        'paid_amount'    => \Illuminate\Support\Facades\DB::raw('total'),
                    ]);
            }
        }
 
        return response()->json([
            'success'          => true,
            'message'          => $debt->estado === 'pagada'
                ? '🎉 ¡Deuda saldada completamente!'
                : '✅ Abono registrado correctamente.',
            'estado'           => $debt->estado,
            'monto_pendiente'  => $debt->monto_pendiente,
            'monto_pagado'     => $debt->monto_pagado,
        ]);
    }
}