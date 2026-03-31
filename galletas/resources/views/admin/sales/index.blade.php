@extends('layouts.app')
@section('title','Historial de Ventas')

@section('content')

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $kpiItems = [
            ['Total ventas',    number_format($kpis->total_ventas ?? 0),                              '🧾', 'from-capy-500 to-capy-700'],
            ['Ingresos',        '$'.number_format($kpis->ingresos ?? 0, 0, ',', '.'),                 '💰', 'from-emerald-500 to-emerald-700'],
            ['Ticket promedio', '$'.number_format($kpis->ticket_promedio ?? 0, 0, ',', '.'),          '📊', 'from-amber-500 to-orange-600'],
            ['Período',         request('desde','hoy').' → '.request('hasta','hoy'),                 '📅', 'from-gray-600 to-gray-800'],
        ];
    @endphp
    @foreach($kpiItems as [$label, $valor, $icon, $grad])
    <div class="bg-gradient-to-br {{ $grad }} text-white rounded-2xl p-4 shadow-md">
        <p class="text-2xl mb-1">{{ $icon }}</p>
        <p class="text-xl font-bold leading-tight">{{ $valor }}</p>
        <p class="text-xs text-white/70 mt-1">{{ $label }}</p>
    </div>
    @endforeach
</div>

{{-- Filtros --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6 bg-white p-4 rounded-2xl border border-warm-200 shadow-sm">
    <div>
        <label class="text-xs text-gray-500 block mb-1">Desde</label>
        <input type="date" name="desde" value="{{ request('desde') }}"
               class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-capy-400">
    </div>
    <div>
        <label class="text-xs text-gray-500 block mb-1">Hasta</label>
        <input type="date" name="hasta" value="{{ request('hasta') }}"
               class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-capy-400">
    </div>
    <div>
        <label class="text-xs text-gray-500 block mb-1">Estado</label>
        <select name="estado" class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-capy-400">
            <option value="">Todos</option>
            <option value="completada" @selected(request('estado')==='completada')>✅ Completada</option>
            <option value="anulada"    @selected(request('estado')==='anulada')>❌ Anulada</option>
            <option value="pendiente"  @selected(request('estado')==='pendiente')>⏳ Pendiente</option>
        </select>
    </div>
    <div>
        <label class="text-xs text-gray-500 block mb-1">Método pago</label>
        <select name="metodo_pago" class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-capy-400">
            <option value="">Todos</option>
            <option value="efectivo"      @selected(request('metodo_pago')==='efectivo')>💵 Efectivo</option>
            <option value="transferencia" @selected(request('metodo_pago')==='transferencia')>📱 Transferencia</option>
            <option value="tarjeta"       @selected(request('metodo_pago')==='tarjeta')>💳 Tarjeta</option>
        </select>
    </div>
    <div class="flex-1 min-w-40">
        <label class="text-xs text-gray-500 block mb-1">Cliente</label>
        <input type="text" name="cliente" value="{{ request('cliente') }}"
               placeholder="Buscar cliente…"
               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-capy-400">
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="px-5 py-2 bg-capy-600 text-white rounded-xl text-sm font-medium hover:bg-capy-700 transition-colors">
            Filtrar
        </button>
        @if(request()->hasAny(['desde','hasta','estado','metodo_pago','cliente']))
        <a href="{{ route('admin.sales.index') }}"
           class="px-4 py-2 border border-gray-200 text-gray-500 rounded-xl text-sm hover:bg-gray-50">✕</a>
        @endif
    </div>
</form>

{{-- Tabla con modal de detalle --}}
<div x-data="salesTable()" class="bg-white rounded-2xl border border-warm-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-warm-100">
            <thead class="bg-warm-50">
                <tr>
                    @foreach(['Factura','Cliente','Fecha','Items','Pago','Total','Estado','Acciones'] as $col)
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ $col }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-warm-100">
                @forelse($ventas as $venta)
                <tr class="hover:bg-warm-50 transition-colors">
                    <td class="px-4 py-3">
                        <span class="font-mono text-sm font-bold text-capy-600">
                            {{ $venta->numero_factura }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-capy-100 flex items-center justify-center
                                        text-capy-600 font-bold text-xs shrink-0">
                                {{ strtoupper(substr($venta->customer->nombre, 0, 1)) }}
                            </div>
                            <span class="text-sm text-gray-700">{{ $venta->customer->nombre }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                        {{ $venta->created_at->format('d/m/Y') }}
                        <br>
                        <span class="text-xs text-gray-400">{{ $venta->created_at->format('H:i') }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full
                                     bg-capy-100 text-capy-700 text-xs font-bold">
                            {{ $venta->items_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ match($venta->metodo_pago) {
                            'efectivo'      => '💵',
                            'transferencia' => '📱',
                            'tarjeta'       => '💳',
                            default         => '💰'
                        } }}
                        {{ ucfirst($venta->metodo_pago) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-bold text-gray-800">{{ $venta->total_formateado }}</span>
                        @if($venta->tiene_deuda)
                        <span class="block text-xs text-red-500">⚠️ Deuda</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ match($venta->estado) {
                                'completada' => 'bg-green-100 text-green-700',
                                'anulada'    => 'bg-red-100 text-red-600',
                                default      => 'bg-amber-100 text-amber-700'
                            } }}">
                            {{ ucfirst($venta->estado) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            {{-- Ver detalle (modal) --}}
                            <button @click="abrirDetalle({{ $venta->id }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-capy-500
                                           hover:bg-capy-50 transition-colors text-sm"
                                    title="Ver detalle">
                                👁️
                            </button>

                            {{-- Exportar PDF --}}
                            <a href="{{ route('admin.sales.pdf', $venta) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-red-500
                                      hover:bg-red-50 transition-colors text-sm"
                               title="Exportar PDF">
                                📄
                            </a>

                            {{-- Anular --}}
                            @if($venta->estado === 'completada')
                            <button @click="anular({{ $venta->id }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-500
                                           hover:bg-red-50 transition-colors text-sm"
                                    title="Anular venta">
                                ❌
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <span class="text-5xl block mb-3">📋</span>
                        <p class="text-gray-400">No hay ventas para mostrar</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal detalle de venta --}}
    <div x-show="modalAbierto" x-cloak x-transition
         @click.self="cerrarModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden fade-in"
             @click.stop>

            {{-- Header modal --}}
            <div class="bg-capy-600 px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-white/70">Detalle de venta</p>
                    <p class="text-xl font-bold text-white font-mono"
                       x-text="venta?.numero_factura"></p>
                </div>
                <button @click="cerrarModal()"
                        class="text-white/70 hover:text-white text-xl">✕</button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Loading --}}
                <div x-show="cargando" class="flex items-center justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-capy-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <template x-if="!cargando && venta">
                    <div class="space-y-4">
                        {{-- Info general --}}
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="bg-warm-50 rounded-xl p-3">
                                <p class="text-xs text-gray-400 mb-1">Cliente</p>
                                <p class="font-semibold text-gray-800" x-text="venta.cliente"></p>
                            </div>
                            <div class="bg-warm-50 rounded-xl p-3">
                                <p class="text-xs text-gray-400 mb-1">Fecha y hora</p>
                                <p class="font-semibold text-gray-800" x-text="venta.fecha"></p>
                            </div>
                            <div class="bg-warm-50 rounded-xl p-3">
                                <p class="text-xs text-gray-400 mb-1">Método de pago</p>
                                <p class="font-semibold text-gray-800 capitalize" x-text="venta.metodo_pago"></p>
                            </div>
                            <div class="bg-warm-50 rounded-xl p-3">
                                <p class="text-xs text-gray-400 mb-1">Estado</p>
                                <p class="font-semibold capitalize" x-text="venta.estado"
                                   :class="venta.estado==='completada'?'text-green-600':'text-red-500'"></p>
                            </div>
                        </div>

                        {{-- Ítems --}}
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Productos</p>
                            <div class="space-y-2">
                                <template x-for="item in venta.items" :key="item.nombre">
                                    <div class="flex items-center justify-between text-sm
                                                bg-warm-50 rounded-xl px-3 py-2">
                                        <div>
                                            <span class="font-medium text-gray-800" x-text="item.nombre"></span>
                                            <span class="text-gray-400 text-xs ml-1" x-text="'· '+item.tamano"></span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="text-gray-500 text-xs"
                                                  x-text="item.cantidad+'× $'+Number(item.precio_unitario).toLocaleString('es-CO')"></span>
                                            <span class="block font-bold text-gray-800"
                                                  x-text="'$'+Number(item.subtotal).toLocaleString('es-CO')"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Totales --}}
                        <div class="bg-capy-50 rounded-xl p-3 space-y-1.5">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Subtotal</span>
                                <span x-text="'$'+Number(venta.subtotal).toLocaleString('es-CO')"></span>
                            </div>
                            <template x-if="venta.descuento > 0">
                                <div class="flex justify-between text-sm text-red-400">
                                    <span>Descuento</span>
                                    <span x-text="'− $'+Number(venta.descuento).toLocaleString('es-CO')"></span>
                                </div>
                            </template>
                            <div class="flex justify-between text-base font-bold text-gray-900
                                        border-t border-capy-200 pt-2">
                                <span>Total</span>
                                <span class="text-capy-600" x-text="venta.total_formateado"></span>
                            </div>
                        </div>

                        {{-- Deuda badge --}}
                        <div x-show="venta.tiene_deuda"
                             class="flex items-center gap-2 bg-red-50 border border-red-200
                                    text-red-700 px-3 py-2 rounded-xl text-sm">
                            ⚠️ Esta venta tiene deuda pendiente
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer modal --}}
            <div class="px-6 pb-6 flex gap-3">
                <a :href="venta ? `/admin/sales/${venta.id}/pdf` : '#'"
                   class="flex-1 text-center py-2.5 rounded-xl bg-red-50 text-red-600
                          border border-red-200 hover:bg-red-100 font-medium text-sm transition-colors">
                    📄 Exportar PDF
                </a>
                <button @click="cerrarModal()"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600
                               hover:bg-gray-50 font-medium text-sm transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">{{ $ventas->links() }}</div>
@endsection

@push('scripts')
<script>
function salesTable() {
    return {
        modalAbierto: false,
        cargando: false,
        venta: null,

        async abrirDetalle(id) {
            this.modalAbierto = true;
            this.cargando = true;
            this.venta = null;
            const res = await fetch(`/admin/sales/${id}/detalle`);
            this.venta = await res.json();
            this.cargando = false;
        },

        cerrarModal() {
            this.modalAbierto = false;
            this.venta = null;
        },

        async anular(id) {
            if (!confirm('¿Estás seguro de que deseas anular esta venta?')) return;
            const motivo = prompt('Motivo de anulación (opcional):') ?? '';
            const res = await fetch(`/admin/sales/${id}/anular`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ motivo }),
            });
            const data = await res.json();
            if (data.success) window.location.reload();
            else alert('Error al anular: ' + data.message);
        },
    };
}
</script>
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush
