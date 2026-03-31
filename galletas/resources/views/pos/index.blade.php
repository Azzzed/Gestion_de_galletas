@extends('layouts.app')
@section('title','POS')
@section('main-class','h-[calc(100vh-3.5rem)] overflow-hidden')

@section('content')
<div class="flex h-full" x-data="posApp()">

    {{-- ── Catálogo ────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden bg-warm-50">

        {{-- Barra búsqueda --}}
        <div class="p-3 bg-white border-b border-warm-200 shadow-sm">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
                    <input x-model="busqueda" @input="filtrar()"
                           placeholder="Buscar galleta o relleno…"
                           class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-gray-200
                                  focus:outline-none focus:ring-2 focus:ring-capy-400 bg-warm-50">
                </div>
                <select x-model="filtroTamano" @change="filtrar()"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-warm-50
                               focus:outline-none focus:ring-2 focus:ring-capy-400">
                    <option value="">Todos</option>
                    <option value="pequeña">🍪 Pequeña</option>
                    <option value="mediana">🍪🍪 Mediana</option>
                    <option value="grande">🍪🍪🍪 Grande</option>
                </select>
            </div>
        </div>

        {{-- Grid galletas --}}
        <div class="flex-1 overflow-y-auto p-3">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">

                <template x-for="g in galletasFiltradas" :key="g.id">
                    <button @click="agregar(g)"
                            class="btn-ripple bg-white rounded-2xl p-3 text-left border-2
                                   border-transparent hover:border-capy-300 active:scale-95
                                   transition-all shadow-sm group">
                        <div class="w-full aspect-square rounded-xl bg-warm-100 flex items-center
                                    justify-center mb-2 overflow-hidden group-hover:bg-warm-200">
                            <template x-if="g.imagen_path">
                                <img :src="'/storage/'+g.imagen_path" :alt="g.nombre"
                                     class="w-full h-full object-cover">
                            </template>
                            <template x-if="!g.imagen_path">
                                <span class="text-4xl">🍪</span>
                            </template>
                        </div>
                        <p class="font-semibold text-sm text-gray-800 truncate" x-text="g.nombre"></p>
                        <div class="flex flex-wrap gap-1 my-1">
                            <template x-for="r in (g.rellenos||[])" :key="r">
                                <span class="text-[10px] bg-capy-100 text-capy-700 px-1.5 py-0.5
                                             rounded-full font-medium" x-text="r"></span>
                            </template>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-capy-600 font-bold text-sm" x-text="fmt(g.precio)"></span>
                            <span class="text-xs text-gray-400 bg-warm-100 px-1.5 py-0.5 rounded-full"
                                  x-text="g.tamano"></span>
                        </div>
                    </button>
                </template>

                <div x-show="galletasFiltradas.length===0"
                     class="col-span-full py-16 flex flex-col items-center text-gray-300">
                    <span class="text-5xl mb-2">🔍</span>
                    <p class="text-sm">Sin resultados</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Carrito ─────────────────────────────────────────────── --}}
    <div class="w-80 xl:w-96 flex flex-col bg-white border-l border-warm-200 shadow-xl">

        {{-- Header + selector de cliente --}}
        <div class="p-4 border-b border-warm-100">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-800">Orden actual</h2>
                <button @click="limpiar()" x-show="carrito.length>0"
                        class="text-xs text-red-400 hover:text-red-600">Limpiar ✕</button>
            </div>

            {{-- Buscador de cliente --}}
            <div class="relative">
                <div @click="showClientes=!showClientes"
                     class="flex items-center gap-2 p-2.5 rounded-xl bg-warm-50 border
                            border-warm-200 cursor-pointer hover:border-capy-300">
                    <span class="text-lg">👤</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Cliente</p>
                        <p class="text-sm font-semibold text-gray-800 truncate"
                           x-text="cliente.nombre"></p>
                    </div>
                    <span class="text-gray-400 text-xs" x-text="showClientes?'▲':'▼'"></span>
                </div>

                <div x-show="showClientes" x-cloak
                     class="absolute top-full left-0 right-0 z-30 mt-1 bg-white
                            rounded-2xl shadow-xl border border-warm-200 overflow-hidden fade-in">
                    <div class="p-3 border-b border-warm-100">
                        <input x-model="queryCliente" @input="buscarClientes()"
                               placeholder="Buscar por nombre o teléfono…"
                               class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-capy-400">
                    </div>
                    <div class="max-h-52 overflow-y-auto">
                        {{-- Mostrador siempre primero --}}
                        <button @click="setCliente({id:1,nombre:'Cliente de Mostrador'})"
                                class="w-full px-4 py-3 text-left hover:bg-warm-50 border-b border-warm-100">
                            <p class="text-sm font-medium">🏪 Cliente de Mostrador</p>
                            <p class="text-xs text-gray-400">Venta rápida sin datos</p>
                        </button>

                        <template x-for="c in resultadosClientes" :key="c.id">
                            <button @click="setCliente(c)"
                                    class="w-full px-4 py-3 text-left hover:bg-warm-50 border-b border-warm-100">
                                <p class="text-sm font-medium" x-text="c.nombre"></p>
                                <p class="text-xs text-gray-400" x-text="c.telefono||'Sin teléfono'"></p>
                            </button>
                        </template>

                        <div x-show="queryCliente&&resultadosClientes.length===0"
                             class="px-4 py-3 text-xs text-gray-400 text-center">
                            Sin resultados —
                            <a href="/admin/customers/create" target="_blank"
                               class="text-capy-600 underline">crear cliente</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ítems --}}
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="(item,idx) in carrito" :key="item.id">
                <div class="slide-up flex items-center gap-2 bg-warm-50 rounded-xl p-2.5">
                    <span class="text-xl shrink-0">🍪</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.nombre"></p>
                        <p class="text-xs text-gray-400" x-text="item.tamano"></p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="dec(idx)"
                                class="w-7 h-7 rounded-full bg-white border border-gray-200
                                       hover:bg-red-50 hover:text-red-500 flex items-center
                                       justify-center font-bold text-sm">−</button>
                        <span class="w-5 text-center text-sm font-bold" x-text="item.cantidad"></span>
                        <button @click="inc(idx)"
                                class="w-7 h-7 rounded-full bg-capy-500 text-white
                                       hover:bg-capy-600 flex items-center justify-center font-bold text-sm">+</button>
                    </div>
                    <span class="text-sm font-bold text-capy-600 w-16 text-right shrink-0"
                          x-text="fmt(item.precio*item.cantidad)"></span>
                </div>
            </template>

            <div x-show="carrito.length===0"
                 class="flex flex-col items-center justify-center h-full py-12 text-gray-300">
                <span class="text-6xl mb-3">🐾</span>
                <p class="text-sm text-center">Agrega galletas<br>al pedido</p>
            </div>
        </div>

        {{-- Totales --}}
        <div x-show="carrito.length>0" x-cloak class="p-4 border-t border-warm-200 space-y-3">

            {{-- Descuento --}}
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-20 shrink-0">Descuento %</label>
                <input type="number" x-model="descuento" min="0" max="100"
                       class="w-16 px-2 py-1.5 text-sm text-center border border-gray-200
                              rounded-lg focus:outline-none focus:ring-2 focus:ring-capy-400">
                <span class="text-xs text-red-400 ml-auto" x-show="descuento>0">
                    − <span x-text="fmt(descuentoValor)"></span>
                </span>
            </div>

            {{-- Totales --}}
            <div class="bg-warm-50 rounded-xl p-3 space-y-1.5">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal (<span x-text="totalItems"></span> items)</span>
                    <span x-text="fmt(subtotal)"></span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-warm-200 pt-2">
                    <span>Total</span>
                    <span class="text-capy-600" x-text="fmt(total)"></span>
                </div>
            </div>

            {{-- Método de pago --}}
            <div class="grid grid-cols-3 gap-1.5">
                <template x-for="m in metodos" :key="m.id">
                    <button @click="metodoPago=m.id"
                            :class="metodoPago===m.id
                                ?'bg-capy-500 text-white border-capy-500'
                                :'bg-white text-gray-600 border-gray-200 hover:border-capy-300'"
                            class="flex flex-col items-center py-2 px-1 rounded-xl border
                                   text-xs font-medium transition-all">
                        <span class="text-base mb-0.5" x-text="m.icon"></span>
                        <span x-text="m.label"></span>
                    </button>
                </template>
            </div>

            {{-- ¿Registrar deuda? (solo si cliente no es mostrador) --}}
            <div x-show="cliente.id!==1" x-cloak
                 class="flex items-center gap-2 bg-red-50 rounded-xl px-3 py-2">
                <input type="checkbox" id="deuda" x-model="tieneDeuda"
                       class="w-4 h-4 accent-red-500 cursor-pointer">
                <label for="deuda" class="text-sm text-red-700 font-medium cursor-pointer">
                    💳 Registrar como deuda
                </label>
            </div>

            {{-- Cobrar --}}
            <button @click="cobrar()"
                    :disabled="carrito.length===0||!metodoPago||procesando"
                    class="w-full py-3.5 bg-capy-600 hover:bg-capy-700 text-white font-bold
                           rounded-2xl shadow-lg active:scale-95 transition-all
                           disabled:opacity-50 disabled:cursor-not-allowed btn-ripple">
                <span x-show="!procesando">💰 Cobrar <span x-text="fmt(total)"></span></span>
                <span x-show="procesando" x-cloak>⏳ Procesando…</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function posApp() {
    return {
        todasLasGalletas: @json($galletas->flatten()->values()),
        galletasFiltradas: [],
        busqueda: '',
        filtroTamano: '',

        carrito: [],
        cliente: { id: 1, nombre: 'Cliente de Mostrador' },
        showClientes: false,
        queryCliente: '',
        resultadosClientes: [],

        descuento: 0,
        metodoPago: 'efectivo',
        tieneDeuda: false,
        procesando: false,

        metodos: [
            { id:'efectivo',      label:'Efectivo',     icon:'💵' },
            { id:'transferencia', label:'Nequi/PSE',    icon:'📱' },
            { id:'tarjeta',       label:'Tarjeta',      icon:'💳' },
        ],

        // Init
        init() { this.galletasFiltradas = this.todasLasGalletas; },

        // Filtros
        filtrar() {
            const t = this.busqueda.toLowerCase();
            this.galletasFiltradas = this.todasLasGalletas.filter(g => {
                const ok = !this.filtroTamano || g.tamano === this.filtroTamano;
                const okB = !t || g.nombre.toLowerCase().includes(t)
                    || (g.rellenos||[]).some(r => r.toLowerCase().includes(t));
                return ok && okB;
            });
        },

        // Carrito
        agregar(g) {
            const i = this.carrito.findIndex(x => x.id === g.id);
            i >= 0 ? this.carrito[i].cantidad++ : this.carrito.push({...g, cantidad:1});
        },
        inc(i) { this.carrito[i].cantidad++; },
        dec(i) { this.carrito[i].cantidad > 1 ? this.carrito[i].cantidad-- : this.carrito.splice(i,1); },
        limpiar() { this.carrito=[]; this.descuento=0; this.metodoPago='efectivo'; this.tieneDeuda=false; },

        get totalItems()    { return this.carrito.reduce((s,i)=>s+i.cantidad,0); },
        get subtotal()      { return this.carrito.reduce((s,i)=>s+i.precio*i.cantidad,0); },
        get descuentoValor(){ return Math.round(this.subtotal*(this.descuento/100)); },
        get total()         { return this.subtotal - this.descuentoValor; },
        fmt(v)              { return '$'+Math.round(v).toLocaleString('es-CO'); },

        // Clientes
        async buscarClientes() {
            if (!this.queryCliente) { this.resultadosClientes=[]; return; }
            const r = await fetch(`/pos/buscar-clientes?q=${encodeURIComponent(this.queryCliente)}`);
            this.resultadosClientes = await r.json();
        },
        setCliente(c) {
            this.cliente = c;
            this.showClientes = false;
            this.queryCliente = '';
            this.resultadosClientes = [];
            if (c.id === 1) this.tieneDeuda = false;
        },

        // Cobrar
        async cobrar() {
            if (this.procesando) return;
            this.procesando = true;
            try {
                const res = await fetch('/pos/venta', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        customer_id:          this.cliente.id,
                        items:                this.carrito.map(i=>({
                            cookie_id:       i.id,
                            cantidad:        i.cantidad,
                            precio_unitario: i.precio,
                        })),
                        descuento_porcentaje: this.descuento,
                        metodo_pago:          this.metodoPago,
                        metodos_pago:         [{ metodo: this.metodoPago, monto: this.total }],
                        tiene_deuda:          this.tieneDeuda,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    window.open(`/pos/comprobante/${data.sale_id}`, '_blank');
                    this.limpiar();
                    this.cliente = { id:1, nombre:'Cliente de Mostrador' };
                } else {
                    alert('Error: ' + data.message);
                }
            } catch(e) { alert('Error de conexión'); }
            finally { this.procesando = false; }
        },
    };
}
</script>
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush
