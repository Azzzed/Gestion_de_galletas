<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    private function getGalletas()
    {
        // Detecta el modelo de galleta del proyecto
        foreach ([\App\Models\Galleta::class, \App\Models\Cookie::class, \App\Models\Producto::class] as $class) {
            if (class_exists($class)) {
                $model = new $class;
                // Intenta usar scope 'activos', si no existe trae todos
                try {
                    return $class::activos()->orderBy('nombre')->get();
                } catch (\Throwable $e) {
                    return $class::orderBy('nombre')->get();
                }
            }
        }
        return collect();
    }

    public function index(): View
    {
        $codes = PromoCode::latest()->get()->map(function ($c) {
            return (object) [
                'id'            => $c->id,
                'code'          => $c->code,
                'description'   => $c->description,
                'type'          => $c->type,
                'type_label'    => $c->type_label,
                'type_icon'     => $c->type_icon,
                'discount_label'=> $c->discount_label,
                'min_order_amount' => $c->min_order_amount,
                'max_uses'      => $c->max_uses,
                'used_count'    => $c->used_count,
                'valid_from'    => $c->valid_from?->format('d/m/Y'),
                'valid_until'   => $c->valid_until?->format('d/m/Y'),
                'is_active'     => $c->is_active,
                'is_valid_now'  => $c->is_valid_now,
            ];
        });

        return view('admin.promo_codes.index', compact('codes'));
    }

    public function create(): View
    {
        $cookies = $this->getGalletas();
        return view('admin.promo_codes.create', compact('cookies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $messages = [
            'code.required'                => 'El código es obligatorio.',
            'code.unique'                  => 'Este código ya existe. Elige uno diferente.',
            'code.max'                     => 'El código no puede tener más de 50 caracteres.',
            'type.required'                => 'Selecciona un tipo de descuento.',
            'type.in'                      => 'El tipo de descuento no es válido.',
            'discount_value.required_unless'=> 'El valor del descuento es obligatorio.',
            'discount_value.numeric'       => 'El valor del descuento debe ser un número.',
            'discount_value.min'           => 'El valor del descuento no puede ser negativo.',
            'min_order_amount.numeric'     => 'El pedido mínimo debe ser un número.',
            'min_order_amount.min'         => 'El pedido mínimo no puede ser negativo.',
            'max_uses.integer'             => 'El máximo de usos debe ser un número entero.',
            'max_uses.min'                 => 'El máximo de usos debe ser al menos 1.',
            'valid_from.date'              => 'La fecha "válido desde" no es válida.',
            'valid_until.date'             => 'La fecha "válido hasta" no es válida.',
            'valid_until.after_or_equal'   => 'La fecha "válido hasta" debe ser igual o posterior a "válido desde".',
        ];
        $validated = $request->validate([
            'code'                  => 'required|string|max:50|unique:promo_codes,code',
            'description'           => 'nullable|string|max:255',
            'type'                  => 'required|in:percentage,fixed_amount,free_delivery,cookie_discount',
            'discount_value'        => 'nullable|required_unless:type,free_delivery|numeric|min:0',
            'applicable_cookie_ids' => 'nullable|array',
            'applicable_cookie_ids.*'=> 'integer|exists:cookies,id',
            'min_order_amount'      => 'nullable|numeric|min:0',
            'max_uses'              => 'nullable|integer|min:1',
            'valid_from'            => 'nullable|date',
            'valid_until'           => 'nullable|date|after_or_equal:valid_from',
            'is_active'             => 'boolean',
        ], $messages);

        $validated['code']          = strtoupper($validated['code']);
        $validated['is_active']      = $request->boolean('is_active', true);
        // Si es domicilio gratis, discount_value siempre es 0
        $validated['discount_value'] = $validated['type'] === 'free_delivery'
            ? 0
            : ($validated['discount_value'] ?? 0);

        PromoCode::create($validated);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', "Código \"{$validated['code']}\" creado exitosamente ✅");
    }

    public function edit(PromoCode $promoCode): View
    {
        $cookies = $this->getGalletas();
        return view('admin.promo_codes.edit', compact('promoCode', 'cookies'));
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $messages = [
            'code.required'                => 'El código es obligatorio.',
            'code.unique'                  => 'Este código ya existe. Elige uno diferente.',
            'code.max'                     => 'El código no puede tener más de 50 caracteres.',
            'type.required'                => 'Selecciona un tipo de descuento.',
            'type.in'                      => 'El tipo de descuento no es válido.',
            'discount_value.required_unless'=> 'El valor del descuento es obligatorio.',
            'discount_value.numeric'       => 'El valor del descuento debe ser un número.',
            'discount_value.min'           => 'El valor del descuento no puede ser negativo.',
            'min_order_amount.numeric'     => 'El pedido mínimo debe ser un número.',
            'min_order_amount.min'         => 'El pedido mínimo no puede ser negativo.',
            'max_uses.integer'             => 'El máximo de usos debe ser un número entero.',
            'max_uses.min'                 => 'El máximo de usos debe ser al menos 1.',
            'valid_from.date'              => 'La fecha "válido desde" no es válida.',
            'valid_until.date'             => 'La fecha "válido hasta" no es válida.',
            'valid_until.after_or_equal'   => 'La fecha "válido hasta" debe ser igual o posterior a "válido desde".',
        ];
        $validated = $request->validate([
            'code'                  => "required|string|max:50|unique:promo_codes,code,{$promoCode->id}",
            'description'           => 'nullable|string|max:255',
            'type'                  => 'required|in:percentage,fixed_amount,free_delivery,cookie_discount',
            'discount_value'        => 'nullable|required_unless:type,free_delivery|numeric|min:0',
            'applicable_cookie_ids' => 'nullable|array',
            'applicable_cookie_ids.*'=> 'integer|exists:cookies,id',
            'min_order_amount'      => 'nullable|numeric|min:0',
            'max_uses'              => 'nullable|integer|min:1',
            'valid_from'            => 'nullable|date',
            'valid_until'           => 'nullable|date|after_or_equal:valid_from',
            'is_active'             => 'boolean',
        ], $messages);

        $validated['code']          = strtoupper($validated['code']);
        $validated['is_active']      = $request->boolean('is_active', true);
        $validated['discount_value'] = $validated['type'] === 'free_delivery'
            ? 0
            : ($validated['discount_value'] ?? 0);

        $promoCode->update($validated);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', "Código \"{$validated['code']}\" actualizado ✅");
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $code = $promoCode->code;
        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')
            ->with('success', "Código \"{$code}\" eliminado.");
    }

    public function toggle(PromoCode $promoCode): JsonResponse
    {
        $promoCode->update(['is_active' => ! $promoCode->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $promoCode->is_active,
            'message'   => $promoCode->is_active ? 'Código activado' : 'Código desactivado',
        ]);
    }

    // ── API: Validar código desde el POS ─────────────────────────

    /**
     * Valida un código y devuelve el descuento calculado.
     * GET /api/promo-codes/validate?code=SUMMER20&subtotal=50000&delivery=1&cookie_ids[]=1&cookie_ids[]=3
     */
    public function validate(Request $request): JsonResponse
    {
        $code = strtoupper(trim($request->get('code', '')));

        if (empty($code)) {
            return response()->json(['valid' => false, 'message' => 'Ingresa un código.']);
        }

        $promo = PromoCode::where('code', $code)->first();

        if (! $promo) {
            return response()->json(['valid' => false, 'message' => 'Código no encontrado.']);
        }

        $subtotal  = (float) $request->get('subtotal', 0);
        $hasDelivery = (bool) $request->get('delivery', false);
        $cookieIds = array_map('intval', $request->get('cookie_ids', []));

        $result = $promo->calculateDiscount($subtotal, $hasDelivery, $cookieIds);

        return response()->json(array_merge($result, [
            'code'          => $promo->code,
            'type'          => $promo->type,
            'discount_label'=> $promo->discount_label,
            'description'   => $promo->description,
        ]));
    }
}