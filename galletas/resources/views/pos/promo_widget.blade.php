{{-- ═══════════════════════════════════════════════════════════════
     WIDGET CÓDIGO PROMO EN EL POS — Añadir en el carrito,
     justo antes del bloque "Descuento %" del POS existente
     ═══════════════════════════════════════════════════════════════ --}}

{{-- Código Promocional --}}
<div x-show="carrito.length > 0" class="px-4 pb-3" x-transition>
    <div class="flex gap-2">
        <div class="relative flex-1">
            <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">local_offer</span>
            <input type="text" x-model="codigoPromo"
                   @input="codigoPromo=$event.target.value.toUpperCase(); promoResult=null"
                   @keydown.enter="validarCodigo()"
                   placeholder="Código promo…"
                   class="field pl-9 text-sm uppercase">
        </div>
        <button @click="validarCodigo()"
                :disabled="!codigoPromo"
                class="btn-ghost py-2 px-3 text-xs disabled:opacity-40">
            Aplicar
        </button>
        <button x-show="promoResult?.valid" @click="limpiarPromo()"
                class="px-2 py-2 text-red-400 hover:text-red-600 transition">
            <span class="icon icon-sm">close</span>
        </button>
    </div>
    <div x-show="promoResult" class="mt-1.5 px-3 py-2 rounded-lg text-xs font-semibold"
         :class="promoResult?.valid ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'"
         x-text="promoResult?.message">
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     JAVASCRIPT — Añadir a la función posApp()
     ═══════════════════════════════════════════════════════════════ --}}
<script>
/*
// Propiedades a añadir en posApp():
codigoPromo: '',
promoResult: null,
promoDescuento: 0,

// Modificar el getter "get descuentoValor()" existente para incluir el promo:
// ANTES:
// get descuentoValor() { return Math.round(this.subtotal*(this.descuento/100)); },
// DESPUÉS:
get descuentoValor() {
    const pctDesc = Math.round(this.subtotal * (this.descuento / 100));
    return pctDesc + (this.promoDescuento || 0);
},

// Método validarCodigo():
async validarCodigo() {
    if (!this.codigoPromo) return;
    const cookieIds = this.carrito.map(i => i.id);
    const params = new URLSearchParams({
        code: this.codigoPromo,
        subtotal: this.subtotal,
        delivery: 0,
    });
    cookieIds.forEach(id => params.append('cookie_ids[]', id));
    const r = await fetch(`/admin/api/promo-codes/validate?${params}`);
    this.promoResult = await r.json();
    if (this.promoResult.valid) {
        this.promoDescuento = this.promoResult.discount_amount ?? 0;
    } else {
        this.promoDescuento = 0;
    }
},

// Método limpiarPromo():
limpiarPromo() {
    this.codigoPromo = '';
    this.promoResult = null;
    this.promoDescuento = 0;
},

// Modificar cobrar() para enviar el código:
// Dentro del body JSON.stringify({...}) añadir:
// promo_code: this.promoResult?.valid ? this.codigoPromo : null,
*/
</script>
