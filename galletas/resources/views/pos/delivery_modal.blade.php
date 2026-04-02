{{-- ═══════════════════════════════════════════════════════════════
     MÓDULO DOMICILIO — Agregar al final de pos/index.blade.php
     Dentro del div principal x-data="posApp()"
     ═══════════════════════════════════════════════════════════════ --}}

{{-- ── BOTÓN DOMICILIO en el carrito (después del botón "Cobrar") --}}
{{-- Añadir ANTES del botón "Cobrar" en la sección del carrito:    --}}
<div x-show="carrito.length>0" class="mb-2">
    <button @click="showDelivery=true"
            class="w-full py-3 rounded-xl border-2 border-brand-300 text-brand-700 font-bold text-sm hover:bg-brand-50 transition-all flex items-center justify-center gap-2">
        <span class="icon icon-sm">local_shipping</span>
        Registrar como Domicilio
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL DOMICILIO — Agregar antes del cierre del div principal
     ═══════════════════════════════════════════════════════════════ --}}
<div x-show="showDelivery" x-cloak x-transition
     @click.self="showDelivery=false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-900/50 backdrop-blur-sm">

    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

        {{-- Header --}}
        <div class="px-6 py-4 flex items-center justify-between flex-shrink-0"
             style="background:linear-gradient(135deg,#1a0a00,#0891B2)">
            <div class="flex items-center gap-3">
                <span class="icon text-white">local_shipping</span>
                <div>
                    <p class="font-display font-bold text-white">Nuevo Pedido a Domicilio</p>
                    <p class="text-white/60 text-xs" x-text="totalItems + ' galleta(s) · ' + fmt(totalConEnvio)"></p>
                </div>
            </div>
            <button @click="showDelivery=false" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white">
                <span class="icon icon-sm">close</span>
            </button>
        </div>

        {{-- Cuerpo scrolleable --}}
        <div class="overflow-y-auto flex-1 p-6 space-y-5">

            {{-- Datos del cliente --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                        Nombre del cliente
                    </label>
                    <input type="text" x-model="deliv.customer_name" placeholder="Ej: María García"
                           class="field text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                        Teléfono de contacto
                    </label>
                    <input type="text" x-model="deliv.customer_phone" placeholder="Ej: 300 123 4567"
                           class="field text-sm">
                </div>
            </div>

            {{-- Dirección --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                        Dirección de envío <span class="text-red-400">*</span>
                    </label>
                    <input type="text" x-model="deliv.delivery_address"
                           placeholder="Ej: Cra 15 #45-20 Apto 301"
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

            {{-- Costo de envío --}}
            <div>
                <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
                    Tipo de envío
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label :class="deliv.delivery_cost_type==='additional' ? 'border-brand-500 bg-brand-50' : 'border-cream-200 bg-white'"
                           class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all">
                        <input type="radio" x-model="deliv.delivery_cost_type" value="additional" class="sr-only">
                        <span class="icon text-brand-500">attach_money</span>
                        <span class="text-xs font-bold text-espresso-800">Cobrado al cliente</span>
                        <span class="text-[10px] text-espresso-700/50">Se suma al pedido</span>
                    </label>
                    <label :class="deliv.delivery_cost_type==='free' ? 'border-green-500 bg-green-50' : 'border-cream-200 bg-white'"
                           class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all">
                        <input type="radio" x-model="deliv.delivery_cost_type" value="free" class="sr-only">
                        <span class="icon text-green-500">local_shipping</span>
                        <span class="text-xs font-bold text-espresso-800">Gratis para cliente</span>
                        <span class="text-[10px] text-espresso-700/50">Lo asume el negocio</span>
                    </label>
                    <label :class="deliv.delivery_cost_type==='business' ? 'border-blue-500 bg-blue-50' : 'border-cream-200 bg-white'"
                           class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all">
                        <input type="radio" x-model="deliv.delivery_cost_type" value="business" class="sr-only">
                        <span class="icon text-blue-500">storefront</span>
                        <span class="text-xs font-bold text-espresso-800">Mensajero propio</span>
                        <span class="text-[10px] text-espresso-700/50">El negocio paga</span>
                    </label>
                </div>
            </div>

            {{-- Valor del envío --}}
            <div x-show="deliv.delivery_cost_type === 'additional'" x-transition>
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
                    <label :class="deliv.payment_method==='cash_on_delivery' ? 'border-brand-500 bg-brand-50' : 'border-cream-200 bg-white'"
                           class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all">
                        <input type="radio" x-model="deliv.payment_method" value="cash_on_delivery" class="sr-only">
                        <span class="icon text-brand-500">payments</span>
                        <div>
                            <p class="text-sm font-bold text-espresso-800">Paga al llegar</p>
                            <p class="text-[10px] text-espresso-700/50">Efectivo o transfer. en entrega</p>
                        </div>
                    </label>
                    <label :class="deliv.payment_method==='transfer' ? 'border-purple-500 bg-purple-50' : 'border-cream-200 bg-white'"
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

            {{-- Código promocional --}}
            <div>
                <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                    Código Promocional
                </label>
                <div class="flex gap-2">
                    <input type="text" x-model="deliv.promo_code"
                           @input="deliv.promo_code = $event.target.value.toUpperCase()"
                           placeholder="Ej: VERANO20"
                           class="field text-sm uppercase flex-1">
                    <button @click="validatePromoForDelivery()"
                            class="btn-ghost py-2 px-4 text-sm">
                        Validar
                    </button>
                </div>
                <div x-show="promoResultDeliv" class="mt-2 px-3 py-2 rounded-lg text-xs font-semibold"
                     :class="promoResultDeliv?.valid ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'"
                     x-text="promoResultDeliv?.message">
                </div>
            </div>

            {{-- Notas --}}
            <div>
                <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                    Notas para el pedido
                </label>
                <textarea x-model="deliv.notes" rows="2"
                          placeholder="Ej: Apartamento 5B, timbre no funciona, llamar al llegar..."
                          class="field text-sm resize-none"></textarea>
            </div>

            {{-- Resumen del pedido --}}
            <div class="rounded-2xl p-4 space-y-2 text-sm" style="background:#FBF3E2;border:1px solid #EED9A0">
                <p class="text-xs font-bold text-espresso-700/50 uppercase tracking-wide mb-3">Resumen del pedido</p>
                <template x-for="item in carrito" :key="item.id">
                    <div class="flex justify-between text-espresso-700">
                        <span x-text="item.cantidad+'× '+item.nombre"></span>
                        <span x-text="fmt(item.precio*item.cantidad)"></span>
                    </div>
                </template>
                <div class="border-t border-amber-200 pt-2 mt-2 space-y-1">
                    <div class="flex justify-between text-espresso-700/60">
                        <span>Subtotal</span>
                        <span x-text="fmt(subtotal)"></span>
                    </div>
                    <div x-show="deliveryCostCalc > 0" class="flex justify-between text-espresso-700/60">
                        <span>Envío</span>
                        <span x-text="fmt(deliveryCostCalc)"></span>
                    </div>
                    <div x-show="deliv.delivery_cost_type === 'free'" class="flex justify-between text-green-600 font-semibold">
                        <span>Envío</span>
                        <span>¡Gratis!</span>
                    </div>
                    <div x-show="promoResultDeliv?.valid && promoResultDeliv?.discount_amount > 0"
                         class="flex justify-between text-green-600 font-semibold">
                        <span>Descuento (<span x-text="deliv.promo_code"></span>)</span>
                        <span x-text="'- ' + fmt(promoResultDeliv?.discount_amount ?? 0)"></span>
                    </div>
                    <div class="flex justify-between font-display font-bold text-lg pt-1">
                        <span class="text-espresso-900">Total</span>
                        <span class="text-brand-700" x-text="fmt(totalConEnvio)"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-cream-200 flex gap-3 flex-shrink-0">
            <button @click="showDelivery=false" class="flex-1 btn-ghost justify-center py-3">
                Cancelar
            </button>
            <button @click="crearDomicilio()"
                    :disabled="!puedeCrearDomicilio || procesandoDomicilio"
                    :class="puedeCrearDomicilio && !procesandoDomicilio
                            ? 'bg-gradient-to-r from-blue-700 to-blue-500 hover:from-blue-800 hover:to-blue-600'
                            : 'bg-gray-300 cursor-not-allowed'"
                    class="flex-1 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg">
                <span x-show="!procesandoDomicilio" class="flex items-center gap-2">
                    <span class="icon icon-sm">local_shipping</span>
                    Registrar Domicilio
                </span>
                <span x-show="procesandoDomicilio">Procesando...</span>
            </button>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════
     JAVASCRIPT — Agregar dentro de la función posApp() en pos/index.blade.php
     Agregar estas propiedades y métodos a la función existente
     ═══════════════════════════════════════════════════════════════ --}}
<script>
// Añadir estas propiedades al objeto de posApp():
/*
// ── Domicilios ─────────────────────────────────
showDelivery: false,
procesandoDomicilio: false,
promoResultDeliv: null,

deliv: {
    customer_name: '',
    customer_phone: '',
    delivery_address: '',
    delivery_neighborhood: '',
    delivery_cost_type: 'additional',
    delivery_cost: 3000,
    payment_method: 'cash_on_delivery',
    promo_code: '',
    notes: '',
},

get deliveryCostCalc() {
    if (this.deliv.delivery_cost_type !== 'additional') return 0;
    return parseFloat(this.deliv.delivery_cost) || 0;
},

get totalConEnvio() {
    const disc = this.promoResultDeliv?.valid ? (this.promoResultDeliv?.discount_amount ?? 0) : 0;
    return Math.max(0, this.subtotal - disc + this.deliveryCostCalc);
},

get puedeCrearDomicilio() {
    return this.carrito.length > 0
        && this.deliv.delivery_address.trim() !== ''
        && this.deliv.payment_method !== '';
},

async validatePromoForDelivery() {
    if (!this.deliv.promo_code) return;
    const cookieIds = this.carrito.map(i => i.id);
    const params = new URLSearchParams({
        code: this.deliv.promo_code,
        subtotal: this.subtotal,
        delivery: 1,
    });
    cookieIds.forEach(id => params.append('cookie_ids[]', id));
    const r = await fetch(`/admin/api/promo-codes/validate?${params}`);
    this.promoResultDeliv = await r.json();
},

async crearDomicilio() {
    if (!this.puedeCrearDomicilio || this.procesandoDomicilio) return;
    this.procesandoDomicilio = true;

    const payload = {
        customer_name: this.deliv.customer_name,
        customer_phone: this.deliv.customer_phone,
        delivery_address: this.deliv.delivery_address,
        delivery_neighborhood: this.deliv.delivery_neighborhood,
        delivery_cost_type: this.deliv.delivery_cost_type,
        delivery_cost: this.deliveryCostCalc,
        payment_method: this.deliv.payment_method,
        promo_code: this.deliv.promo_code || null,
        notes: this.deliv.notes,
        items: this.carrito.map(i => ({ cookie_id: i.id, cantidad: i.cantidad })),
    };

    try {
        const r = await fetch('/admin/deliveries', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(payload),
        });
        const data = await r.json();
        if (data.success) {
            this.showDelivery = false;
            this.limpiar();
            this.promoResultDeliv = null;
            // Reset form
            this.deliv = { customer_name:'', customer_phone:'', delivery_address:'', delivery_neighborhood:'', delivery_cost_type:'additional', delivery_cost:3000, payment_method:'cash_on_delivery', promo_code:'', notes:'' };
            alert(data.message);
        } else {
            alert('❌ ' + (data.message || 'Error al registrar el domicilio'));
        }
    } catch(e) {
        alert('❌ Error de conexión');
    } finally {
        this.procesandoDomicilio = false;
    }
},
*/
</script>
