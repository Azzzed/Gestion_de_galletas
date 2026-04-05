@extends('layouts.app')
@section('title','Historial de Ventas')

@section('content')
<div x-data="salesTable()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">receipt_long</span>
                Historial de Ventas
            </h1>
            <p class="page-subtitle">Registro, análisis y búsqueda avanzada</p>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl p-5 shadow-sm bg-gradient-to-br from-espresso-900 to-darkBrown">
            <span class="icon text-white/60 block mb-2">receipt_long</span>
            <p class="font-display font-bold text-white text-2xl">{{ number_format($kpis->total_ventas ?? 0) }}</p>
            <p class="text-white/60 text-xs font-bold uppercase tracking-wide mt-1">Ventas en período</p>
        </div>
        <div class="card p-5">
            <span class="icon text-green-500 block mb-2">payments</span>
            <p class="font-display font-bold text-espresso-900 text-2xl">${{ number_format($kpis->ingresos ?? 0, 0, ',', '.') }}</p>
            <p class="text-espresso-700/50 text-xs font-bold uppercase tracking-wide mt-1">Ingresos totales</p>
        </div>
        <div class="card p-5">
            <span class="icon text-purple-500 block mb-2">analytics</span>
            <p class="font-display font-bold text-espresso-900 text-2xl">${{ number_format($kpis->ticket_promedio ?? 0, 0, ',', '.') }}</p>
            <p class="text-espresso-700/50 text-xs font-bold uppercase tracking-wide mt-1">Ticket promedio</p>
        </div>
    </div>

    {{-- Filtros avanzados --}}
    <div class="card p-5 mb-6">
        <p class="text-xs font-bold text-espresso-700/50 uppercase tracking-wider mb-3 flex items-center gap-1">
            <span class="icon icon-sm">filter_list</span>Filtros avanzados
        </p>
        <form method="GET" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="field text-sm py-2">
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="field text-sm py-2">
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Estado</label>
                <select name="estado" class="field text-sm py-2">
                    <option value="">Todos</option>
                    <option value="completada" @selected(request('estado')==='completada')>Completada</option>
                    <option value="anulada"    @selected(request('estado')==='anulada')>Anulada</option>
                    {{-- Nota: filtrar por "entregado" oculta domicilios (filtro solo aplica a ventas POS) --}}
                </select>
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Método pago</label>
                <select name="metodo_pago" class="field text-sm py-2">
                    <option value="">Todos</option>
                    <option value="efectivo"      @selected(request('metodo_pago')==='efectivo')>Efectivo</option>
                    <option value="transferencia" @selected(request('metodo_pago')==='transferencia')>Nequi / Transf.</option>
                    <option value="tarjeta"       @selected(request('metodo_pago')==='tarjeta')>Tarjeta</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Galleta</label>
                <select name="cookie_id" class="field text-sm py-2">
                    <option value="">Todas</option>
                    @foreach($cookies as $c)
                    <option value="{{ $c->id }}" @selected(request('cookie_id') == $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Cliente</label>
                <input type="text" name="cliente" value="{{ request('cliente') }}" placeholder="Nombre o tel."
                       class="field text-sm py-2">
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Monto mín.</label>
                <input type="number" name="monto_min" value="{{ request('monto_min') }}" placeholder="0" class="field text-sm py-2">
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Monto máx.</label>
                <input type="number" name="monto_max" value="{{ request('monto_max') }}" placeholder="999999" class="field text-sm py-2">
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Ordenar por</label>
                <select name="order_by" class="field text-sm py-2">
                    <option value="created_at" @selected(request('order_by','created_at')==='created_at')>Fecha</option>
                    <option value="total"       @selected(request('order_by')==='total')>Total</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] text-espresso-700/50 uppercase font-bold block mb-1">Dirección</label>
                <select name="order_dir" class="field text-sm py-2">
                    <option value="desc" @selected(request('order_dir','desc')==='desc')>Más reciente</option>
                    <option value="asc"  @selected(request('order_dir')==='asc')>Más antiguo</option>
                </select>
            </div>
            <div class="flex items-end gap-2 col-span-2">
                <button type="submit" class="btn-primary py-2 flex-1">
                    <span class="icon icon-sm">search</span>Buscar
                </button>
                @if(request()->hasAny(['desde','hasta','estado','metodo_pago','cliente','cookie_id','monto_min','monto_max']))
                <a href="{{ route('admin.sales.index') }}" class="btn-ghost py-2">
                    <span class="icon icon-sm">close</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Layout: tabla + top clientes --}}
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-5">

        {{-- Tabla principal --}}
        <div class="xl:col-span-3">
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="tbl-head">
                            <tr>
                                <th>Factura / Ref.</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th class="text-center">Items</th>
                                <th>Pago</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th class="text-right pr-6">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventas as $venta)
                            @php
                                $esEntrega = ($venta->_type ?? 'sale') === 'delivery';
                                $payIcons  = [
                                    'efectivo'      => 'payments',
                                    'transferencia' => 'phone_iphone',
                                    'nequi'         => 'phone_iphone',
                                    'daviplata'     => 'account_balance_wallet',
                                    'tarjeta'       => 'credit_card',
                                    'contraentrega' => 'delivery_dining',
                                ];
                            @endphp
                            <tr class="tbl-row {{ $esEntrega ? 'bg-blue-50/40' : '' }}">

                                {{-- Factura --}}
                                <td>
                                    <span class="font-mono text-sm font-bold {{ $esEntrega ? 'text-blue-600' : 'text-brand-700' }}">
                                        {{ $venta->numero_factura }}
                                    </span>
                                    @if($esEntrega)
                                    <span class="block text-[10px] font-bold text-blue-400 mt-0.5">🛵 Domicilio</span>
                                    @endif
                                </td>

                                {{-- Cliente --}}
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full {{ $esEntrega ? 'bg-blue-100' : 'bg-brand-100' }} flex items-center justify-center {{ $esEntrega ? 'text-blue-700' : 'text-brand-700' }} font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($venta->customer->nombre ?? '?', 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-medium truncate max-w-[120px]">
                                            {{ $venta->customer->nombre ?? '—' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Fecha --}}
                                <td>
                                    <p class="text-sm font-medium">{{ $venta->created_at->format('d/m/Y') }}</p>
                                    <p class="text-xs text-espresso-700/40">{{ $venta->created_at->format('H:i') }}</p>
                                </td>

                                {{-- Items --}}
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $esEntrega ? 'bg-blue-100 text-blue-700' : 'bg-brand-100 text-brand-700' }} text-xs font-bold">
                                        {{ $venta->items_count }}
                                    </span>
                                </td>

                                {{-- Método de pago --}}
                                <td>
                                    <div class="flex items-center gap-1 text-sm">
                                        <span class="icon icon-sm text-brand-400">{{ $payIcons[$venta->metodo_pago] ?? 'payment' }}</span>
                                        <span class="capitalize text-xs">{{ $venta->metodo_pago }}</span>
                                    </div>
                                </td>

                                {{-- Total --}}
                                <td>
                                    <p class="font-bold">{{ $venta->total_formateado }}</p>
                                    @if($venta->tiene_deuda)
                                    <span class="badge badge-red text-[10px]">
                                        <span class="icon icon-sm">credit_card_off</span>
                                        {{ $esEntrega ? 'Pend.' : 'Deuda' }}
                                    </span>
                                    @endif
                                </td>

                                {{-- Estado --}}
                                <td>
                                    @if($esEntrega)
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                        ✅ Entregado
                                    </span>
                                    @else
                                    @php $stMap = ['completada'=>'badge-green','anulada'=>'badge-red','pendiente'=>'badge-amber']; @endphp
                                    <span class="badge {{ $stMap[$venta->estado] ?? 'badge-gray' }}">{{ ucfirst($venta->estado) }}</span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="text-right">
                                    <div class="flex items-center gap-1 justify-end">
                                        @if($esEntrega)
                                        {{-- Domicilio → ir al detalle del kanban --}}
                                        <a href="{{ route('admin.deliveries.show', $venta->id) }}"
                                           title="Ver domicilio"
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                            <span class="icon icon-sm">local_shipping</span>
                                        </a>
                                        @else
                                        {{-- Venta POS → modal detalle + PDF + anular --}}
                                        <button @click="abrirDetalle({{ $venta->id }})"
                                                title="Ver detalle"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-brand-600 hover:bg-brand-50 transition-colors">
                                            <span class="icon icon-sm">visibility</span>
                                        </button>
                                        <a href="{{ route('admin.sales.pdf', $venta->id) }}"
                                           title="Descargar PDF"
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-red-500 hover:bg-red-50 transition-colors">
                                            <span class="icon icon-sm">picture_as_pdf</span>
                                        </a>
                                        @if($venta->estado === 'completada')
                                        <button @click="anular({{ $venta->id }})"
                                                title="Anular venta"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-red-500 hover:bg-red-50 transition-colors">
                                            <span class="icon icon-sm">block</span>
                                        </button>
                                        @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-20 text-center">
                                    <span class="icon icon-2xl text-espresso-700/20 block mb-3">receipt_long</span>
                                    <p class="text-sm font-medium text-espresso-700/40">No hay ventas para mostrar</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">{{ $ventas->links() }}</div>
        </div>

        {{-- Panel lateral: Top Clientes --}}
        <div class="xl:col-span-1">
            <div class="card p-5 sticky top-24">
                <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4 text-base">
                    <span class="icon text-brand-500">star</span>
                    Clientes Top
                </h3>
                <p class="text-xs text-espresso-700/40 mb-3">Del período filtrado</p>
                <div class="space-y-2">
                    @forelse($topClientes as $i => $tc)
                    <div class="flex items-center gap-2 p-2.5 rounded-xl bg-cream-50 border border-cream-200">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0
                                     {{ $i === 0 ? 'bg-amber-400 text-white' : ($i === 1 ? 'bg-slate-300 text-white' : 'bg-cream-200 text-espresso-700') }}">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-espresso-900 truncate">{{ $tc->customer?->nombre ?? '—' }}</p>
                            <p class="text-[10px] text-espresso-700/50">{{ $tc->compras }} compras</p>
                        </div>
                        <span class="text-xs font-bold text-brand-700 flex-shrink-0">
                            ${{ number_format($tc->total_gastado, 0, ',', '.') }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-espresso-700/40 text-center py-4">Sin datos</p>
                    @endforelse
                </div>
                <div class="mt-4 pt-3 border-t border-cream-200">
                    <a href="{{ route('admin.stats.index') }}" class="btn-ghost w-full justify-center py-2 text-xs">
                        <span class="icon icon-sm">analytics</span> Ver estadísticas completas
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal detalle (solo para ventas POS) --}}
    <div x-show="modalAbierto" x-cloak x-transition
         @click.self="cerrarModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden fade-in">
            <div class="px-6 py-4 flex items-center justify-between" style="background:linear-gradient(135deg,#1a0a00,#ea6008)">
                <div>
                    <p class="text-white/60 text-xs uppercase tracking-wider">Detalle de venta</p>
                    <p class="font-display font-bold text-white text-xl font-mono" x-text="venta?.numero_factura"></p>
                </div>
                <button @click="cerrarModal()" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white">
                    <span class="icon icon-sm">close</span>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div x-show="cargando" class="flex items-center justify-center py-10">
                    <span class="icon icon-xl text-brand-400" style="animation:spin 1s linear infinite">progress_activity</span>
                </div>
                <template x-if="!cargando && venta">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="bg-cream-50 rounded-xl p-3 border border-cream-200">
                                <p class="text-[10px] text-espresso-700/50 uppercase font-bold mb-1">Cliente</p>
                                <p class="font-bold" x-text="venta.cliente"></p>
                            </div>
                            <div class="bg-cream-50 rounded-xl p-3 border border-cream-200">
                                <p class="text-[10px] text-espresso-700/50 uppercase font-bold mb-1">Fecha</p>
                                <p class="font-bold" x-text="venta.fecha"></p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] text-espresso-700/50 uppercase font-bold mb-2">Productos</p>
                            <div class="space-y-1.5">
                                <template x-for="item in venta.items" :key="item.nombre">
                                    <div class="flex items-center justify-between bg-cream-50 rounded-xl px-4 py-2.5 border border-cream-200">
                                        <span class="text-sm font-bold" x-text="item.nombre"></span>
                                        <span class="text-sm font-bold" x-text="'$'+Number(item.subtotal).toLocaleString('es-CO')"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="flex justify-between font-display font-bold text-lg border-t border-cream-200 pt-2">
                            <span>Total</span>
                            <span class="text-brand-700" x-text="venta.total_formateado"></span>
                        </div>
                    </div>
                </template>
            </div>
            <div class="px-6 pb-6 flex gap-3">
                <a :href="venta ? `/admin/sales/${venta.id}/pdf` : '#'" class="flex-1 btn-ghost justify-center py-2.5">
                    <span class="icon icon-sm">picture_as_pdf</span>PDF
                </a>
                <button @click="cerrarModal()" class="flex-1 btn-ghost justify-center py-2.5">Cerrar</button>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endpush

@push('scripts')
<script>
function salesTable() {
    return {
        modalAbierto: false,
        cargando: false,
        venta: null,

        async abrirDetalle(id) {
            this.modalAbierto = true;
            this.cargando     = true;
            this.venta        = null;
            const res  = await fetch(`/admin/sales/${id}/detalle`);
            this.venta = await res.json();
            this.cargando = false;
        },

        cerrarModal() {
            this.modalAbierto = false;
            this.venta        = null;
        },

        async anular(id) {
            if (!confirm('¿Estás seguro de que deseas anular esta venta?')) return;
            const motivo = prompt('Motivo de anulación (opcional):') ?? '';
            const res = await fetch(`/admin/sales/${id}/anular`, {
                method:  'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ motivo }),
            });
            const data = await res.json();
            if (data.success) window.location.reload();
        },
    };
}
</script>
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush
@endsection