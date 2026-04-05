@extends('layouts.app')
@section('title','POS — Capy Crunch')
@section('main-class','h-[calc(100vh-3.5rem)] overflow-hidden')

@section('content')
<div class="flex h-full" x-data="posApp()" @click.away="showClientes=false">

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
                <div @click.stop="showClientes=!showClientes"
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

            {{-- ── CÓDIGO PROMOCIONAL ────────────────────────── --}}
            <div x-show="carrito.length>0" x-transition>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">local_offer</span>
                        <input type="text" x-model="codigoPromo"
                               @input="codigoPromo=$event.target.value.toUpperCase(); promoResult=null"
                               @keydown.enter="validarCodigo()"
                               placeholder="Código promo…"
                               class="field pl-9 text-sm uppercase"
                               style="text-transform:uppercase">
                    </div>
                    <button @click="validarCodigo()"
                            :disabled="!codigoPromo"
                            class="btn-ghost py-2 px-3 text-xs disabled:opacity-40 disabled:cursor-not-allowed">
                        Aplicar
                    </button>
                    <button x-show="promoResult?.valid" @click="limpiarPromo()"
                            class="px-2 py-2 text-red-400 hover:text-red-600 transition-colors">
                        <span class="icon icon-sm">close</span>
                    </button>
                </div>
                <div x-show="promoResult" x-transition
                     class="mt-1.5 px-3 py-2 rounded-lg text-xs font-semibold"
                     :class="promoResult?.valid
                        ? 'bg-green-50 border border-green-200 text-green-700'
                        : 'bg-red-50   border border-red-200   text-red-700'"
                     x-text="promoResult?.message">
                </div>
            </div>

            {{-- Descuento manual % --}}
            <div class="flex items-center gap-2">
                <span class="icon icon-sm text-brand-500">percent</span>
                <label class="text-xs text-espresso-700/60 font-semibold">Descuento %</label>
                <input type="number" x-model="descuento" min="0" max="100"
                       class="field text-sm text-center w-16 py-1.5">
                <span class="text-xs text-red-500 font-semibold ml-auto" x-show="descuentoValor>0">
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
                        <p class="text-white/50 text-xs" x-show="descuentoValor>0">
                            Dcto <span x-text="fmt(descuentoValor)"></span>
                        </p>
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

            {{-- ── BOTÓN DOMICILIO ───────────────────────────── --}}
            <button @click="showDelivery=true"
                    class="w-full py-2.5 rounded-xl border-2 border-blue-300 text-blue-700 font-bold text-sm
                           hover:bg-blue-50 active:scale-95 transition-all flex items-center justify-center gap-2">
                <span class="icon icon-sm">local_shipping</span>
                Registrar como Domicilio
            </button>

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

    {{-- ══════════════════════════════════════════════════════════
         MODAL DOMICILIO
    ══════════════════════════════════════════════════════════ --}}
    <div x-show="showDelivery" x-cloak x-transition
         @click.self="showDelivery=false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-900/60 backdrop-blur-sm">

        <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

            {{-- Header --}}
            <div class="px-6 py-4 flex items-center justify-between flex-shrink-0"
                 style="background:linear-gradient(135deg,#1a0a00,#0891B2)">
                <div class="flex items-center gap-3">
                    <span class="icon text-white">local_shipping</span>
                    <div>
                        <p class="font-display font-bold text-white">Nuevo Pedido a Domicilio</p>
                        <p class="text-white/60 text-xs"
                           x-text="totalItems + ' galleta(s) · ' + fmt(totalConEnvio)"></p>
                    </div>
                </div>
                <button @click="showDelivery=false"
                        class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition-colors">
                    <span class="icon icon-sm">close</span>
                </button>
            </div>

            {{-- Cuerpo scrolleable --}}
            <div class="overflow-y-auto flex-1 p-6 space-y-5">

                {{-- ── SECCIÓN CLIENTE ─────────────────────────── --}}
                {{-- Toggle: cliente registrado vs mostrador --}}
                <div class="grid grid-cols-2 gap-2">
                    <button type="button"
                            @click="deliv.modo_cliente = 'registrado'; resetDelivCustomer()"
                            :class="deliv.modo_cliente === 'registrado'
                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                : 'border-cream-200 bg-white text-espresso-600 hover:border-blue-300'"
                            class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 font-bold text-sm transition-all">
                        <span class="icon icon-sm">person_search</span>
                        Cliente registrado
                    </button>
                    <button type="button"
                            @click="deliv.modo_cliente = 'mostrador'; resetDelivCustomer()"
                            :class="deliv.modo_cliente === 'mostrador'
                                ? 'border-brand-500 bg-brand-50 text-brand-700'
                                : 'border-cream-200 bg-white text-espresso-600 hover:border-brand-300'"
                            class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 font-bold text-sm transition-all">
                        <span class="icon icon-sm">storefront</span>
                        Sin registro
                    </button>
                </div>

                {{-- MODO: Cliente registrado --}}
                <div x-show="deliv.modo_cliente === 'registrado'" x-transition>

                    {{-- Buscador de cliente --}}
                    <div class="relative">
                        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                            Buscar cliente <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-blue-400">search</span>
                            <input type="text"
                                   x-model="deliv.queryCliente"
                                   @input.debounce.300ms="buscarClientesDomicilio()"
                                   @focus="deliv.showDropdown = true"
                                   placeholder="Nombre o teléfono del cliente…"
                                   class="field pl-9 text-sm"
                                   :class="deliv.clienteSeleccionado ? 'border-blue-400 bg-blue-50' : ''">
                            <button x-show="deliv.clienteSeleccionado"
                                    @click="resetDelivCustomer()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-red-400 hover:text-red-600">
                                <span class="icon icon-sm">close</span>
                            </button>
                        </div>

                        {{-- Dropdown resultados --}}
                        <div x-show="deliv.showDropdown && deliv.resultadosCliente.length > 0 && !deliv.clienteSeleccionado"
                             @click.away="deliv.showDropdown = false"
                             class="absolute top-full left-0 right-0 z-40 mt-1 bg-white rounded-2xl shadow-2xl border border-cream-200 overflow-hidden max-h-52 overflow-y-auto">
                            <template x-for="c in deliv.resultadosCliente" :key="c.id">
                                <button type="button"
                                        @click="seleccionarClienteDomicilio(c)"
                                        class="w-full px-4 py-3 text-left hover:bg-blue-50 border-b border-cream-100 flex items-center gap-3 transition-colors">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <span class="icon icon-sm text-blue-600">person</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-espresso-900 truncate" x-text="c.nombre"></p>
                                        <p class="text-xs text-espresso-700/50" x-text="c.telefono || 'Sin teléfono'"></p>
                                    </div>
                                    <span x-show="c.direcciones && c.direcciones.length > 0"
                                          class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold flex-shrink-0"
                                          x-text="c.direcciones.length + ' dir.'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Cliente seleccionado: info + sus direcciones --}}
                    <div x-show="deliv.clienteSeleccionado" x-transition class="space-y-3">

                        {{-- Chip del cliente --}}
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 border border-blue-200">
                            <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                                <span class="icon icon-sm text-white">person</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-blue-900 text-sm" x-text="deliv.clienteSeleccionado?.nombre"></p>
                                <p class="text-xs text-blue-600" x-text="deliv.clienteSeleccionado?.telefono || 'Sin teléfono'"></p>
                            </div>
                            <span class="text-[10px] bg-blue-500 text-white px-2 py-0.5 rounded-full font-bold">Registrado</span>
                        </div>

                        {{-- Direcciones guardadas del cliente --}}
                        <div x-show="deliv.clienteSeleccionado?.direcciones?.length > 0">
                            <p class="text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
                                Direcciones guardadas
                            </p>
                            <div class="space-y-1.5">
                                <template x-for="(dir, idx) in (deliv.clienteSeleccionado?.direcciones ?? [])" :key="idx">
                                    <button type="button"
                                            @click="seleccionarDireccion(dir)"
                                            :class="deliv.delivery_address === dir.direccion
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-cream-200 bg-white hover:border-blue-300'"
                                            class="w-full flex items-center gap-3 p-3 rounded-xl border-2 text-left transition-all">
                                        <span class="icon icon-sm text-blue-500 flex-shrink-0">location_on</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-espresso-900 truncate" x-text="dir.direccion"></p>
                                            <p class="text-xs text-espresso-700/50" x-text="dir.barrio || ''"></p>
                                        </div>
                                        <span x-show="deliv.delivery_address === dir.direccion"
                                              class="icon icon-sm text-blue-500 flex-shrink-0">check_circle</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Nueva dirección para cliente registrado --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-espresso-700/60 uppercase tracking-wider">
                                    <span x-text="deliv.clienteSeleccionado?.direcciones?.length > 0 ? 'Otra dirección' : 'Dirección de envío'"></span>
                                    <span class="text-red-400 ml-1">*</span>
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="text" x-model="deliv.delivery_address"
                                       placeholder="Cra 15 #45-20 Apto 301"
                                       class="field text-sm col-span-2">
                                <input type="text" x-model="deliv.delivery_neighborhood"
                                       placeholder="Barrio"
                                       class="field text-sm">
                                {{-- Toggle guardar dirección --}}
                                <label class="flex items-center gap-2 px-3 py-2 rounded-xl border border-cream-200 bg-cream-50 cursor-pointer hover:border-blue-300 transition-all">
                                    <input type="checkbox" x-model="deliv.guardar_direccion" class="accent-blue-500 w-4 h-4">
                                    <span class="text-xs font-semibold text-espresso-700">Guardar en perfil</span>
                                    <span class="icon icon-sm text-blue-400">bookmark</span>
                                </label>
                            </div>
                        </div>

                    </div>{{-- fin cliente seleccionado --}}

                    {{-- Hint si no ha buscado --}}
                    <div x-show="!deliv.clienteSeleccionado && !deliv.queryCliente"
                         class="flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-50 border border-blue-100 text-xs text-blue-600">
                        <span class="icon icon-sm">info</span>
                        Escribe el nombre o teléfono del cliente para buscarlo.
                    </div>
                </div>

                {{-- MODO: Sin registro (mostrador) --}}
                <div x-show="deliv.modo_cliente === 'mostrador'" x-transition>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                                Nombre del cliente
                            </label>
                            <input type="text" x-model="deliv.customer_name"
                                   placeholder="Ej: María García"
                                   class="field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                                Teléfono de contacto
                            </label>
                            <input type="text" x-model="deliv.customer_phone"
                                   placeholder="Ej: 300 123 4567"
                                   class="field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                                Dirección de envío <span class="text-red-400">*</span>
                            </label>
                            <input type="text" x-model="deliv.delivery_address"
                                   placeholder="Cra 15 #45-20 Apto 301"
                                   class="field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                                Barrio
                            </label>
                            <input type="text" x-model="deliv.delivery_neighborhood"
                                   placeholder="Ej: El Prado"
                                   class="field text-sm">
                        </div>
                    </div>
                </div>

                {{-- Tipo de envío --}}
                <div>
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
                        Tipo de envío
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <label :class="deliv.delivery_cost_type==='additional'
                                    ? 'border-brand-500 bg-brand-50'
                                    : 'border-cream-200 bg-white hover:border-brand-300'"
                               class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all">
                            <input type="radio" x-model="deliv.delivery_cost_type" value="additional" class="sr-only">
                            <span class="icon text-brand-500">attach_money</span>
                            <span class="text-xs font-bold text-espresso-800 text-center">Cobrado al cliente</span>
                            <span class="text-[10px] text-espresso-700/50 text-center">Se suma al pedido</span>
                        </label>
                        <label :class="deliv.delivery_cost_type==='free'
                                    ? 'border-green-500 bg-green-50'
                                    : 'border-cream-200 bg-white hover:border-green-300'"
                               class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all">
                            <input type="radio" x-model="deliv.delivery_cost_type" value="free" class="sr-only">
                            <span class="icon text-green-500">local_shipping</span>
                            <span class="text-xs font-bold text-espresso-800 text-center">Gratis al cliente</span>
                            <span class="text-[10px] text-espresso-700/50 text-center">Lo asume el negocio</span>
                        </label>
                        <label :class="deliv.delivery_cost_type==='business'
                                    ? 'border-blue-500 bg-blue-50'
                                    : 'border-cream-200 bg-white hover:border-blue-300'"
                               class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all">
                            <input type="radio" x-model="deliv.delivery_cost_type" value="business" class="sr-only">
                            <span class="icon text-blue-500">storefront</span>
                            <span class="text-xs font-bold text-espresso-800 text-center">Mensajero propio</span>
                            <span class="text-[10px] text-espresso-700/50 text-center">El negocio paga</span>
                        </label>
                    </div>
                </div>

                {{-- Valor del envío --}}
                <div x-show="deliv.delivery_cost_type==='additional'" x-transition>
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                        Valor del envío ($)
                    </label>
                    <input type="number" x-model="deliv.delivery_cost" min="0" step="500"
                           placeholder="3000"
                           class="field text-sm">
                </div>

                {{-- Método de pago --}}
                <div>
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
                        Método de pago <span class="text-red-400">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label :class="deliv.payment_method==='cash_on_delivery'
                                    ? 'border-brand-500 bg-brand-50'
                                    : 'border-cream-200 bg-white hover:border-brand-300'"
                               class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all">
                            <input type="radio" x-model="deliv.payment_method" value="cash_on_delivery" class="sr-only">
                            <span class="icon text-brand-500">payments</span>
                            <div>
                                <p class="text-sm font-bold text-espresso-800">Paga al llegar</p>
                                <p class="text-[10px] text-espresso-700/50">Efectivo o transfer. en entrega</p>
                            </div>
                        </label>
                        <label :class="deliv.payment_method==='transfer'
                                    ? 'border-purple-500 bg-purple-50'
                                    : 'border-cream-200 bg-white hover:border-purple-300'"
                               class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all">
                            <input type="radio" x-model="deliv.payment_method" value="transfer" class="sr-only">
                            <span class="icon text-purple-500">phone_iphone</span>
                            <div>
                                <p class="text-sm font-bold text-espresso-800">Ya pagó (transfer.)</p>
                                <p class="text-[10px] text-espresso-700/50">Nequi, Daviplata, PSE</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Código promo para domicilio --}}
                <div>
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                        Código Promocional
                    </label>
                    <div class="flex gap-2">
                        <input type="text" x-model="deliv.promo_code"
                               @input="deliv.promo_code=$event.target.value.toUpperCase(); promoResultDeliv=null"
                               @keydown.enter="validatePromoForDelivery()"
                               placeholder="Ej: FREEENVIO"
                               class="field text-sm uppercase flex-1"
                               style="text-transform:uppercase">
                        <button @click="validatePromoForDelivery()"
                                :disabled="!deliv.promo_code"
                                class="btn-ghost py-2 px-4 text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                            Validar
                        </button>
                        <button x-show="promoResultDeliv?.valid" @click="promoResultDeliv=null; deliv.promo_code=''"
                                class="px-2 text-red-400 hover:text-red-600 transition-colors">
                            <span class="icon icon-sm">close</span>
                        </button>
                    </div>
                    <div x-show="promoResultDeliv" x-transition
                         class="mt-1.5 px-3 py-2 rounded-lg text-xs font-semibold"
                         :class="promoResultDeliv?.valid
                            ? 'bg-green-50 border border-green-200 text-green-700'
                            : 'bg-red-50   border border-red-200   text-red-700'"
                         x-text="promoResultDeliv?.message">
                    </div>
                </div>

                {{-- Notas --}}
                <div>
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                        Notas para el pedido
                    </label>
                    <textarea x-model="deliv.notes" rows="2"
                              placeholder="Ej: Apartamento 5B, timbre no funciona, llamar al llegar…"
                              class="field text-sm resize-none"></textarea>
                </div>

                {{-- Resumen del pedido --}}
                <div class="rounded-2xl p-4 space-y-2 text-sm"
                     style="background:#FBF3E2;border:1px solid #EED9A0">
                    <p class="text-xs font-bold text-espresso-700/50 uppercase tracking-wide mb-3">
                        Resumen del pedido
                    </p>
                    <template x-for="item in carrito" :key="item.id">
                        <div class="flex justify-between text-espresso-700">
                            <span x-text="item.cantidad+'× '+item.nombre"></span>
                            <span x-text="fmt(item.precio*item.cantidad)"></span>
                        </div>
                    </template>
                    <div class="border-t border-amber-200 pt-2 mt-2 space-y-1">
                        <div class="flex justify-between text-espresso-700/60">
                            <span>Subtotal galletas</span>
                            <span x-text="fmt(subtotal)"></span>
                        </div>
                        <div x-show="deliveryCostCalc>0" class="flex justify-between text-espresso-700/60">
                            <span>Envío</span>
                            <span x-text="fmt(deliveryCostCalc)"></span>
                        </div>
                        <div x-show="deliv.delivery_cost_type==='free'"
                             class="flex justify-between text-green-600 font-semibold">
                            <span>Envío</span>
                            <span>¡Gratis!</span>
                        </div>
                        <div x-show="promoResultDeliv?.valid && promoResultDeliv?.discount_amount>0"
                             class="flex justify-between text-green-600 font-semibold">
                            <span>Descuento (<span x-text="deliv.promo_code"></span>)</span>
                            <span x-text="'− '+fmt(promoResultDeliv?.discount_amount??0)"></span>
                        </div>
                        <div class="flex justify-between font-display font-bold text-lg border-t border-amber-200 pt-1.5">
                            <span class="text-espresso-900">Total</span>
                            <span class="text-blue-700" x-text="fmt(totalConEnvio)"></span>
                        </div>
                    </div>
                </div>

            </div>{{-- fin cuerpo --}}

            {{-- Footer del modal --}}
            <div class="px-6 py-4 border-t border-cream-200 flex gap-3 flex-shrink-0 bg-white">
                <button @click="showDelivery=false"
                        class="flex-1 btn-ghost justify-center py-3">
                    Cancelar
                </button>
                <button @click="crearDomicilio()"
                        :disabled="!puedeCrearDomicilio || procesandoDomicilio"
                        :class="puedeCrearDomicilio && !procesandoDomicilio
                                ? 'bg-gradient-to-r from-blue-700 to-blue-500 hover:from-blue-800 hover:to-blue-600 shadow-lg shadow-blue-200'
                                : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                        class="flex-1 text-white font-bold py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-all">
                    <template x-if="!procesandoDomicilio">
                        <span class="flex items-center gap-2">
                            <span class="icon icon-sm">local_shipping</span>
                            Registrar Domicilio
                        </span>
                    </template>
                    <template x-if="procesandoDomicilio">
                        <span class="flex items-center gap-2">
                            <span class="icon icon-sm" style="animation:spin 1s linear infinite">progress_activity</span>
                            Procesando…
                        </span>
                    </template>
                </button>
            </div>

        </div>{{-- fin modal card --}}
    </div>{{-- fin modal overlay --}}

