@extends('layouts.app')
@section('title', 'Cliente: '.$customer->nombre)

@section('content')
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js" defer></script>

<div x-data="{ tab: 'resumen', ventaExpandida: null }">

    {{-- ── HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-brand-100 flex items-center justify-center shadow text-brand-700 font-display font-bold text-2xl">
                {{ strtoupper(substr($customer->nombre, 0, 1)) }}
            </div>
            <div>
                <h1 class="font-display font-bold text-espresso-900 text-2xl">{{ $customer->nombre }}</h1>
                <div class="flex flex-wrap gap-3 mt-1 text-sm text-espresso-700/60">
                    @if($customer->telefono)
                        <span class="flex items-center gap-1"><span class="icon icon-sm">phone</span>{{ $customer->telefono }}</span>
                    @endif
                    @if($customer->email)
                        <span class="flex items-center gap-1"><span class="icon icon-sm">email</span>{{ $customer->email }}</span>
                    @endif
                    @if($customer->direccion)
                        <span class="flex items-center gap-1"><span class="icon icon-sm">home</span>{{ $customer->direccion }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn-ghost py-2 px-4 text-sm">
                <span class="icon icon-sm">edit</span> Editar
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn-ghost py-2 px-3">
                <span class="icon icon-sm">arrow_back</span>
            </a>
        </div>
    </div>

    {{-- ── KPIs ────────────────────────────────────────────────── --}}
    @php
        $ventas       = $customer->sales()->where('estado','completada')->with('items.cookie')->latest()->get();
        $totalVentas  = $ventas->count();
        $totalGastado = $ventas->sum('total');
        $deuda        = $customer->debts()->whereIn('estado',['pendiente','pagada_parcial'])->sum('monto_pendiente');

        $domicilios     = $customer->deliveryOrders()->latest()->get();
        $totalDom       = $domicilios->count();
        $ingresosDom    = $domicilios->whereIn('payment_status',['paid'])->sum('paid_amount')
                        + $domicilios->where('payment_status','partial')->sum('paid_amount');

        // Galleta favorita
        $galletaFav = $ventas->flatMap(fn($s) => $s->items)
            ->groupBy('cookie_id')
            ->map(fn($items) => ['nombre' => $items->first()->cookie?->nombre ?? '?', 'total' => $items->sum('cantidad')])
            ->sortByDesc('total')->first();
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
        <div class="rounded-2xl p-4 bg-gradient-to-br from-espresso-900 to-darkBrown shadow-md">
            <span class="icon text-white/60 block mb-1">receipt_long</span>
            <p class="font-display font-bold text-white text-xl">{{ $totalVentas }}</p>
            <p class="text-white/60 text-[10px] uppercase font-bold mt-0.5">Compras</p>
        </div>
        <div class="rounded-2xl p-4 bg-gradient-to-br from-brand-600 to-brand-500 shadow-md lg:col-span-2">
            <span class="icon text-white/60 block mb-1">payments</span>
            <p class="font-display font-bold text-white text-xl">${{ number_format($totalGastado,0,',','.') }}</p>
            <p class="text-white/60 text-[10px] uppercase font-bold mt-0.5">Total gastado (POS)</p>
        </div>
        <div class="rounded-2xl p-4 bg-gradient-to-br from-blue-700 to-blue-500 shadow-md">
            <span class="icon text-white/60 block mb-1">local_shipping</span>
            <p class="font-display font-bold text-white text-xl">{{ $totalDom }}</p>
            <p class="text-white/60 text-[10px] uppercase font-bold mt-0.5">Domicilios</p>
        </div>
        <div class="rounded-2xl p-4 bg-gradient-to-br from-teal-700 to-teal-500 shadow-md">
            <span class="icon text-white/60 block mb-1">local_shipping</span>
            <p class="font-display font-bold text-white text-base">${{ number_format($ingresosDom,0,',','.') }}</p>
            <p class="text-white/60 text-[10px] uppercase font-bold mt-0.5">Cobrado dom.</p>
        </div>
        <div class="rounded-2xl p-4 shadow-md {{ $deuda > 0 ? 'bg-gradient-to-br from-red-600 to-red-500' : 'bg-gradient-to-br from-green-700 to-green-500' }}">
            <span class="icon text-white/60 block mb-1">{{ $deuda > 0 ? 'credit_card_off' : 'check_circle' }}</span>
            <p class="font-display font-bold text-white text-xl">${{ number_format($deuda,0,',','.') }}</p>
            <p class="text-white/60 text-[10px] uppercase font-bold mt-0.5">Deuda</p>
        </div>
    </div>

    {{-- Galleta favorita --}}
    @if($galletaFav)
    <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-amber-50 border border-amber-200 mb-5">
        <span class="icon text-amber-500">bakery_dining</span>
        <p class="text-sm text-amber-800">
            Galleta favorita: <strong>{{ $galletaFav['nombre'] }}</strong>
            <span class="text-amber-600">({{ $galletaFav['total'] }} unidades)</span>
        </p>
    </div>
    @endif

    {{-- ── TABS ─────────────────────────────────────────────────── --}}
    <div class="flex gap-1 mb-5 p-1 bg-cream-100 rounded-2xl w-fit flex-wrap">
        @foreach([
            ['resumen',    'dashboard',       'Resumen'],
            ['ventas',     'receipt_long',    'Ventas ('.$totalVentas.')'],
            ['domicilios', 'local_shipping',  'Domicilios ('.$totalDom.')'],
            ['deudas',     'credit_card_off', 'Deudas'],
            ['direcciones','location_on',     'Direcciones'],
        ] as [$t, $icon, $label])
        <button @click="tab='{{ $t }}'"
                :class="tab==='{{ $t }}' ? 'bg-white text-espresso-900 shadow-sm font-bold' : 'text-espresso-700/50 hover:text-espresso-700'"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm transition-all">
            <span class="icon icon-sm">{{ $icon }}</span>
            {{ $label }}
            @if($t === 'deudas' && $deuda > 0)
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TAB: RESUMEN
    ══════════════════════════════════════════════════════════ --}}
    <div x-show="tab==='resumen'" x-transition>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Últimas compras con detalle --}}
            <div class="card p-5">
                <h3 class="font-display font-bold text-espresso-900 mb-4 flex items-center gap-2">
                    <span class="icon text-brand-500">receipt_long</span> Últimas compras
                </h3>
                @forelse($ventas->take(5) as $s)
                <div class="border-b border-cream-100 last:border-0 py-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <div>
                            <span class="font-mono text-xs font-bold text-brand-700">{{ $s->numero_factura }}</span>
                            <span class="text-xs text-espresso-700/50 ml-2">{{ $s->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <span class="font-bold text-espresso-900">{{ $s->total_formateado }}</span>
                    </div>
                    <div class="flex flex-wrap gap-1">
                        @foreach($s->items as $item)
                        <span class="text-[10px] bg-cream-100 text-espresso-700 px-2 py-0.5 rounded-full border border-cream-200">
                            {{ $item->cantidad }}× {{ $item->cookie?->nombre ?? 'Galleta' }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="text-sm text-espresso-700/40 py-6 text-center">Sin compras registradas</p>
                @endforelse
            </div>

            {{-- Últimos domicilios --}}
            <div class="card p-5">
                <h3 class="font-display font-bold text-espresso-900 mb-4 flex items-center gap-2">
                    <span class="icon text-blue-500">local_shipping</span> Últimos domicilios
                </h3>
                @forelse($domicilios->take(5) as $d)
                @php
                    $sc = ['scheduled'=>'text-amber-600','dispatched'=>'text-blue-600','delivered'=>'text-green-600','cancelled'=>'text-red-400'];
                    $sl = ['scheduled'=>'Agendado','dispatched'=>'En camino','delivered'=>'Entregado','cancelled'=>'Cancelado'];
                @endphp
                <div class="flex items-center justify-between py-2.5 border-b border-cream-100 last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-espresso-900 truncate">{{ $d->delivery_address }}</p>
                        <p class="text-xs text-espresso-700/50">{{ $d->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="text-right ml-3 flex-shrink-0">
                        <p class="font-bold text-brand-700">{{ $d->total_formatted }}</p>
                        <p class="text-xs font-bold {{ $sc[$d->status] ?? '' }}">{{ $sl[$d->status] ?? $d->status }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-espresso-700/40 py-6 text-center">Sin domicilios</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TAB: VENTAS — detalle completo con productos
    ══════════════════════════════════════════════════════════ --}}
    <div x-show="tab==='ventas'" x-transition>
        @if($ventas->isEmpty())
        <div class="card p-16 text-center">
            <span class="icon text-4xl text-espresso-700/20 block mb-3">receipt_long</span>
            <p class="text-sm text-espresso-700/40">Sin ventas registradas</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($ventas as $s)
            <div class="card overflow-hidden">
                {{-- Cabecera de la venta --}}
                <div @click="ventaExpandida = ventaExpandida === {{ $s->id }} ? null : {{ $s->id }}"
                     class="flex items-center gap-4 p-4 cursor-pointer hover:bg-cream-50 transition-colors">

                    {{-- Número + fecha --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="font-mono font-bold text-brand-700 text-sm">{{ $s->numero_factura }}</span>
                            <span class="text-xs text-espresso-700/50">{{ $s->created_at->format('d/m/Y') }}</span>
                            <span class="text-xs text-espresso-700/50">{{ $s->created_at->format('H:i') }}</span>
                        </div>
                        {{-- Resumen de items en una línea --}}
                        <p class="text-xs text-espresso-700/60 mt-0.5 truncate">
                            {{ $s->items->map(fn($i) => $i->cantidad.'× '.($i->cookie?->nombre ?? '?'))->join(', ') }}
                        </p>
                    </div>

                    {{-- Método de pago --}}
                    <div class="hidden sm:flex items-center gap-1 text-xs text-espresso-700/50 flex-shrink-0">
                        @php $pIcons = ['efectivo'=>'payments','transferencia'=>'phone_iphone','tarjeta'=>'credit_card']; @endphp
                        <span class="icon icon-sm">{{ $pIcons[$s->metodo_pago] ?? 'payment' }}</span>
                        {{ ucfirst($s->metodo_pago) }}
                    </div>

                    {{-- Estado --}}
                    @if($s->tiene_deuda)
                    <span class="text-[10px] font-bold bg-red-100 text-red-600 px-2 py-0.5 rounded-full flex-shrink-0">Deuda</span>
                    @endif

                    {{-- Total --}}
                    <span class="font-display font-bold text-lg text-espresso-900 flex-shrink-0">{{ $s->total_formateado }}</span>

                    {{-- Chevron --}}
                    <span class="icon icon-sm text-espresso-700/30 flex-shrink-0"
                          :class="ventaExpandida === {{ $s->id }} ? 'rotate-180' : ''"
                          style="transition:transform .2s">expand_more</span>
                </div>

                {{-- Detalle expandido --}}
                <div x-show="ventaExpandida === {{ $s->id }}"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="border-t border-cream-100">

                    {{-- Tabla de productos --}}
                    <div class="px-4 pt-3 pb-2">
                        <p class="text-[10px] font-bold text-espresso-700/50 uppercase tracking-wide mb-2">Productos</p>
                        <div class="space-y-1.5">
                            @foreach($s->items as $item)
                            <div class="flex items-center gap-3 bg-cream-50 rounded-xl px-4 py-2.5">
                                <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center flex-shrink-0">
                                    @if($item->cookie?->imagen_path)
                                        <img src="{{ Storage::url($item->cookie->imagen_path) }}"
                                             class="w-8 h-8 rounded-lg object-cover">
                                    @else
                                        <span class="icon icon-sm text-brand-500">bakery_dining</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-espresso-900">
                                        {{ $item->cookie?->nombre ?? 'Galleta eliminada' }}
                                    </p>
                                    <p class="text-xs text-espresso-700/50 capitalize">
                                        {{ $item->cookie?->tamano ?? '' }}
                                        @foreach($item->cookie?->rellenos ?? [] as $r)
                                            · {{ $r }}
                                        @endforeach
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm font-bold text-espresso-900">
                                        ${{ number_format($item->subtotal, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-espresso-700/50">
                                        {{ $item->cantidad }} × ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Totales --}}
                    <div class="mx-4 mb-4 mt-2 rounded-xl bg-cream-50 border border-cream-200 px-4 py-3 space-y-1 text-sm">
                        @if($s->descuento_porcentaje > 0)
                        <div class="flex justify-between text-espresso-700/60">
                            <span>Subtotal</span>
                            <span>${{ number_format($s->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-green-600">
                            <span>Descuento {{ $s->descuento_porcentaje }}%</span>
                            <span>− ${{ number_format($s->subtotal - $s->total, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between font-bold text-base {{ $s->descuento_porcentaje > 0 ? 'border-t border-cream-200 pt-1' : '' }}">
                            <span>Total</span>
                            <span class="text-brand-700">{{ $s->total_formateado }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-espresso-700/50 pt-1 border-t border-cream-200">
                            <span class="flex items-center gap-1">
                                <span class="icon icon-sm">{{ $pIcons[$s->metodo_pago] ?? 'payment' }}</span>
                                {{ ucfirst($s->metodo_pago) }}
                            </span>
                            <a href="{{ route('admin.sales.pdf', $s) }}"
                               class="flex items-center gap-1 text-brand-600 hover:underline">
                                <span class="icon icon-sm">picture_as_pdf</span> Comprobante PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TAB: DOMICILIOS
    ══════════════════════════════════════════════════════════ --}}
    <div x-show="tab==='domicilios'" x-transition>
        @if($domicilios->isEmpty())
        <div class="card p-16 text-center">
            <span class="icon text-4xl text-espresso-700/20 block mb-3">local_shipping</span>
            <p class="text-sm text-espresso-700/40">Sin domicilios registrados</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($domicilios as $d)
            @php
                $sBg  = ['scheduled'=>'border-amber-300 bg-amber-50/30','dispatched'=>'border-blue-300 bg-blue-50/30','delivered'=>'border-green-300 bg-green-50/30','cancelled'=>'border-red-200 opacity-60'];
                $sLbl = ['scheduled'=>'⏳ Agendado','dispatched'=>'🛵 En camino','delivered'=>'✅ Entregado','cancelled'=>'❌ Cancelado'];
                $pLbl = ['paid'=>'✅ Pagado','pending'=>'⚠ Pendiente','partial'=>'💛 Abono parcial'];
                $pClr = ['paid'=>'text-green-700','pending'=>'text-red-600','partial'=>'text-amber-600'];
            @endphp
            <div class="card border-2 {{ $sBg[$d->status] ?? 'border-cream-200' }} p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-espresso-900 flex items-center gap-1">
                            <span class="icon icon-sm text-brand-400">location_on</span>
                            {{ $d->delivery_address }}
                            @if($d->delivery_neighborhood)
                                <span class="text-xs text-espresso-700/50 font-normal">· {{ $d->delivery_neighborhood }}</span>
                            @endif
                        </p>
                        {{-- Items del domicilio --}}
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach(($d->items ?? []) as $item)
                            <span class="text-[10px] bg-white text-espresso-700 px-2 py-0.5 rounded-full border border-cream-200 font-medium">
                                {{ $item['cantidad'] }}× {{ $item['nombre'] }}
                            </span>
                            @endforeach
                        </div>
                        <div class="flex flex-wrap gap-3 mt-2 text-xs text-espresso-700/50">
                            <span>{{ $d->created_at->format('d/m/Y H:i') }}</span>
                            <span>{{ $d->payment_method_label }}</span>
                            @if($d->promo_code)
                            <span class="text-green-600 font-bold">🏷 {{ $d->promo_code }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 space-y-1">
                        <p class="font-display font-bold text-lg text-brand-700">{{ $d->total_formatted }}</p>
                        @if($d->delivery_cost > 0)
                        <p class="text-xs text-espresso-700/50">Envío: ${{ number_format($d->delivery_cost,0,',','.') }}</p>
                        @endif
                        <p class="text-xs font-bold {{ $pClr[$d->payment_status] ?? '' }}">{{ $pLbl[$d->payment_status] ?? '' }}</p>
                        @if($d->payment_status === 'partial')
                        <p class="text-xs text-amber-600">
                            Abonado: ${{ number_format($d->paid_amount,0,',','.') }}<br>
                            Resta: ${{ number_format($d->remaining,0,',','.') }}
                        </p>
                        @endif
                        <p class="text-xs font-semibold">{{ $sLbl[$d->status] ?? '' }}</p>
                    </div>
                </div>
                @if($d->notes)
                <p class="mt-2 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-1.5 border border-amber-100">
                    📝 {{ $d->notes }}
                </p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TAB: DEUDAS
    ══════════════════════════════════════════════════════════ --}}
    <div x-show="tab==='deudas'" x-transition
         x-data="{
            modalAbono: false,
            deudaActiva: null,
            monto: '',
            procesando: false,
            abrirAbono(id, factura, pendiente) {
                this.deudaActiva = { id, factura, pendiente };
                this.monto = pendiente;
                this.modalAbono = true;
            },
            async registrar() {
                if (!this.monto || this.procesando) return;
                this.procesando = true;
                try {
                    const r = await fetch('/admin/customers/debts/' + this.deudaActiva.id + '/abono', {
                        method: 'POST',
                        headers: {
                            'Content-Type':  'application/json',
                            'Accept':        'application/json',
                            'X-CSRF-TOKEN':  document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({ monto: this.monto }),
                    });
                    const raw  = await r.text();
                    let data;
                    try { data = JSON.parse(raw); } catch { data = { success: false, message: raw.substring(0,200) }; }
                    if (data.success) {
                        this.modalAbono = false;
                        window.location.reload();
                    } else {
                        alert('❌ ' + (data.message || 'Error al registrar el abono'));
                    }
                } catch(e) {
                    alert('❌ Error de red: ' + e.message);
                } finally {
                    this.procesando = false;
                }
            }
         }">

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="tbl-head">
                        <tr>
                            <th>Factura</th><th>Fecha</th><th>Original</th>
                            <th>Pagado</th><th>Pendiente</th><th>Estado</th>
                            <th class="pr-6 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->debts()->with('sale')->latest()->get() as $debt)
                        <tr class="tbl-row">
                            <td>
                                <span class="font-mono text-xs font-bold text-brand-700">
                                    {{ $debt->sale?->numero_factura ?? '—' }}
                                </span>
                            </td>
                            <td class="text-sm">{{ $debt->created_at->format('d/m/Y') }}</td>
                            <td>${{ number_format($debt->monto_original,0,',','.') }}</td>
                            <td class="text-green-600 font-medium">${{ number_format($debt->monto_pagado,0,',','.') }}</td>
                            <td class="{{ $debt->estado === 'pagado' ? 'text-green-600' : 'text-red-600 font-bold' }}">
                                ${{ number_format($debt->monto_pendiente,0,',','.') }}
                            </td>
                            <td>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full
                                    {{ $debt->estado === 'pagado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($debt->estado) }}
                                </span>
                            </td>
                            <td class="text-right pr-6">
                                @if($debt->estado !== 'pagado')
                                <button @click="abrirAbono(
                                            {{ $debt->id }},
                                            '{{ $debt->sale?->numero_factura ?? 'Deuda #'.$debt->id }}',
                                            {{ $debt->monto_pendiente }}
                                        )"
                                        class="text-xs bg-green-100 hover:bg-green-200 text-green-700 font-bold px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1 ml-auto">
                                    <span class="icon icon-sm">payments</span> Abonar
                                </button>
                                @else
                                <span class="text-xs text-green-500 font-semibold flex items-center gap-1 justify-end">
                                    <span class="icon icon-sm">check_circle</span> Saldado
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-espresso-700/40 text-sm">
                                Sin deudas registradas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal abono ──────────────────────────────────────── --}}
        <div x-show="modalAbono"
             style="display:none"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             @click.self="modalAbono=false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-900/50 backdrop-blur-sm">

            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-4 flex items-center justify-between"
                     style="background:linear-gradient(135deg,#15803D,#16a34a)">
                    <div>
                        <p class="text-white/60 text-xs uppercase tracking-wider">Registrar abono</p>
                        <p class="font-display font-bold text-white" x-text="deudaActiva?.factura"></p>
                    </div>
                    <button @click="modalAbono=false"
                            class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white hover:bg-white/20">
                        <span class="icon icon-sm">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Deuda pendiente --}}
                    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center justify-between">
                        <p class="text-sm text-red-700 font-semibold">Pendiente total</p>
                        <p class="font-display font-bold text-red-700 text-lg"
                           x-text="'$' + Number(deudaActiva?.pendiente ?? 0).toLocaleString('es-CO')"></p>
                    </div>

                    {{-- Campo monto --}}
                    <div>
                        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                            Monto del abono ($) <span class="text-red-400">*</span>
                        </label>
                        <input type="number"
                               x-model="monto"
                               :max="deudaActiva?.pendiente"
                               min="1"
                               step="500"
                               class="field text-lg font-bold text-center"
                               placeholder="0">
                        <p class="text-xs text-espresso-700/40 mt-1 text-center">
                            Máximo: $<span x-text="Number(deudaActiva?.pendiente ?? 0).toLocaleString('es-CO')"></span>
                        </p>
                    </div>

                    {{-- Botones acciones rápidas --}}
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="monto = deudaActiva?.pendiente"
                                class="py-2 text-xs bg-cream-100 hover:bg-cream-200 text-espresso-700 font-bold rounded-lg transition-colors">
                            Total
                        </button>
                        <button @click="monto = Math.round(deudaActiva?.pendiente / 2)"
                                class="py-2 text-xs bg-cream-100 hover:bg-cream-200 text-espresso-700 font-bold rounded-lg transition-colors">
                            50%
                        </button>
                        <button @click="monto = Math.round(deudaActiva?.pendiente / 4)"
                                class="py-2 text-xs bg-cream-100 hover:bg-cream-200 text-espresso-700 font-bold rounded-lg transition-colors">
                            25%
                        </button>
                    </div>

                    {{-- Botones --}}
                    <div class="flex gap-3 pt-1">
                        <button @click="modalAbono=false"
                                class="flex-1 btn-ghost justify-center py-3">
                            Cancelar
                        </button>
                        <button @click="registrar()"
                                :disabled="!monto || monto <= 0 || procesando"
                                :class="monto > 0 && !procesando
                                    ? 'bg-green-600 hover:bg-green-700 text-white'
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                                class="flex-1 font-bold py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-all">
                            <template x-if="!procesando">
                                <span class="flex items-center gap-2">
                                    <span class="icon icon-sm">payments</span> Registrar
                                </span>
                            </template>
                            <template x-if="procesando">
                                <span class="flex items-center gap-2">
                                    <span class="icon icon-sm" style="animation:spin 1s linear infinite">progress_activity</span>
                                    Guardando…
                                </span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- fin tab deudas --}}

    {{-- ══════════════════════════════════════════════════════════
         TAB: DIRECCIONES
    ══════════════════════════════════════════════════════════ --}}
    <div x-show="tab==='direcciones'" x-transition>
        @php $dirs = $customer->direcciones ?? []; @endphp
        @if(empty($dirs))
        <div class="card p-16 text-center">
            <span class="icon text-4xl text-espresso-700/20 block mb-3">location_off</span>
            <p class="text-sm font-medium text-espresso-700/40 mb-1">Sin direcciones guardadas</p>
            <p class="text-xs text-espresso-700/30">Se guardan al registrar un domicilio con "Guardar en perfil" activado</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($dirs as $idx => $dir)
            @php
                $vecesUsada = \App\Models\DeliveryOrder::where('customer_id', $customer->id)
                    ->where('delivery_address', $dir['direccion'])->count();
            @endphp
            <div class="card p-5 border-2 border-cream-200 hover:border-brand-300 transition-all">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-100 flex items-center justify-center flex-shrink-0">
                        <span class="icon text-brand-600">location_on</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-espresso-900 text-sm">{{ $dir['direccion'] }}</p>
                        @if(!empty($dir['barrio']))
                        <p class="text-xs text-espresso-700/50 mt-0.5">{{ $dir['barrio'] }}</p>
                        @endif
                        @if($vecesUsada > 0)
                        <p class="text-xs text-espresso-700/40 mt-2 flex items-center gap-1">
                            <span class="icon icon-sm">local_shipping</span>
                            {{ $vecesUsada }} {{ $vecesUsada === 1 ? 'domicilio' : 'domicilios' }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-xs text-espresso-700/40 mt-4 text-center">
            Las direcciones se agregan automáticamente con "Guardar en perfil" activo.
        </p>
        @endif
    </div>

</div>{{-- fin x-data --}}
@endsection