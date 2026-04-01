@extends('layouts.app')
@section('title', 'Comprobante ' . $sale->numero_factura)

@section('content')
<div class="max-w-lg mx-auto">

    {{-- Acciones --}}
    <div class="flex items-center justify-between mb-6 print:hidden">
        <a href="{{ route('pos.index') }}"
           class="px-4 py-2 bg-white border border-warm-200 text-gray-600 rounded-xl
                  text-sm font-medium hover:bg-warm-50 transition-colors">
            ← Volver al POS
        </a>
        <div class="flex gap-2">
            <a href="{{ route('admin.sales.pdf', $sale) }}"
               class="px-4 py-2 bg-red-50 border border-red-200 text-red-600 rounded-xl
                      text-sm font-medium hover:bg-red-100 transition-colors">
                📄 Exportar PDF
            </a>
            <button onclick="window.print()"
                    class="px-4 py-2 bg-capy-600 text-white rounded-xl text-sm
                           font-medium hover:bg-capy-700 transition-colors">
                🖨️ Imprimir
            </button>
        </div>
    </div>

    {{-- Comprobante --}}
    <div class="bg-white rounded-3xl border border-warm-200 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="bg-capy-700 px-8 py-6 text-white text-center">
            <p class="text-3xl mb-1">🐾</p>
            <h1 class="text-xl font-bold">CapyCrunch</h1>
            <p class="text-capy-200 text-sm">Comprobante de Venta</p>
            <div class="mt-3 inline-block bg-white/20 px-4 py-1.5 rounded-full">
                <span class="font-mono font-bold text-lg tracking-wider">
                    {{ $sale->numero_factura }}
                </span>
            </div>
        </div>

        <div class="px-8 py-6 space-y-5">

            {{-- Info general --}}
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-warm-50 rounded-2xl p-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Cliente</p>
                    <p class="font-semibold text-gray-800">{{ $sale->customer->nombre }}</p>
                    @if($sale->customer->telefono)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $sale->customer->telefono }}</p>
                    @endif
                </div>
                <div class="bg-warm-50 rounded-2xl p-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Fecha y hora</p>
                    <p class="font-semibold text-gray-800">
                        {{ $sale->created_at->format('d/m/Y') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $sale->created_at->format('H:i:s') }}
                    </p>
                </div>
                <div class="bg-warm-50 rounded-2xl p-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Método de pago</p>
                    <p class="font-semibold text-gray-800">
                        @php
                            $iconos = [
                                'efectivo'      => '💵 Efectivo',
                                'transferencia' => '📱 Transferencia',
                                'nequi'         => '💜 Nequi',
                                'daviplata'     => '🧡 Daviplata',
                                'tarjeta'       => '💳 Tarjeta',
                            ];
                        @endphp
                        {{ $iconos[$sale->metodo_pago] ?? ucfirst($sale->metodo_pago) }}
                    </p>
                </div>
                <div class="bg-warm-50 rounded-2xl p-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Estado</p>
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $sale->estado === 'completada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        {{ ucfirst($sale->estado) }}
                    </span>
                </div>
            </div>

            {{-- Deuda badge --}}
            @if($sale->tiene_deuda)
            <div class="flex items-center gap-2 bg-red-50 border border-red-200
                        text-red-700 px-4 py-3 rounded-2xl text-sm">
                ⚠️ Esta venta quedó registrada como <strong>deuda pendiente</strong>
            </div>
            @endif

            {{-- Productos --}}
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Productos</p>
                <div class="space-y-2">
                    @foreach($sale->items as $item)
                    <div class="flex items-center gap-3 bg-warm-50 rounded-2xl px-4 py-3">
                        {{-- Imagen o emoji --}}
                        <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0
                                    bg-warm-100 flex items-center justify-center">
                            @if($item->cookie->imagen_path)
                                <img src="{{ Storage::url($item->cookie->imagen_path) }}"
                                     alt="{{ $item->cookie->nombre }}"
                                     class="w-full h-full object-cover">
                            @else
                                <span class="text-xl">🍪</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 text-sm truncate">
                                {{ $item->cookie->nombre }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ ucfirst($item->cookie->tamano) }} ·
                                ${{ number_format($item->precio_unitario, 0, ',', '.') }} c/u
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs text-gray-400">× {{ $item->cantidad }}</p>
                            <p class="font-bold text-gray-800 text-sm">
                                ${{ number_format($item->subtotal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Totales --}}
            <div class="bg-capy-50 rounded-2xl p-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span>${{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($sale->descuento > 0)
                <div class="flex justify-between text-sm text-red-400">
                    <span>Descuento ({{ $sale->descuento_porcentaje }}%)</span>
                    <span>− ${{ number_format($sale->descuento, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-xl font-bold text-gray-900
                            border-t border-capy-200 pt-3 mt-1">
                    <span>Total</span>
                    <span class="text-capy-600">{{ $sale->total_formateado }}</span>
                </div>
            </div>

            @if($sale->notas)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3 text-sm text-amber-800">
                📝 {{ $sale->notas }}
            </div>
            @endif

        </div>

        {{-- Footer --}}
        <div class="px-8 py-4 bg-warm-50 border-t border-warm-200 text-center">
            <p class="text-xs text-gray-400">CapyCrunch · Gracias por tu compra 🍪</p>
            <p class="text-xs text-gray-300 mt-0.5">
                Generado el {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>
</div>

<style>
@media print {
    nav, .print\:hidden { display: none !important; }
    body { background: white; }
    .max-w-lg { max-width: 100%; }
}
</style>
@endsection