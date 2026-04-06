{{-- ═══════════════════════════════════════════════════════════════
     resources/views/pos/delivery_modal.blade.php
     Modal de domicilio con opción de Fiado agregada
     ═══════════════════════════════════════════════════════════════ --}}

{{-- ══ MODAL OVERLAY ═════════════════════════════════════════════ --}}
<div x-show="showDelivery" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="showDelivery=false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(26,10,0,0.55);backdrop-filter:blur(4px)">

    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col"
         @click.stop>

        {{-- ── Header ──────────────────────────────────────────── --}}
        <div class="px-6 py-4 flex items-center justify-between flex-shrink-0"
             style="background:linear-gradient(135deg,#1a0a00,#0e7490)">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="icon text-white">local_shipping</span>
                </div>
                <div>
                    <p class="font-display font-bold text-white text-base">Nuevo Domicilio</p>
                    <p class="text-white/60 text-xs"
                       x-text="carrito.reduce((s,i)=>s+i.cantidad,0) + ' galleta(s) · ' + fmt(totalConEnvio ?? subtotal)"></p>
                </div>
            </div>
            <button @click="showDelivery=false"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
                <span class="icon icon-sm">close</span>
            </button>
        </div>

        {{-- ── Cuerpo scrolleable ───────────────────────────────── --}}
        <div class="overflow-y-auto flex-1 p-6 space-y-5">

            {{-- ── Sección: Cliente ─────────────────────────────── --}}
            <div class="space-y-3">
                <p class="text-xs font-bold text-espresso-700/50 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="icon icon-sm text-brand-400">person</span>Datos del cliente
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Nombre</label>
                        <input type="text" x-model="deliv.customer_name"
                               placeholder="Ej: María García"
                               class="field text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Teléfono</label>
                        <input type="text" x-model="deliv.customer_phone"
                               placeholder="3001234567"
                               class="field text-sm">
                    </div>
                </div>

                {{-- Búsqueda de cliente registrado --}}
                <div class="relative">
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                        Cliente registrado (opcional)
                    </label>
                    <div class="relative">
                        <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">search</span>
                        <input type="text"
                               x-model="deliv.queryCliente"
                               @input.debounce.300ms="buscarClientesDeliv()"
                               @focus="buscarClientesDeliv()"
                               placeholder="Buscar por nombre o teléfono..."
                               class="field pl-9 text-sm">
                    </div>
                    <div x-show="deliv.resultadosClientes.length > 0 && !deliv.clienteSeleccionado"
                         class="absolute z-10 w-full mt-1 bg-white rounded-xl border border-cream-200 shadow-lg overflow-hidden">
                        <template x-for="c in deliv.resultadosClientes" :key="c.id">
                            <button @click="seleccionarClienteDeliv(c)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-cream-50 transition-colors border-b border-cream-100 last:border-0">
                                <p class="text-sm font-bold text-espresso-900" x-text="c.nombre"></p>
                                <p class="text-xs text-espresso-700/50" x-text="c.telefono ?? 'Sin teléfono'"></p>
                            </button>
                        </template>
                    </div>
                    <div x-show="deliv.clienteSeleccionado"
                         class="mt-2 flex items-center gap-3 px-4 py-2.5 bg-green-50 border border-green-200 rounded-xl">
                        <div class="w-8 h-8 rounded-lg bg-green-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                             x-text="(deliv.clienteSeleccionado?.nombre ?? '?').charAt(0).toUpperCase()"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-green-800" x-text="deliv.clienteSeleccionado?.nombre"></p>
                            <p class="text-xs text-green-600" x-text="deliv.clienteSeleccionado?.telefono ?? '—'"></p>
                        </div>
                        <button @click="deliv.clienteSeleccionado=null; deliv.queryCliente=''"
                                class="text-green-500 hover:text-green-700 transition-colors">
                            <span class="icon icon-sm">close</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Sección: Dirección ───────────────────────────── --}}
            <div class="space-y-3">
                <p class="text-xs font-bold text-espresso-700/50 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="icon icon-sm text-brand-400">location_on</span>Dirección de entrega
                </p>
                <input type="text" x-model="deliv.delivery_address"
                       placeholder="Cra 15 #45-20 Apto 301"
                       class="field text-sm">
                <input type="text" x-model="deliv.delivery_neighborhood"
                       placeholder="Barrio (opcional)"
                       class="field text-sm">
            </div>

            {{-- ── Sección: Costo de envío ──────────────────────── --}}
            <div class="space-y-3">
                <p class="text-xs font-bold text-espresso-700/50 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="icon icon-sm text-brand-400">local_shipping</span>Costo de envío
                </p>
                <div class="grid grid-cols-3 gap-2">
                    <label :class="deliv.delivery_cost_type==='free'
                                ? 'border-green-500 bg-green-50'
                                : 'border-cream-200 bg-white hover:border-green-300'"
                           class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all">
                        <input type="radio" x-model="deliv.delivery_cost_type" value="free" class="sr-only">
                        <span class="icon text-green-500">local_offer</span>
                        <span class="text-xs font-bold text-espresso-800">Gratis</span>
                        <span class="text-[10px] text-espresso-700/50">Sin cobro envío</span>
                    </label>
                    <label :class="deliv.delivery_cost_type==='additional'
                                ? 'border-brand-500 bg-brand-50'
                                : 'border-cream-200 bg-white hover:border-brand-300'"
                           class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all">
                        <input type="radio" x-model="deliv.delivery_cost_type" value="additional" class="sr-only">
                        <span class="icon text-brand-500">attach_money</span>
                        <span class="text-xs font-bold text-espresso-800">Con cobro</span>
                        <span class="text-[10px] text-espresso-700/50">Paga el cliente</span>
                    </label>
                    <label :class="deliv.delivery_cost_type==='business'
                                ? 'border-blue-500 bg-blue-50'
                                : 'border-cream-200 bg-white hover:border-blue-300'"
                           class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all">
                        <input type="radio" x-model="deliv.delivery_cost_type" value="business" class="sr-only">
                        <span class="icon text-blue-500">storefront</span>
                        <span class="text-xs font-bold text-espresso-800">Mensajero propio</span>
                        <span class="text-[10px] text-espresso-700/50">El negocio paga</span>
                    </label>
                </div>
                <div x-show="deliv.delivery_cost_type==='additional'" x-transition>
                    <input type="number" x-model="deliv.delivery_cost" min="0" step="500"
                           placeholder="3000"
                           class="field text-sm">
                </div>
            </div>

            {{-- ── Sección: Método de pago ──────────────────────── --}}
            <div class="space-y-3">
                <p class="text-xs font-bold text-espresso-700/50 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="icon icon-sm text-brand-400">payments</span>Método de pago <span class="text-red-400">*</span>
                </p>

                {{-- Grid: Paga al llegar + Transferencia --}}
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

                {{-- ✅ NUEVO: Fiado — ancho completo, requiere cliente registrado --}}
                <label :class="deliv.payment_method==='debt'
                            ? 'border-red-400 bg-red-50'
                            : (deliv.clienteSeleccionado
                                ? 'border-cream-200 bg-white hover:border-red-300 cursor-pointer'
                                : 'border-cream-100 bg-cream-50 opacity-50 cursor-not-allowed')"
                       class="flex items-center gap-3 p-3 rounded-xl border-2 transition-all w-full">
                    <input type="radio" x-model="deliv.payment_method" value="debt" class="sr-only"
                           :disabled="!deliv.clienteSeleccionado">
                    <span class="icon flex-shrink-0"
                          :class="deliv.payment_method==='debt' ? 'text-red-500' : 'text-espresso-700/40'">
                        credit_card_off
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-espresso-800">🫱 Fiado / Deuda</p>
                        <p class="text-[10px]"
                           :class="deliv.payment_method==='debt' ? 'text-red-500' : 'text-espresso-700/50'">
                            <template x-if="deliv.clienteSeleccionado">
                                <span>Queda registrado como cuenta por cobrar de <strong x-text="deliv.clienteSeleccionado?.nombre"></strong></span>
                            </template>
                            <template x-if="!deliv.clienteSeleccionado">
                                <span class="text-amber-600">⚠ Selecciona un cliente registrado para usar esta opción</span>
                            </template>
                        </p>
                    </div>
                    <span x-show="deliv.payment_method==='debt'"
                          class="badge badge-red text-[10px] flex-shrink-0">Deuda</span>
                </label>
            </div>

            {{-- ── Código promo ─────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                    Código Promocional
                </label>
                <div class="flex gap-2">
                    <input type="text" x-model="deliv.promo_code"
                           @input="deliv.promo_code=$event.target.value.toUpperCase(); promoResultDeliv=null"
                           @keydown.enter="validatePromoForDelivery()"
                           placeholder="Ej: FREEENVIO"
                           class="field text-sm flex-1" style="text-transform:uppercase">
                    <button @click="validatePromoForDelivery()"
                            :disabled="!deliv.promo_code"
                            class="btn-ghost py-2 px-4 text-sm disabled:opacity-40">
                        Validar
                    </button>
                    <button x-show="promoResultDeliv?.valid"
                            @click="promoResultDeliv=null; deliv.promo_code=''"
                            class="px-2 text-red-400 hover:text-red-600 transition-colors">
                        <span class="icon icon-sm">close</span>
                    </button>
                </div>
                <div x-show="promoResultDeliv" x-transition
                     class="mt-1.5 px-3 py-2 rounded-lg text-xs font-semibold"
                     :class="promoResultDeliv?.valid ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'"
                     x-text="promoResultDeliv?.message">
                </div>
            </div>

            {{-- ── Notas ────────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                    Notas del pedido
                </label>
                <textarea x-model="deliv.notes"
                          placeholder="Instrucciones especiales, punto de referencia..."
                          rows="2"
                          class="field text-sm resize-none"></textarea>
            </div>

            {{-- ── Resumen de totales ───────────────────────────── --}}
            <div class="rounded-2xl overflow-hidden border border-cream-200">
                <div class="px-4 py-3 bg-cream-50">
                    <p class="text-xs font-bold text-espresso-700/50 uppercase tracking-wider mb-3">Resumen del pedido</p>
                    <div class="space-y-1.5 text-sm">
                        <template x-for="item in carrito" :key="item.id">
                            <div class="flex justify-between text-espresso-700/70">
                                <span x-text="item.cantidad + '× ' + item.nombre"></span>
                                <span x-text="fmt(item.precio * item.cantidad)"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="px-4 py-3 space-y-1 border-t border-cream-200 bg-white">
                    <div class="flex justify-between text-sm text-espresso-700/60">
                        <span>Subtotal galletas</span>
                        <span x-text="fmt(subtotal)"></span>
                    </div>
                    <div x-show="promoResultDeliv?.valid && (promoResultDeliv?.discount_amount ?? 0) > 0"
                         class="flex justify-between text-sm text-green-600">
                        <span>Descuento promo</span>
                        <span x-text="'− ' + fmt(promoResultDeliv?.discount_amount ?? 0)"></span>
                    </div>
                    <div x-show="deliv.delivery_cost_type==='additional'"
                         class="flex justify-between text-sm text-espresso-700/60">
                        <span>Costo envío</span>
                        <span x-text="fmt(deliv.delivery_cost || 0)"></span>
                    </div>
                    <div class="flex justify-between font-display font-bold text-lg pt-1 border-t border-cream-200">
                        <span class="text-espresso-900">Total</span>
                        <span class="text-brand-700" x-text="fmt(totalConEnvio)"></span>
                    </div>
                    {{-- Aviso fiado --}}
                    <div x-show="deliv.payment_method==='debt'" x-transition
                         class="mt-2 flex items-center gap-2 px-3 py-2 rounded-xl bg-red-50 border border-red-200">
                        <span class="icon icon-sm text-red-500">warning</span>
                        <p class="text-xs text-red-600 font-semibold">
                            Este domicilio quedará como deuda pendiente del cliente
                        </p>
                    </div>
                </div>
            </div>

        </div>{{-- /cuerpo --}}

        {{-- ── Footer ──────────────────────────────────────────── --}}
        <div class="px-6 py-4 border-t border-cream-200 flex gap-3 flex-shrink-0">
            <button @click="showDelivery=false" class="flex-1 btn-ghost justify-center py-3">
                Cancelar
            </button>
            <button @click="crearDomicilio()"
                    :disabled="!puedeCrearDomicilio || procesandoDomicilio"
                    :class="puedeCrearDomicilio && !procesandoDomicilio
                        ? (deliv.payment_method==='debt'
                            ? 'bg-gradient-to-r from-red-700 to-red-500 hover:from-red-800 hover:to-red-600'
                            : 'bg-gradient-to-r from-cyan-700 to-cyan-500 hover:from-cyan-800 hover:to-cyan-600')
                        : 'bg-gray-300 cursor-not-allowed'"
                    class="flex-1 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg">
                <span x-show="!procesandoDomicilio" class="flex items-center gap-2">
                    <span class="icon icon-sm" x-text="deliv.payment_method==='debt' ? 'credit_card_off' : 'local_shipping'"></span>
                    <span x-text="deliv.payment_method==='debt' ? 'Registrar Fiado' : 'Registrar Domicilio'"></span>
                </span>
                <span x-show="procesandoDomicilio" class="flex items-center gap-2">
                    <span class="icon icon-sm" style="animation:spin 1s linear infinite">progress_activity</span>
                    Procesando...
                </span>
            </button>
        </div>

    </div>{{-- /modal box --}}
</div>{{-- /overlay --}}