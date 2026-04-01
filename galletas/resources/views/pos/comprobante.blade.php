@extends('layouts.app')
@section('title', 'Comprobante ' . $sale->numero_factura)

@section('content')
<div class="max-w-lg mx-auto">

    {{-- Acciones --}}
    <div class="flex items-center justify-between mb-6 print:hidden">
        <a href="{{ route('pos.index') }}" class="btn-ghost">
            <span class="icon icon-sm">arrow_back</span>
            Volver al POS
        </a>
        <div class="flex gap-2">
            <a href="{{ route('admin.sales.pdf', $sale) }}" class="btn-danger">
                <span class="icon icon-sm">picture_as_pdf</span>
                PDF
            </a>
            <button onclick="window.print()" class="btn-primary">
                <span class="icon icon-sm">print</span>
                Imprimir
            </button>
        </div>
    </div>

    {{-- Comprobante --}}
    <div class="card overflow-hidden">

        {{-- Header con logo --}}
        <div class="relative px-8 py-7 text-center overflow-hidden"
             style="background: linear-gradient(135deg, #1a0a00 0%, #3b1f0e 55%, #c24808 100%)">
            {{-- Decorative circles --}}
            <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full opacity-10" style="background:#f97316"></div>
            <div class="absolute -bottom-6 -left-6 w-24 h-24 rounded-full opacity-10" style="background:#f97316"></div>

            {{-- Logo --}}
            <div class="relative inline-flex items-center justify-center w-16 h-16 rounded-2xl overflow-hidden mb-4 mx-auto ring-2 ring-white/20 shadow-xl">
                <img src="/images/capy-crunch-logo.jpg" alt="Capy Crunch"
                     class="w-full h-full object-cover"
                     onerror="this.style.display='none';this.parentElement.style.background='#ea6008';this.parentElement.innerHTML+='<span style=\'font-family:Material Symbols Outlined;color:#fff;font-size:32px\'>bakery_dining</span>'">
            </div>

            <h1 class="font-display font-bold text-white text-xl tracking-tight">Capy Crunch</h1>
            <p class="text-white/50 text-xs tracking-widest uppercase mt-0.5">Comprobante de Venta</p>

            <div class="mt-4 inline-flex items-center gap-2 bg-white/15 px-5 py-2 rounded-full border border-white/20">
                <span class="icon icon-sm text-white/70">tag</span>
                <span class="font-mono font-bold text-white text-base tracking-widest">{{ $sale->numero_factura }}</span>
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">

            {{-- Info general --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl p-3.5" style="background:#fdfaf4;border:1px solid #f5e5c0">
                    <p class="text-[10px] font-bold text-espresso-700/50 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                        <span class="icon icon-sm text-brand-400">person</span>Cliente
                    </p>
                    <p class="font-bold text-espresso-900 text-sm">{{ $sale->customer->nombre }}</p>
                    @if($sale->customer->telefono)
                    <p class="text-xs text-espresso-700/50 mt-0.5 flex items-center gap-1">
                        <span class="icon icon-sm" style="font-size:12px">phone</span>{{ $sale->customer->telefono }}
                    </p>
                    @endif
                </div>

                <div class="rounded-xl p-3.5" style="background:#fdfaf4;border:1px solid #f5e5c0">
                    <p class="text-[10px] font-bold text-espresso-700/50 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                        <span class="icon icon-sm text-brand-400">schedule</span>Fecha y hora
                    </p>
                    <p class="font-bold text-espresso-900 text-sm">{{ $sale->created_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-espresso-700/50 mt-0.5">{{ $sale->created_at->format('H:i:s') }}</p>
                </div>

                <div class="rounded-xl p-3.5" style="background:#fdfaf4;border:1px solid #f5e5c0">
                    <p class="text-[10px] font-bold text-espresso-700/50 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                        <span class="icon icon-sm text-brand-400">payments</span>Método de pago
                    </p>
                    @php
                        $payIcons = ['efectivo'=>'payments','transferencia'=>'phone_iphone','nequi'=>'phone_iphone','daviplata'=>'account_balance_wallet','tarjeta'=>'credit_card'];
                        $payLabels = ['efectivo'=>'Efectivo','transferencia'=>'Transferencia','nequi'=>'Nequi','daviplata'=>'Daviplata','tarjeta'=>'Tarjeta'];
                    @endphp
                    <div class="flex items-center gap-1.5">
                        <span class="icon icon-sm text-brand-500">{{ $payIcons[$sale->metodo_pago] ?? 'payment' }}</span>
                        <p class="font-bold text-espresso-900 text-sm">{{ $payLabels[$sale->metodo_pago] ?? ucfirst($sale->metodo_pago) }}</p>
                    </div>
                </div>

                <div class="rounded-xl p-3.5" style="background:#fdfaf4;border:1px solid #f5e5c0">
                    <p class="text-[10px] font-bold text-espresso-700/50 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                        <span class="icon icon-sm text-brand-400">info</span>Estado
                    </p>
                    @php $stMap = ['completada'=>'badge-green','anulada'=>'badge-red','pendiente'=>'badge-amber']; @endphp
                    <span class="badge {{ $stMap[$sale->estado] ?? 'badge-gray' }} mt-0.5">
                        {{ ucfirst($sale->estado) }}
                    </span>
                </div>
            </div>

            {{-- Deuda alert --}}
            @if($sale->tiene_deuda)
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                <span class="icon text-red-500">credit_card_off</span>
                <div>
                    <p class="font-bold">Deuda pendiente registrada</p>
                    <p class="text-xs text-red-500 mt-0.5">Esta venta quedó como cuenta por cobrar</p>
                </div>
            </div>
            @endif

            {{-- Productos --}}
            <div>
                <p class="text-[10px] font-bold text-espresso-700/50 uppercase tracking-wider mb-3 flex items-center gap-1">
                    <span class="icon icon-sm text-brand-400">inventory_2</span>Productos
                </p>
                <div class="space-y-2">
                    @foreach($sale->items as $item)
                    <div class="flex items-center gap-3 rounded-xl px-4 py-3 border border-cream-200"
                         style="background:#fdfaf4">
                        <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0 border border-cream-200 bg-cream-100 flex items-center justify-center">
                            @if($item->cookie->imagen_path)
                                <img src="{{ Storage::url($item->cookie->imagen_path) }}"
                                     alt="{{ $item->cookie->nombre }}"
                                     class="w-full h-full object-cover">
                            @else
                                <span class="icon text-brand-300">bakery_dining</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-espresso-900 text-sm truncate">{{ $item->cookie->nombre }}</p>
                            <p class="text-xs text-espresso-700/50 capitalize">
                                {{ $item->cookie->tamano }} · ${{ number_format($item->precio_unitario, 0, ',', '.') }} c/u
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs text-espresso-700/40">× {{ $item->cantidad }}</p>
                            <p class="font-bold text-espresso-900 text-sm">${{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Totales --}}
            <div class="rounded-2xl overflow-hidden border border-cream-200">
                <div class="px-4 py-3 space-y-2" style="background:#fbf3e2">
                    <div class="flex justify-between text-sm text-espresso-700/60">
                        <span>Subtotal</span>
                        <span>${{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($sale->descuento > 0)
                    <div class="flex justify-between text-sm text-red-500">
                        <span>Descuento ({{ $sale->descuento_porcentaje }}%)</span>
                        <span>− ${{ number_format($sale->descuento, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center border-t border-brand-200 pt-2.5">
                        <span class="font-display font-bold text-espresso-900 text-lg">Total</span>
                        <span class="font-display font-bold text-brand-700 text-2xl">{{ $sale->total_formateado }}</span>
                    </div>
                </div>
            </div>

            {{-- Notas --}}
            @if($sale->notas)
            <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 px-4 py-3 rounded-xl">
                <span class="icon icon-sm text-amber-500 mt-0.5">sticky_note_2</span>
                <p class="text-sm text-amber-800">{{ $sale->notas }}</p>
            </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-cream-200 text-center" style="background:#fdfaf4">
            <p class="text-xs text-espresso-700/40 font-medium">Capy Crunch · Gracias por tu compra</p>
            <p class="text-[10px] text-espresso-700/30 mt-1">Generado el {{ now()->format('d/m/Y H:i') }}</p>
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