</div>{{-- fin posApp --}}
@endsection

@push('styles')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .select-field {
        appearance: none; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='%239a3a10'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px;
    }
</style>
@endpush

@push('scripts')
<script>
function posApp() {
    return {
        // ── Catálogo ───────────────────────────────────────────
        todasLasGalletas: @json($galletas->flatten()->values()),
        galletasFiltradas: [],
        busqueda: '',
        filtroTamano: '',

        // ── Carrito ────────────────────────────────────────────
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
            { id: 'efectivo',      label: 'Efectivo',  icon: 'payments'      },
            { id: 'transferencia', label: 'Nequi/PSE', icon: 'phone_iphone'  },
            { id: 'tarjeta',       label: 'Tarjeta',   icon: 'credit_card'   },
        ],

        // ── Código promo (venta normal) ────────────────────────
        codigoPromo: '',
        promoResult: null,
        promoDescuento: 0,

        // ── Domicilios ─────────────────────────────────────────
        showDelivery: false,
        procesandoDomicilio: false,
        promoResultDeliv: null,
        deliv: {
            // Modo: 'registrado' o 'mostrador'
            modo_cliente:          'registrado',
            // Cliente registrado
            clienteSeleccionado:   null,
            queryCliente:          '',
            resultadosCliente:     [],
            showDropdown:          false,
            guardar_direccion:     false,
            // Cliente mostrador
            customer_name:         '',
            customer_phone:        '',
            // Dirección (compartida)
            delivery_address:      '',
            delivery_neighborhood: '',
            // Envío y pago
            delivery_cost_type:    'additional',
            delivery_cost:         3000,
            payment_method:        'cash_on_delivery',
            promo_code:            '',
            notes:                 '',
        },

        // ── Init ───────────────────────────────────────────────
        init() {
            this.galletasFiltradas = this.todasLasGalletas;
        },

        // ── Catálogo ───────────────────────────────────────────
        filtrar() {
            const t = this.busqueda.toLowerCase();
            this.galletasFiltradas = this.todasLasGalletas.filter(g => {
                const okTam  = !this.filtroTamano || g.tamano === this.filtroTamano;
                const okBusc = !t || g.nombre.toLowerCase().includes(t)
                                  || (g.rellenos || []).some(r => r.toLowerCase().includes(t));
                return okTam && okBusc;
            });
        },

        // ── Carrito ────────────────────────────────────────────

agregar(g) {
    const i = this.carrito.findIndex(x => x.id === g.id);
    const cantidadEnCarrito = i >= 0 ? this.carrito[i].cantidad : 0;

    if (cantidadEnCarrito >= g.stock) {
        alert(`Solo hay ${g.stock} unidades de "${g.nombre}" disponibles.`);
        return;
    }

    if (i >= 0) {
        this.carrito[i].cantidad++;
    } else {
        this.carrito.push({ ...g, cantidad: 1 });
    }
    this.recalcular();
},

inc(i) {
    const item = this.carrito[i];                                    // ← sacar el item por índice
    const galleta = this.todasLasGalletas.find(g => g.id === item.id);
    const stockMax = galleta ? galleta.stock : 999;
    if (item.cantidad >= stockMax) {
        alert(`Solo hay ${stockMax} unidades de "${item.nombre}" disponibles.`);
        return;
    }
    this.carrito[i].cantidad++;
    this.recalcular();
},

dec(i) {
    this.carrito[i].cantidad > 1
        ? this.carrito[i].cantidad--
        : this.carrito.splice(i, 1);
    this.recalcular();             // ← faltaba
},

        limpiar() {
            this.carrito      = [];
            this.descuento    = 0;
            this.metodoPago   = 'efectivo';
            this.tieneDeuda   = false;
            this.limpiarPromo();
        },

        // ── Getters carrito ────────────────────────────────────
        get totalItems()     { return this.carrito.reduce((s, i) => s + i.cantidad, 0); },
        get subtotal()       { return this.carrito.reduce((s, i) => s + i.precio * i.cantidad, 0); },
        get descuentoValor() {
            const pctDesc = Math.round(this.subtotal * (this.descuento / 100));
            return pctDesc + (this.promoDescuento || 0);
        },
        get total()          { return Math.max(0, this.subtotal - this.descuentoValor); },

        // ── Getters domicilio ──────────────────────────────────
        get deliveryCostCalc() {
            if (this.deliv.delivery_cost_type !== 'additional') return 0;
            return parseFloat(this.deliv.delivery_cost) || 0;
        },
        get deliveryDiscountAmount() {
            if (!this.promoResultDeliv?.valid) return 0;
            return this.promoResultDeliv.discount_amount ?? 0;
        },
        get totalConEnvio() {
            return Math.max(0, this.subtotal - this.deliveryDiscountAmount + this.deliveryCostCalc);
        },
        get puedeCrearDomicilio() {
            const tieneCliente = this.deliv.modo_cliente === 'mostrador'
                || this.deliv.clienteSeleccionado !== null;
            return this.carrito.length > 0
                && this.deliv.delivery_address.trim() !== ''
                && this.deliv.payment_method !== ''
                && tieneCliente;
        },

        // ── Helpers ────────────────────────────────────────────
        fmt(v) { return '$' + Math.round(v).toLocaleString('es-CO'); },

        // ── Clientes ───────────────────────────────────────────
        async buscarClientes() {
            if (!this.queryCliente) { this.resultadosClientes = []; return; }
            const r = await fetch(`/pos/buscar-clientes?q=${encodeURIComponent(this.queryCliente)}`);
            this.resultadosClientes = await r.json();
        },
        setCliente(c) {
            this.cliente           = c;
            this.showClientes      = false;
            this.queryCliente      = '';
            this.resultadosClientes = [];
            if (c.id === 1) this.tieneDeuda = false;
        },

        // ── Código promo (venta normal) ────────────────────────
        async validarCodigo() {
            if (!this.codigoPromo) return;
            const cookieIds = this.carrito.map(i => i.id);
            const params    = new URLSearchParams({
                code:     this.codigoPromo,
                subtotal: this.subtotal,
                delivery: 0,
            });
            cookieIds.forEach(id => params.append('cookie_ids[]', id));
            const r         = await fetch(`/admin/api/promo-codes/validate?${params}`);
            this.promoResult = await r.json();
            this.promoDescuento = this.promoResult.valid
                ? (this.promoResult.discount_amount ?? 0)
                : 0;
        },
        limpiarPromo() {
            this.codigoPromo  = '';
            this.promoResult  = null;
            this.promoDescuento = 0;
        },

        // ── Búsqueda de clientes para domicilio ───────────────
        async buscarClientesDomicilio() {
            if (!this.deliv.queryCliente || this.deliv.queryCliente.length < 2) {
                this.deliv.resultadosCliente = [];
                return;
            }
            try {
                const r = await fetch(`/pos/buscar-clientes?q=${encodeURIComponent(this.deliv.queryCliente)}&con_direcciones=1`);
                this.deliv.resultadosCliente = await r.json();
                this.deliv.showDropdown = true;
            } catch (e) {
                this.deliv.resultadosCliente = [];
            }
        },

        seleccionarClienteDomicilio(c) {
            this.deliv.clienteSeleccionado = c;
            this.deliv.queryCliente        = c.nombre;
            this.deliv.showDropdown        = false;
            // Si tiene solo una dirección, preseleccionarla
            if (c.direcciones && c.direcciones.length === 1) {
                this.seleccionarDireccion(c.direcciones[0]);
            } else {
                this.deliv.delivery_address      = '';
                this.deliv.delivery_neighborhood = '';
            }
        },

        seleccionarDireccion(dir) {
            this.deliv.delivery_address      = dir.direccion;
            this.deliv.delivery_neighborhood = dir.barrio || '';
        },

        resetDelivCustomer() {
            this.deliv.clienteSeleccionado   = null;
            this.deliv.queryCliente          = '';
            this.deliv.resultadosCliente     = [];
            this.deliv.showDropdown          = false;
            this.deliv.delivery_address      = '';
            this.deliv.delivery_neighborhood = '';
            this.deliv.guardar_direccion     = false;
            this.deliv.customer_name         = '';
            this.deliv.customer_phone        = '';
        },

        // ── Código promo (domicilio) ───────────────────────────
        async validatePromoForDelivery() {
            if (!this.deliv.promo_code) return;
            const cookieIds = this.carrito.map(i => i.id);
            const params    = new URLSearchParams({
                code:     this.deliv.promo_code,
                subtotal: this.subtotal,
                delivery: 1,
            });
            cookieIds.forEach(id => params.append('cookie_ids[]', id));
            const r             = await fetch(`/admin/api/promo-codes/validate?${params}`);
            this.promoResultDeliv = await r.json();

            // Si el código da domicilio gratis, poner costo en 0
            if (this.promoResultDeliv.valid && this.promoResultDeliv.type === 'free_delivery') {
                this.deliv.delivery_cost_type = 'free';
            }
        },

        // ── Cobrar (venta normal) ──────────────────────────────
        async cobrar() {
            if (this.procesando) return;
            this.procesando = true;
            try {
                const res = await fetch('/pos/venta', {
                    method:  'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'X-CSRF-TOKEN':  document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        customer_id:          this.cliente.id,
                        items:                this.carrito.map(i => ({
                                                  cookie_id:        i.id,
                                                  cantidad:         i.cantidad,
                                                  precio_unitario:  i.precio,
                                              })),
                        descuento_porcentaje: this.descuento,
                        descuento_promo:      this.promoDescuento,
                        promo_code:           this.promoResult?.valid ? this.codigoPromo : null,
                        metodo_pago:          this.metodoPago,
                        metodos_pago:         [{ metodo: this.metodoPago, monto: this.total }],
                        tiene_deuda:          this.tieneDeuda,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    window.open(`/pos/comprobante/${data.sale_id}`, '_blank');
                    this.limpiar();
                    this.cliente = { id: 1, nombre: 'Cliente de Mostrador' };
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (e) {
                alert('❌ Error de conexión');
            } finally {
                this.procesando = false;
            }
        },

        // ── Crear domicilio ────────────────────────────────────
        async crearDomicilio() {
            if (!this.puedeCrearDomicilio || this.procesandoDomicilio) return;
            this.procesandoDomicilio = true;

            // Construir payload según modo
            const esRegistrado = this.deliv.modo_cliente === 'registrado' && this.deliv.clienteSeleccionado;
            const payload = {
                customer_id:            esRegistrado ? this.deliv.clienteSeleccionado.id : null,
                customer_name:          esRegistrado ? null : (this.deliv.customer_name || null),
                customer_phone:         esRegistrado ? null : (this.deliv.customer_phone || null),
                delivery_address:       this.deliv.delivery_address,
                delivery_neighborhood:  this.deliv.delivery_neighborhood || null,
                delivery_cost_type:     this.deliv.delivery_cost_type,
                delivery_cost:          this.deliveryCostCalc,
                payment_method:         this.deliv.payment_method,
                promo_code:             this.promoResultDeliv?.valid ? this.deliv.promo_code : null,
                notes:                  this.deliv.notes || null,
                guardar_direccion:      esRegistrado && this.deliv.guardar_direccion,
                items:                  this.carrito.map(i => ({
                                            cookie_id: i.id,
                                            cantidad:  i.cantidad,
                                        })),
            };

            try {
                const r = await fetch('/admin/deliveries', {
                    method:  'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'Accept':        'application/json',          // ← clave: fuerza JSON en errores Laravel
                        'X-CSRF-TOKEN':  document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(payload),
                });

                // Leer el texto crudo primero para no perder el cuerpo si no es JSON
                const raw  = await r.text();
                let data;
                try {
                    data = JSON.parse(raw);
                } catch (_) {
                    // Laravel devolvió HTML (ej. error 500 o página de login)
                    console.error('Respuesta no-JSON del servidor:', raw.substring(0, 500));
                    alert('❌ Error del servidor (código ' + r.status + '). Revisa la consola para detalles.');
                    return;
                }

                if (r.ok && data.success) {
                    this.showDelivery     = false;
                    this.promoResultDeliv = null;
                    this.deliv = {
                        modo_cliente: 'registrado',
                        clienteSeleccionado: null, queryCliente: '', resultadosCliente: [], showDropdown: false, guardar_direccion: false,
                        customer_name: '', customer_phone: '',
                        delivery_address: '', delivery_neighborhood: '',
                        delivery_cost_type: 'additional', delivery_cost: 3000,
                        payment_method: 'cash_on_delivery', promo_code: '', notes: '',
                    };
                    this.limpiar();
                    alert('🛵 ' + data.message);
                } else if (r.status === 422 && data.errors) {
                    // Errores de validación de Laravel — mostrar el primero
                    const primerError = Object.values(data.errors).flat()[0];
                    alert('⚠️ ' + primerError);
                } else {
                    alert('❌ ' + (data.message || 'Error al registrar el domicilio'));
                }
            } catch (e) {
                console.error('Error en crearDomicilio:', e);
                alert('❌ Error de red: ' + e.message);
            } finally {
                this.procesandoDomicilio = false;
            }
        },
    };
}
</script>
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush