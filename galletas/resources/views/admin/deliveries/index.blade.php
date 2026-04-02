@extends('layouts.app')
@section('title','Gestión de Domicilios')

@section('content')

{{-- Alpine cargado ANTES del contenido --}}
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js" defer></script>

<div x-data="{
        modal: false,
        detail: null,
        loadingDetail: false,
        showPayForm: false,
        payAmount: 0,

        async openDetail(id) {
            this.modal = true;
            this.loadingDetail = true;
            this.detail = null;
            this.showPayForm = false;
            const r = await fetch('/admin/deliveries/' + id, {
                headers: { 'Accept': 'application/json' }
            });
            this.detail = await r.json();
            this.loadingDetail = false;
        },

        async changeStatus(id, status) {
            const r = await fetch('/admin/deliveries/' + id + '/status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ status })
            });
            const data = await r.json();
            if (data.success) { this.modal = false; window.location.reload(); }
            else alert(data.message);
        },

        async cancelOrder(id) {
            if (!confirm('¿Cancelar este domicilio? El stock será restaurado.')) return;
            const r = await fetch('/admin/deliveries/' + id + '/cancel', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });
            const data = await r.json();
            if (data.success) { this.modal = false; window.location.reload(); }
            else alert(data.message);
        },

        async registerPay(id) {
            if (!this.payAmount || this.payAmount <= 0) return;
            const r = await fetch('/admin/deliveries/' + id + '/payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ amount: this.payAmount })
            });
            const data = await r.json();
            if (data.success) { alert(data.message); window.location.reload(); }
            else alert(data.message);
        }
    }">

    {{-- ── HEADER ──────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">local_shipping</span>
                Gestión de Domicilios
            </h1>
            <p class="page-subtitle">Panel en tiempo real — {{ today()->format('d/m/Y') }}</p>
        </div>
        <form method="GET" class="flex gap-2 flex-wrap items-center">
            <input type="date" name="fecha"
                   value="{{ request('fecha', today()->toDateString()) }}"
                   class="field text-sm py-2 w-auto"
                   onchange="this.form.submit()">
            <input type="text" name="buscar"
                   value="{{ request('buscar') }}"
                   placeholder="Buscar cliente o dirección…"
                   class="field text-sm py-2 min-w-48">
            <button type="submit" class="btn-primary py-2">
                <span class="icon icon-sm">search</span>
            </button>
        </form>
    </div>

    {{-- ── KPIs ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Total del día',    $kpis['total_orders'],                                                'receipt_long',   'bg-espresso-900'],
            ['Entregados',       $kpis['delivered_today'],                                             'check_circle',   'bg-green-600'],
            ['Ingresos',         '$'.number_format($kpis['total_revenue'],0,',','.'),                  'payments',       'bg-brand-500'],
            ['Pago pendiente',   '$'.number_format($kpis['pending_payment'],0,',','.'),                'credit_card_off','bg-red-500'],
        ] as [$label, $value, $icon, $bg])
        <div class="{{ $bg }} rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-2 mb-2">
                <span class="icon icon-sm text-white opacity-70">{{ $icon }}</span>
                <p class="text-white opacity-70 text-xs font-bold uppercase tracking-wide">{{ $label }}</p>
            </div>
            <p class="text-white font-display font-bold text-2xl">{{ $value }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── KANBAN ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- AGENDADOS --}}
        <div class="flex flex-col">
            <div class="flex items-center gap-2 mb-3 px-1">
                <span class="w-3 h-3 rounded-full bg-amber-400 flex-shrink-0"></span>
                <h2 class="font-display font-bold text-espresso-900">Agendados</h2>
                <span class="ml-auto text-xs font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">
                    {{ $scheduled->count() }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse($scheduled as $d)
                    @include('admin.deliveries._card', ['delivery' => $d])
                @empty
                    <div class="text-center py-10 text-espresso-700/30 bg-white rounded-2xl border border-cream-200">
                        <span class="icon text-3xl block mb-2">inbox</span>
                        <p class="text-sm font-medium">Sin pedidos agendados</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- EN DESPACHO --}}
        <div class="flex flex-col">
            <div class="flex items-center gap-2 mb-3 px-1">
                <span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0"></span>
                <h2 class="font-display font-bold text-espresso-900">En Despacho</h2>
                <span class="ml-auto text-xs font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                    {{ $dispatched->count() }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse($dispatched as $d)
                    @include('admin.deliveries._card', ['delivery' => $d])
                @empty
                    <div class="text-center py-10 text-espresso-700/30 bg-white rounded-2xl border border-cream-200">
                        <span class="icon text-3xl block mb-2">local_shipping</span>
                        <p class="text-sm font-medium">Nada en camino</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ENTREGADOS --}}
        <div class="flex flex-col">
            <div class="flex items-center gap-2 mb-3 px-1">
                <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                <h2 class="font-display font-bold text-espresso-900">Entregados hoy</h2>
                <span class="ml-auto text-xs font-bold bg-green-100 text-green-800 px-2 py-0.5 rounded-full">
                    {{ $delivered->count() }}
                </span>
            </div>
            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                @forelse($delivered as $d)
                    @include('admin.deliveries._card', ['delivery' => $d])
                @empty
                    <div class="text-center py-10 text-espresso-700/30 bg-white rounded-2xl border border-cream-200">
                        <span class="icon text-3xl block mb-2">check_circle</span>
                        <p class="text-sm font-medium">Sin entregas hoy</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>{{-- fin kanban --}}

    {{-- ── MODAL DETALLE ───────────────────────────────────────────── --}}
    <div x-show="modal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display:none"
         @click.self="modal=false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-900/50 backdrop-blur-sm">

        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">

            {{-- Header modal --}}
            <div class="px-6 py-4 flex items-center justify-between"
                 style="background:linear-gradient(135deg,#1a0a00,#ea6008)">
                <div>
                    <p class="text-white/60 text-xs uppercase tracking-wider">
                        Pedido #<span x-text="detail?.id"></span>
                    </p>
                    <p class="font-display font-bold text-white text-lg"
                       x-text="detail?.display_name ?? '...'"></p>
                </div>
                <button @click="modal=false"
                        class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white hover:bg-white/20">
                    <span class="icon icon-sm">close</span>
                </button>
            </div>

            {{-- Loading --}}
            <div x-show="loadingDetail" class="flex justify-center py-10">
                <span class="icon text-3xl text-brand-500"
                      style="animation:spin 1s linear infinite">progress_activity</span>
            </div>

            {{-- Contenido --}}
            <div x-show="detail && !loadingDetail" class="p-6 space-y-4 max-h-[65vh] overflow-y-auto">

                {{-- Datos básicos --}}
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-cream-50 rounded-xl p-3 border border-cream-200">
                        <p class="text-[10px] text-espresso-700/50 uppercase font-bold mb-1">Teléfono</p>
                        <p class="font-bold" x-text="detail?.display_phone"></p>
                    </div>
                    <div class="bg-cream-50 rounded-xl p-3 border border-cream-200">
                        <p class="text-[10px] text-espresso-700/50 uppercase font-bold mb-1">Forma de pago</p>
                        <p class="font-bold" x-text="detail?.payment_method_label"></p>
                    </div>
                    <div class="bg-cream-50 rounded-xl p-3 border border-cream-200 col-span-2">
                        <p class="text-[10px] text-espresso-700/50 uppercase font-bold mb-1">Dirección</p>
                        <p class="font-bold" x-text="detail?.delivery_address"></p>
                        <p class="text-xs text-espresso-700/50 mt-0.5" x-text="detail?.delivery_neighborhood"></p>
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <p class="text-[10px] font-bold text-espresso-700/50 uppercase tracking-wide mb-2">Productos</p>
                    <div class="space-y-1.5">
                        <template x-for="item in (detail?.items ?? [])" :key="item.cookie_id">
                            <div class="flex items-center justify-between bg-cream-50 rounded-xl px-4 py-2.5 border border-cream-200">
                                <span class="text-sm font-bold text-espresso-900" x-text="item.nombre"></span>
                                <div class="text-right">
                                    <p class="text-xs text-espresso-700/50"
                                       x-text="item.cantidad + '× $' + Number(item.precio_unitario).toLocaleString('es-CO')"></p>
                                    <p class="text-sm font-bold"
                                       x-text="'$' + Number(item.subtotal).toLocaleString('es-CO')"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Totales --}}
                <div class="bg-cream-50 rounded-xl p-4 border border-cream-200 space-y-1.5 text-sm">
                    <div class="flex justify-between text-espresso-700/60">
                        <span>Subtotal</span>
                        <span x-text="detail?.subtotal_formatted"></span>
                    </div>
                    <div class="flex justify-between text-espresso-700/60">
                        <span>Envío (<span x-text="detail?.delivery_cost_type_label"></span>)</span>
                        <span x-text="detail?.delivery_cost_formatted"></span>
                    </div>
                    <template x-if="detail?.discount_amount > 0">
                        <div class="flex justify-between text-green-600 font-semibold">
                            <span>Descuento <span x-text="detail?.promo_code ? '(' + detail.promo_code + ')' : ''"></span></span>
                            <span x-text="'- $' + Number(detail?.discount_amount).toLocaleString('es-CO')"></span>
                        </div>
                    </template>
                    <div class="flex justify-between font-bold text-lg border-t border-cream-200 pt-1.5">
                        <span>Total</span>
                        <span class="text-brand-700" x-text="detail?.total_formatted"></span>
                    </div>
                </div>

                {{-- Pago pendiente --}}
                <div x-show="detail?.payment_status !== 'paid'"
                     class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                    <span class="icon text-red-500">credit_card_off</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-red-700">
                            Pago pendiente: <span x-text="detail?.remaining_formatted"></span>
                        </p>
                    </div>
                    <button @click="showPayForm=!showPayForm"
                            class="px-3 py-1.5 bg-green-500 text-white text-xs font-bold rounded-lg hover:bg-green-600 transition">
                        Registrar
                    </button>
                </div>

                <div x-show="showPayForm" class="flex gap-2">
                    <input type="number" x-model="payAmount"
                           :max="detail?.remaining"
                           class="field text-sm flex-1"
                           placeholder="Monto a registrar">
                    <button @click="registerPay(detail.id)"
                            class="btn-primary py-2 text-sm">Guardar</button>
                </div>

                {{-- Notas --}}
                <div x-show="detail?.notes"
                     class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-sm text-amber-800">
                    <p class="font-bold mb-0.5">Notas:</p>
                    <p x-text="detail?.notes"></p>
                </div>

                {{-- Acciones de estado --}}
                <div x-show="detail?.status !== 'delivered' && detail?.status !== 'cancelled'"
                     class="flex gap-2 pt-1">
                    <button x-show="detail?.status === 'scheduled'"
                            @click="changeStatus(detail.id, 'dispatched')"
                            class="flex-1 btn-primary justify-center py-2.5">
                        <span class="icon icon-sm">local_shipping</span> Despachar
                    </button>
                    <button x-show="detail?.status === 'dispatched'"
                            @click="changeStatus(detail.id, 'delivered')"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-xl transition inline-flex items-center justify-center gap-2">
                        <span class="icon icon-sm">check_circle</span> Marcar Entregado
                    </button>
                    <button @click="cancelOrder(detail.id)"
                            class="px-4 py-2.5 bg-red-50 border border-red-200 text-red-600 font-bold rounded-xl hover:bg-red-100 transition text-sm">
                        Cancelar
                    </button>
                </div>

            </div>{{-- fin contenido modal --}}
        </div>
    </div>{{-- fin modal --}}

</div>{{-- fin x-data --}}

<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection