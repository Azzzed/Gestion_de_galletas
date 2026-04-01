@extends('layouts.app')
@section('title','Historial de Ventas')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
            <span class="icon icon-lg text-brand-500">receipt_long</span>
            Historial de Ventas
        </h1>
        <p class="page-subtitle">Registro y análisis de transacciones</p>
    </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $kpiData = [
            ['Total ventas',    number_format($kpis->total_ventas ?? 0),                     'receipt_long',    'brand'],
            ['Ingresos',        '$'.number_format($kpis->ingresos ?? 0, 0, ',', '.'),         'payments',        'green'],
            ['Ticket promedio', '$'.number_format($kpis->ticket_promedio ?? 0, 0, ',', '.'), 'analytics',       'amber'],
            ['Período',         request('desde','Hoy'),                                        'calendar_today',  'gray'],
        ];
        $kpiColors = [
            'brand' => ['from-espresso-900 via-espresso-800 to-brand-600','bg-white/15','text-white'],
            'green' => ['bg-white border border-green-100','bg-green-100','text-green-700'],
            'amber' => ['bg-white border border-amber-100','bg-amber-100','text-amber-700'],
            'gray'  => ['bg-white border border-cream-200','bg-cream-100','text-espresso-700'],
        ];
    @endphp
    @foreach($kpiData as [$label, $valor, $icono, $color])
    @php $c = $kpiColors[$color]; @endphp
    <div class="rounded-2xl p-5 shadow-sm {{ str_contains($c[0],'from-') ? 'bg-gradient-to-br '.$c[0] : $c[0] }}">
        <div class="flex items-start gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl {{ $c[1] }} flex items-center justify-center flex-shrink-0">
                <span class="icon icon-sm {{ $c[2] }}">{{ $icono }}</span>
            </div>
        </div>
        <p class="font-display font-bold text-xl {{ str_contains($c[0],'from-') ? 'text-white' : 'text-espresso-900' }}">{{ $valor }}</p>
        <p class="text-xs font-semibold mt-0.5 {{ str_contains($c[0],'from-') ? 'text-white/60' : 'text-espresso-700/50' }} uppercase tracking-wide">{{ $label }}</p>
    </div>
    @endforeach
</div>

{{-- Filtros --}}
<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-[10px] text-espresso-700/50 uppercase tracking-wider font-bold block mb-1">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" class="field text-sm py-2">
        </div>
        <div>
            <label class="text-[10px] text-espresso-700/50 uppercase tracking-wider font-bold block mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="field text-sm py-2">
        </div>
        <div>
            <label class="text-[10px] text-espresso-700/50 uppercase tracking-wider font-bold block mb-1">Estado</label>
            <select name="estado" class="field text-sm py-2 w-auto min-w-[150px]">
                <option value="">Todos</option>
                <option value="completada" @selected(request('estado')==='completada')>Completada</option>
                <option value="anulada"    @selected(request('estado')==='anulada')>Anulada</option>
                <option value="pendiente"  @selected(request('estado')==='pendiente')>Pendiente</option>
            </select>
        </div>
        <div>
            <label class="text-[10px] text-espresso-700/50 uppercase tracking-wider font-bold block mb-1">Pago</label>
            <select name="metodo_pago" class="field text-sm py-2 w-auto min-w-[150px]">
                <option value="">Todos</option>
                <option value="efectivo"      @selected(request('metodo_pago')==='efectivo')>Efectivo</option>
                <option value="transferencia" @selected(request('metodo_pago')==='transferencia')>Transferencia</option>
                <option value="tarjeta"       @selected(request('metodo_pago')==='tarjeta')>Tarjeta</option>
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="text-[10px] text-espresso-700/50 uppercase tracking-wider font-bold block mb-1">Cliente</label>
            <div class="relative">
                <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">search</span>
                <input type="text" name="cliente" value="{{ request('cliente') }}"
                       placeholder="Buscar cliente…" class="field pl-9 text-sm py-2">
            </div>
        </div>
        <div class="flex gap-2 items-end">
            <button type="submit" class="btn-primary py-2">
                <span class="icon icon-sm">filter_list</span>Filtrar
            </button>
            @if(request()->hasAny(['desde','hasta','estado','metodo_pago','cliente']))
            <a href="{{ route('admin.sales.index') }}" class="btn-ghost py-2">
                <span class="icon icon-sm">close</span>
            </a>
            @endif
        </div>
    </form>
</div>

{{-- Tabla con modal --}}
<div x-data="salesTable()" class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="tbl-head">
                <tr>
                    <th>Factura</th>
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
                <tr class="tbl-row">
                    <td>
                        <span class="font-mono text-sm font-bold text-brand-700">{{ $venta->numero_factura }}</span>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($venta->customer->nombre, 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium text-espresso-800">{{ $venta->customer->nombre }}</span>
                        </div>
                    </td>
                    <td>
                        <p class="text-sm font-medium text-espresso-800">{{ $venta->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-espresso-700/40">{{ $venta->created_at->format('H:i') }}</p>
                    </td>
                    <td class="text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-xs font-bold">
                            {{ $venta->items_count }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-center gap-1.5 text-sm text-espresso-700">
                            @php $payIcons = ['efectivo'=>'payments','transferencia'=>'phone_iphone','tarjeta'=>'credit_card']; @endphp
                            <span class="icon icon-sm text-brand-400">{{ $payIcons[$venta->metodo_pago] ?? 'payment' }}</span>
                            <span class="capitalize">{{ $venta->metodo_pago }}</span>
                        </div>
                    </td>
                    <td>
                        <p class="font-bold text-espresso-900">{{ $venta->total_formateado }}</p>
                        @if($venta->tiene_deuda)
                        <span class="badge badge-red text-[10px]">
                            <span class="icon icon-sm">credit_card_off</span>Deuda
                        </span>
                        @endif
                    </td>
                    <td>
                        @php
                            $stMap = ['completada'=>'badge-green','anulada'=>'badge-red','pendiente'=>'badge-amber'];
                        @endphp
                        <span class="badge {{ $stMap[$venta->estado] ?? 'badge-gray' }}">
                            {{ ucfirst($venta->estado) }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center gap-1 justify-end">
                            <button @click="abrirDetalle({{ $venta->id }})"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-brand-600 hover:bg-brand-50 transition-colors"
                                    title="Ver detalle">
                                <span class="icon icon-sm">visibility</span>
                            </button>
                            <a href="{{ route('admin.sales.pdf', $venta) }}"
                               class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-red-500 hover:bg-red-50 transition-colors"
                               title="PDF">
                                <span class="icon icon-sm">picture_as_pdf</span>
                            </a>
                            @if($venta->estado === 'completada')
                            <button @click="anular({{ $venta->id }})"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-red-500 hover:bg-red-50 transition-colors"
                                    title="Anular">
                                <span class="icon icon-sm">block</span>
                            </button>
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

    {{-- Modal detalle --}}
    <div x-show="modalAbierto" x-cloak x-transition
         @click.self="cerrarModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden fade-in">

            <div class="px-6 py-4 flex items-center justify-between" style="background:linear-gradient(135deg,#1a0a00,#ea6008)">
                <div>
                    <p class="text-white/60 text-xs uppercase tracking-wider">Detalle de venta</p>
                    <p class="font-display font-bold text-white text-xl font-mono" x-text="venta?.numero_factura"></p>
                </div>
                <button @click="cerrarModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
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
                                <p class="text-[10px] text-espresso-700/50 uppercase tracking-wide font-bold mb-1">Cliente</p>
                                <p class="font-bold text-espresso-900" x-text="venta.cliente"></p>
                            </div>
                            <div class="bg-cream-50 rounded-xl p-3 border border-cream-200">
                                <p class="text-[10px] text-espresso-700/50 uppercase tracking-wide font-bold mb-1">Fecha</p>
                                <p class="font-bold text-espresso-900" x-text="venta.fecha"></p>
                            </div>
                            <div class="bg-cream-50 rounded-xl p-3 border border-cream-200">
                                <p class="text-[10px] text-espresso-700/50 uppercase tracking-wide font-bold mb-1">Método de pago</p>
                                <p class="font-bold text-espresso-900 capitalize" x-text="venta.metodo_pago"></p>
                            </div>
                            <div class="bg-cream-50 rounded-xl p-3 border border-cream-200">
                                <p class="text-[10px] text-espresso-700/50 uppercase tracking-wide font-bold mb-1">Estado</p>
                                <p class="font-bold capitalize"
                                   :class="venta.estado==='completada'?'text-green-600':'text-red-500'"
                                   x-text="venta.estado"></p>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] text-espresso-700/50 uppercase tracking-wider font-bold mb-2">Productos</p>
                            <div class="space-y-1.5">
                                <template x-for="item in venta.items" :key="item.nombre">
                                    <div class="flex items-center justify-between bg-cream-50 rounded-xl px-4 py-2.5 border border-cream-200">
                                        <div>
                                            <span class="text-sm font-bold text-espresso-900" x-text="item.nombre"></span>
                                            <span class="text-xs text-espresso-700/40 ml-1 capitalize" x-text="'· '+item.tamano"></span>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-espresso-700/50" x-text="item.cantidad+'× $'+Number(item.precio_unitario).toLocaleString('es-CO')"></p>
                                            <p class="text-sm font-bold text-espresso-900" x-text="'$'+Number(item.subtotal).toLocaleString('es-CO')"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-2xl overflow-hidden" style="background:#fbf3e2;border:1px solid #eecf93">
                            <div class="px-4 py-3 space-y-1.5">
                                <div class="flex justify-between text-sm text-espresso-700/60">
                                    <span>Subtotal</span>
                                    <span x-text="'$'+Number(venta.subtotal).toLocaleString('es-CO')"></span>
                                </div>
                                <template x-if="venta.descuento > 0">
                                    <div class="flex justify-between text-sm text-red-500">
                                        <span>Descuento</span>
                                        <span x-text="'− $'+Number(venta.descuento).toLocaleString('es-CO')"></span>
                                    </div>
                                </template>
                                <div class="flex justify-between font-display font-bold text-lg text-espresso-900 border-t border-brand-200 pt-2">
                                    <span>Total</span>
                                    <span class="text-brand-700" x-text="venta.total_formateado"></span>
                                </div>
                            </div>
                        </div>

                        <div x-show="venta.tiene_deuda" class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                            <span class="icon icon-sm">credit_card_off</span>
                            Esta venta tiene deuda pendiente
                        </div>
                    </div>
                </template>
            </div>

            <div class="px-6 pb-6 flex gap-3">
                <a :href="venta ? `/admin/sales/${venta.id}/pdf` : '#'"
                   class="flex-1 btn-ghost justify-center py-2.5">
                    <span class="icon icon-sm">picture_as_pdf</span>Exportar PDF
                </a>
                <button @click="cerrarModal()" class="flex-1 btn-ghost justify-center py-2.5">
                    <span class="icon icon-sm">close</span>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">{{ $ventas->links() }}</div>
@endsection

@push('styles')
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endpush

@push('scripts')
<script>
function salesTable() {
    return {
        modalAbierto: false, cargando: false, venta: null,
        async abrirDetalle(id) {
            this.modalAbierto=true; this.cargando=true; this.venta=null;
            const res = await fetch(`/admin/sales/${id}/detalle`);
            this.venta = await res.json(); this.cargando=false;
        },
        cerrarModal() { this.modalAbierto=false; this.venta=null; },
        async anular(id) {
            if (!confirm('¿Estás seguro de que deseas anular esta venta?')) return;
            const motivo = prompt('Motivo de anulación (opcional):') ?? '';
            const res = await fetch(`/admin/sales/${id}/anular`, {
                method:'PATCH',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body:JSON.stringify({motivo}),
            });
            const data = await res.json();
            if (data.success) window.location.reload();
            else alert('Error al anular: '+data.message);
        },
    };
}
</script>
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush