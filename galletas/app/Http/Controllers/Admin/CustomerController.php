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
        $query = Customer::reales()->withCount('sales');

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

        $deudas       = $customer->debts()->with('sale')->pendientes()->latest()->get();
        $saldoTotal   = $customer->saldo_pendiente;
        $frecuentes   = $customer->ventasFrecuentes(5);
        $totalGastado = $customer->sales()->completadas()->sum('total');

        return view('admin.customers.show', compact(
            'customer', 'ventas', 'deudas', 'saldoTotal', 'frecuentes', 'totalGastado'
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
        abort_if($customer->es_mostrador, 403);

        if ($customer->tieneVentasAsociadas()) {
            return back()->with('error',
                "No se puede eliminar a \"{$customer->nombre}\" porque tiene ventas registradas."
            );
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Cliente eliminado.');
    }

    /**
     * Registrar un abono a una deuda.
     * Acepta peticiones AJAX (fetch con Accept: application/json) y formularios normales.
     */
    public function registrarAbono(Request $request, Debt $debt): JsonResponse|RedirectResponse
    {
        // Calcular el pendiente real directo de la BD para evitar
        // problemas de cast o scopes que lo devuelvan en 0
        $pendienteReal = (float) Debt::where('id', $debt->id)->value('monto_pendiente');

        $request->validate([
            'monto' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($pendienteReal) {
                    if ((float) $value > $pendienteReal) {
                        $fail('El monto no puede ser mayor al saldo pendiente ($'
                            . number_format($pendienteReal, 0, ',', '.') . ').');
                    }
                },
            ],
        ], [
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric'  => 'El monto debe ser un número.',
            'monto.min'      => 'El monto debe ser al menos $1.',
        ]);

        $debt->registrarPago((float) $request->monto);

        // Si vino por fetch (modal Alpine) → devolver JSON
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Abono registrado correctamente.',
                'nuevo_pendiente' => (float) Debt::where('id', $debt->id)->value('monto_pendiente'),
            ]);
        }

        // Si vino por form normal → redirigir
        return back()->with('success', 'Abono registrado correctamente.');
    }
}