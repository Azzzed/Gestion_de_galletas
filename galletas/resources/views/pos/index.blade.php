@extends('layouts.app')
@section('title','POS — Capy Crunch')
@section('main-class','h-[calc(100vh-3.5rem)] overflow-hidden')

@section('content')
<div class="flex h-full" x-data="posApp()">

    {{-- ── CATÁLOGO ─────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden" style="background:#fdfaf4">

        {{-- Barra superior --}}
        <div class="px-4 py-3 bg-white border-b border-cream-200 shadow-sm flex gap-2">
            <div class="relative flex-1">
                <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">search</span>
                <input x-model="busqueda" @input="filtrar()"
                       placeholder="Buscar galleta o relleno…"
                       class="field pl-9 text-sm">
            </div>
            <select x-model="filtroTamano" @change="filtrar()" class="field select-field w-auto text-sm min-w-[130px]">
                <option value="">Todos los tamaños</option>
                <option value="pequeña">Pequeña</option>
                <option value="mediana">Mediana</option>
                <option value="grande">Grande</option>
            </select>
        </div>

        {{-- Grid --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                <template x-for="g in galletasFiltradas" :key="g.id">
                    <button @click="agregar(g)"
                            class="bg-white rounded-2xl p-3 text-left border border-cream-200
                                   hover:border-brand-400 hover:shadow-lg active:scale-95
                                   transition-all duration-200 group shadow-sm relative overflow-hidden">
                        {{-- Image --}}
                        <div class="w-full aspect-square rounded-xl overflow-hidden mb-2.5"
                             style="background:#fbf3e2">
                            <template x-if="g.imagen_path">
                                <img :src="'/storage/'+g.imagen_path" :alt="g.nombre"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </template>
                            <template x-if="!g.imagen_path">
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="icon icon-2xl text-brand-300">bakery_dining</span>
                                </div>
                            </template>
                        </div>
                        {{-- Info --}}
                        <p class="font-bold text-sm text-espresso-800 truncate" x-text="g.nombre"></p>
                        <div class="flex flex-wrap gap-1 my-1.5">
                            <template x-for="r in (g.rellenos||[])" :key="r">
                                <span class="text-[10px] bg-brand-50 text-brand-700 px-1.5 py-0.5 rounded-full font-medium border border-brand-100" x-text="r"></span>
                            </template>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-brand-600 font-bold text-sm" x-text="fmt(g.precio)"></span>
                            <span class="text-[10px] text-espresso-700/60 bg-cream-100 px-2 py-0.5 rounded-full font-medium capitalize" x-text="g.tamano"></span>
                        </div>
                        {{-- Hover accent --}}
                        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-brand-400 to-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>
                </template>

                <div x-show="galletasFiltradas.length===0"
                     class="col-span-full py-20 flex flex-col items-center text-espresso-700/40">
                    <span class="icon icon-2xl mb-3">search_off</span>
                    <p class="text-sm font-medium">Sin resultados para esta búsqueda</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── CARRITO ──────────────────────────────────────────── --}}
    <div class="w-80 xl:w-96 flex flex-col bg-white border-l border-cream-200 shadow-2xl">

        {{-- Header carrito --}}
        <div class="px-4 py-3.5 border-b border-cream-100">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="icon text-brand-500">shopping_cart</span>
                    <h2 class="font-display font-bold text-espresso-900 text-[15px]">Orden actual</h2>
                </div>
                <button @click="limpiar()" x-show="carrito.length>0"
                        class="flex items-center gap-1 text-xs text-red-400 hover:text-red-600 font-medium transition-colors">
                    <span class="icon icon-sm">delete_sweep</span>Limpiar
                </button>
            </div>

            {{-- Selector de cliente --}}
            <div class="relative">
                <div @click="showClientes=!showClientes"
                     class="flex items-center gap-2.5 p-2.5 rounded-xl cursor-pointer transition-all"
                     :class="showClientes ? 'bg-brand-50 border border-brand-300' : 'bg-cream-50 border border-cream-200 hover:border-brand-300'">
                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                        <span class="icon icon-sm text-brand-600">person</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-espresso-700/50 uppercase tracking-wider font-semibold">Cliente</p>
                        <p class="text-sm font-bold text-espresso-800 truncate" x-text="cliente.nombre"></p>
                    </div>
                    <span class="icon icon-sm text-espresso-700/40" x-text="showClientes?'expand_less':'expand_more'"></span>
                </div>

                <div x-show="showClientes" x-cloak x-transition
                     class="absolute top-full left-0 right-0 z-30 mt-1.5 bg-white rounded-2xl shadow-2xl border border-cream-200 overflow-hidden fade-in">
                    <div class="p-2.5 border-b border-cream-100">
                        <div class="relative">
                            <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">search</span>
                            <input x-model="queryCliente" @input="buscarClientes()"
                                   placeholder="Buscar cliente…"
                                   class="field pl-9 text-sm">
                        </div>
                    </div>
                    <div class="max-h-52 overflow-y-auto">
                        <button @click="setCliente({id:1,nombre:'Cliente de Mostrador'})"
                                class="w-full px-4 py-3 text-left hover:bg-cream-50 border-b border-cream-100 flex items-center gap-3">
                            <span class="icon icon-sm text-brand-500">storefront</span>
                            <div>
                                <p class="text-sm font-semibold text-espresso-800">Mostrador</p>
                                <p class="text-xs text-espresso-700/50">Venta rápida sin datos</p>
                            </div>
                        </button>
                        <template x-for="c in resultadosClientes" :key="c.id">
                            <button @click="setCliente(c)"
                                    class="w-full px-4 py-3 text-left hover:bg-cream-50 border-b border-cream-100 flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                                    <span class="icon icon-sm text-brand-600">person</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-espresso-800" x-text="c.nombre"></p>
                                    <p class="text-xs text-espresso-700/50" x-text="c.telefono||'Sin teléfono'"></p>
                                </div>
                            </button>
                        </template>
                        <div x-show="queryCliente&&resultadosClientes.length===0"
                             class="px-4 py-4 text-xs text-espresso-700/50 text-center">
                            Sin resultados —
                            <a href="/admin/customers/create" target="_blank" class="text-brand-600 underline font-medium">crear cliente</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="flex-1 overflow-y-auto px-3 py-2 space-y-1.5">
            <template x-for="(item,idx) in carrito" :key="item.id">
                <div class="slide-up flex items-center gap-2 bg-cream-50 rounded-xl px-3 py-2.5 border border-cream-200">
                    <span class="icon icon-sm text-brand-400 flex-shrink-0">bakery_dining</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-espresso-800 truncate" x-text="item.nombre"></p>
                        <p class="text-[11px] text-espresso-700/50 capitalize" x-text="item.tamano"></p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button @click="dec(idx)"
                                class="w-6 h-6 rounded-lg bg-white border border-cream-200 hover:bg-red-50 hover:text-red-500 flex items-center justify-center font-bold text-sm transition-colors text-espresso-700">−</button>
                        <span class="w-6 text-center text-sm font-bold text-espresso-900" x-text="item.cantidad"></span>
                        <button @click="inc(idx)"
                                class="w-6 h-6 rounded-lg bg-brand-500 hover:bg-brand-600 text-white flex items-center justify-center font-bold text-sm transition-colors">+</button>
                    </div>
                    <span class="text-sm font-bold text-brand-600 w-16 text-right flex-shrink-0" x-text="fmt(item.precio*item.cantidad)"></span>
                </div>
            </template>

            <div x-show="carrito.length===0"
                 class="flex flex-col items-center justify-center h-full py-14 text-espresso-700/30">
                <div class="w-16 h-16 rounded-2xl bg-cream-100 flex items-center justify-center mb-3">
                    <span class="icon icon-2xl">shopping_cart</span>
                </div>
                <p class="text-sm font-medium text-center">Agrega galletas<br>del catálogo</p>
            </div>
        </div>

        {{-- Footer: totales + pago --}}
        <div x-show="carrito.length>0" x-cloak class="border-t border-cream-200 px-4 py-4 space-y-3">

            {{-- Descuento --}}
            <div class="flex items-center gap-2">
                <span class="icon icon-sm text-brand-500">local_offer</span>
                <label class="text-xs text-espresso-700/60 font-semibold">Descuento %</label>
                <input type="number" x-model="descuento" min="0" max="100"
                       class="field text-sm text-center w-16 py-1.5">
                <span class="text-xs text-red-500 font-semibold ml-auto" x-show="descuento>0">
                    − <span x-text="fmt(descuentoValor)"></span>
                </span>
            </div>

            {{-- Total box --}}
            <div class="rounded-2xl overflow-hidden" style="background: linear-gradient(135deg,#1a0a00,#3b1f0e)">
                <div class="px-4 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-white/50 text-xs uppercase tracking-wide font-semibold">Total</p>
                        <p class="text-white font-display font-bold text-2xl" x-text="fmt(total)"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-white/50 text-xs">Subtotal <span x-text="fmt(subtotal)"></span></p>
                        <p class="text-white/50 text-xs"><span x-text="totalItems"></span> items</p>
                    </div>
                </div>
            </div>

            {{-- Método de pago --}}
            <div class="grid grid-cols-3 gap-1.5">
                <template x-for="m in metodos" :key="m.id">
                    <button @click="metodoPago=m.id"
                            :class="metodoPago===m.id
                                ? 'bg-brand-500 text-white border-brand-500 shadow-md'
                                : 'bg-white text-espresso-700 border-cream-200 hover:border-brand-300'"
                            class="flex flex-col items-center py-2.5 rounded-xl border text-[11px] font-bold transition-all gap-1">
                        <span class="icon icon-sm" x-text="m.icon"></span>
                        <span x-text="m.label"></span>
                    </button>
                </template>
            </div>

            {{-- Deuda toggle --}}
            <div x-show="cliente.id!==1" x-cloak
                 @click="tieneDeuda=!tieneDeuda"
                 :class="tieneDeuda ? 'border-red-300 bg-red-50' : 'border-cream-200 bg-cream-50'"
                 class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border cursor-pointer transition-all">
                <div :class="tieneDeuda ? 'bg-red-500' : 'bg-cream-300'"
                     class="w-9 h-5 rounded-full relative transition-colors flex-shrink-0">
                    <span :class="tieneDeuda ? 'translate-x-4' : 'translate-x-0.5'"
                          class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                </div>
                <div>
                    <p class="text-sm font-bold" :class="tieneDeuda ? 'text-red-700' : 'text-espresso-700'">Registrar como deuda</p>
                    <p class="text-[10px]" :class="tieneDeuda ? 'text-red-500' : 'text-espresso-700/50'">El cliente pagará después</p>
                </div>
                <span class="icon icon-sm ml-auto" :class="tieneDeuda ? 'text-red-500' : 'text-espresso-700/30'">credit_card_off</span>
            </div>

            {{-- Botón cobrar --}}
            <button @click="cobrar()"
                    :disabled="carrito.length===0||!metodoPago||procesando"
                    class="w-full btn-primary justify-center py-3.5 text-base rounded-2xl
                           disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none">
                <span x-show="!procesando" class="flex items-center gap-2">
                    <span class="icon">payments</span>
                    Cobrar <span x-text="fmt(total)"></span>
                </span>
                <span x-show="procesando" x-cloak class="flex items-center gap-2">
                    <span class="icon" style="animation:spin 1s linear infinite">progress_activity</span>
                    Procesando…
                </span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .select-field { appearance: none; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='%239a3a10'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px;
    }
</style>
@endpush

@push('scripts')
<script>
function posApp() {
    return {
        todasLasGalletas: @json($galletas->flatten()->values()),
        galletasFiltradas: [],
        busqueda: '', filtroTamano: '',
        carrito: [],
        cliente: { id: 1, nombre: 'Cliente de Mostrador' },
        showClientes: false, queryCliente: '', resultadosClientes: [],
        descuento: 0, metodoPago: 'efectivo', tieneDeuda: false, procesando: false,
        metodos: [
            { id:'efectivo',      label:'Efectivo',  icon:'payments' },
            { id:'transferencia', label:'Nequi/PSE', icon:'phone_iphone' },
            { id:'tarjeta',       label:'Tarjeta',   icon:'credit_card' },
        ],
        init() { this.galletasFiltradas = this.todasLasGalletas; },
        filtrar() {
            const t = this.busqueda.toLowerCase();
            this.galletasFiltradas = this.todasLasGalletas.filter(g => {
                const ok = !this.filtroTamano || g.tamano === this.filtroTamano;
                const okB = !t || g.nombre.toLowerCase().includes(t) || (g.rellenos||[]).some(r=>r.toLowerCase().includes(t));
                return ok && okB;
            });
        },
        agregar(g) {
            const i = this.carrito.findIndex(x=>x.id===g.id);
            i>=0 ? this.carrito[i].cantidad++ : this.carrito.push({...g,cantidad:1});
        },
        inc(i) { this.carrito[i].cantidad++; },
        dec(i) { this.carrito[i].cantidad>1 ? this.carrito[i].cantidad-- : this.carrito.splice(i,1); },
        limpiar() { this.carrito=[]; this.descuento=0; this.metodoPago='efectivo'; this.tieneDeuda=false; },
        get totalItems()    { return this.carrito.reduce((s,i)=>s+i.cantidad,0); },
        get subtotal()      { return this.carrito.reduce((s,i)=>s+i.precio*i.cantidad,0); },
        get descuentoValor(){ return Math.round(this.subtotal*(this.descuento/100)); },
        get total()         { return this.subtotal - this.descuentoValor; },
        fmt(v) { return '$'+Math.round(v).toLocaleString('es-CO'); },
        async buscarClientes() {
            if (!this.queryCliente) { this.resultadosClientes=[]; return; }
            const r = await fetch(`/pos/buscar-clientes?q=${encodeURIComponent(this.queryCliente)}`);
            this.resultadosClientes = await r.json();
        },
        setCliente(c) { this.cliente=c; this.showClientes=false; this.queryCliente=''; this.resultadosClientes=[]; if(c.id===1)this.tieneDeuda=false; },
        async cobrar() {
            if (this.procesando) return;
            this.procesando = true;
            try {
                const res = await fetch('/pos/venta', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({
                        customer_id: this.cliente.id,
                        items: this.carrito.map(i=>({ cookie_id:i.id, cantidad:i.cantidad, precio_unitario:i.precio })),
                        descuento_porcentaje: this.descuento,
                        metodo_pago: this.metodoPago,
                        metodos_pago: [{ metodo:this.metodoPago, monto:this.total }],
                        tiene_deuda: this.tieneDeuda,
                    }),
                });
                const data = await res.json();
                if (data.success) { window.open(`/pos/comprobante/${data.sale_id}`,'_blank'); this.limpiar(); this.cliente={id:1,nombre:'Cliente de Mostrador'}; }
                else alert('Error: '+data.message);
            } catch(e) { alert('Error de conexión'); }
            finally { this.procesando=false; }
        },
    };
}
</script>
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush