<?php

namespace App\Http\Controllers;

use App\Models\Cookie;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $galletas = Cookie::disponiblePos()
            ->orderBy('tamano')
            ->orderBy('nombre')
            ->get()
            ->groupBy('tamano');

        $clienteMostrador = Customer::find(Customer::MOSTRADOR_ID);

        return view('pos.index', compact('galletas', 'clienteMostrador'));
    }

    public function buscarGalletas(Request $request): JsonResponse
    {
        $galletas = Cookie::disponiblePos()
            ->buscar($request->get('q', ''))
            ->select('id', 'nombre', 'precio', 'rellenos', 'tamano', 'stock', 'imagen_path')
            ->limit(12)
            ->get();

        return response()->json($galletas);
    }

    public function buscarClientes(Request $request): JsonResponse
    {
        $clientes = Customer::activos()
            ->reales()
            ->buscar($request->get('q', ''))
            ->select('id', 'nombre', 'telefono')
            ->limit(8)
            ->get();

        return response()->json($clientes);
    }

    public function procesarVenta(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id'             => 'nullable|exists:customers,id',
            'items'                   => 'required|array|min:1',
            'items.*.cookie_id'       => 'required|exists:cookies,id',
            'items.*.cantidad'        => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'descuento_porcentaje'    => 'nullable|numeric|min:0|max:100',
            'metodo_pago'             => 'required|string',
            'metodos_pago'            => 'required|array|min:1',
            'tiene_deuda'             => 'boolean',
        ]);

        $customerId   = $request->customer_id ?? Customer::MOSTRADOR_ID;
        $descuentoPct = $request->descuento_porcentaje ?? 0;

        $sale = Sale::create([
            'customer_id'          => $customerId,
            'descuento_porcentaje' => $descuentoPct,
            'metodo_pago'          => $request->metodo_pago,
            'metodos_pago'         => $request->metodos_pago,
            'estado'               => 'completada',
            'tiene_deuda'          => $request->tiene_deuda ?? false,
            'cajero_id'            => auth()->id(),
        ]);

        foreach ($request->items as $item) {
            $sale->items()->create([
                'cookie_id'       => $item['cookie_id'],
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'descuento_item'  => 0,
                'notas_item'      => $item['notas_item'] ?? null,
            ]);

            // Descontar stock
            Cookie::find($item['cookie_id'])?->decrement('stock', $item['cantidad']);
        }

        $sale->load('items');
        $sale->recalcularTotales();

        // Crear deuda si aplica
        if ($request->tiene_deuda && $customerId !== Customer::MOSTRADOR_ID) {
            \App\Models\Debt::create([
                'customer_id'      => $customerId,
                'sale_id'          => $sale->id,
                'monto_original'   => $sale->total,
                'monto_pendiente'  => $sale->total,
                'monto_pagado'     => 0,
                'estado'           => 'pendiente',
            ]);
        }

        return response()->json([
            'success'          => true,
            'sale_id'          => $sale->id,
            'numero_factura'   => $sale->numero_factura,
            'total'            => $sale->total,
            'total_formateado' => $sale->total_formateado,
        ]);
    }

    public function comprobante(Sale $sale): View
    {
        $sale->load(['customer', 'items.cookie']);
        return view('pos.comprobante', compact('sale'));
    }
}
