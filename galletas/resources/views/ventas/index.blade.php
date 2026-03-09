@extends('layouts.app')
@section('title', 'Punto de Venta')

@section('content')
<div x-data="posSystem()" x-cloak class="flex flex-col lg:flex-row gap-6">

    {{-- ════════════════════════════════════════════════ --}}
    {{-- ══ COLUMNA IZQUIERDA — CATÁLOGO DE GALLETAS ══ --}}
    {{-- ════════════════════════════════════════════════ --}}
    <div class="flex-1">

        {{-- Header con tipo de venta --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-cookie-900">
                    <span x-show="saleType === 'individual'">🍪 Venta Individual</span>
                    <span x-show="saleType === 'bowl'">🥣 Armar Bowl de 6</span>
                </h2>
                <p class="text-cookie-500 text-sm mt-1">
                    <span x-show="saleType === 'individual'">Cada galleta a <strong>$10.000</strong></span>
                    <span x-show="saleType === 'bowl'">
                        Selecciona 6 galletas — Total: <strong>$60.000</strong>
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-bold"
                              :class="totalItems === 6 ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                            <span x-text="totalItems"></span>/6
                        </span>
                    </span>
                </p>
            </div>

            {{-- Toggle tipo de venta --}}
            <div class="flex bg-white rounded-xl p-1 shadow-sm border border-cookie-200">
                <button @click="switchType('individual')"
                        :class="saleType === 'individual' ? 'bg-cookie-500 text-white shadow-md' : 'text-cookie-600 hover:bg-cookie-50'"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    🍪 Individual
                </button>
                <button @click="switchType('bowl')"
                        :class="saleType === 'bowl' ? 'bg-cookie-500 text-white shadow-md' : 'text-cookie-600 hover:bg-cookie-50'"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    🥣 Bowl de 6
                </button>
            </div>
        </div>

        {{-- ══ GRID DE TARJETAS DE GALLETAS ══ --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">
            @foreach($products as $product)
                <div @click="addToCart({
                        id: {{ $product->id }},
                        name: '{{ $product->name }}',
                        price: {{ $product->price }},
                        stock: {{ $product->available_stock }},
                        image: '{{ $product->image_url ?? '' }}',
                        color: '{{ $product->color_hex }}'
                     })"
                     class="card-bounce cursor-pointer rounded-2xl overflow-hidden bg-white
                            border-2 transition-all duration-200 select-none"
                     :class="getCartQty({{ $product->id }}) > 0
                             ? 'border-cookie-500 ring-2 ring-cookie-300'
                             : 'border-transparent shadow-md hover:shadow-xl'"
                >

                    {{-- ═══ CONTENEDOR DE IMAGEN ═══ --}}
                    {{-- Aquí irá la foto real de tu galleta --}}
                    <div class="relative aspect-square overflow-hidden bg-gray-100">

                        @if($product->image_url)
                            {{-- ✅ IMAGEN REAL --}}
                            <img src="{{ $product->image_url }}"
                                 alt="Galleta {{ $product->name }}"
                                 class="w-full h-full object-cover transition-transform duration-300
                                        hover:scale-110"
                                 loading="lazy" />
                        @else
                            {{-- 🎨 PLACEHOLDER — Reemplaza con tu imagen --}}
                            {{-- Coloca la imagen en: public/images/galletas/{{ $product->slug }}.jpg --}}
                            <div class="w-full h-full flex flex-col items-center justify-center"
                                 style="background: linear-gradient(135deg, {{ $product->color_hex }}22, {{ $product->color_hex }}44)">
                                <span class="text-5xl mb-2">🍪</span>
                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-white/70 text-gray-600">
                                    Agregar foto
                                </span>
                            </div>
                        @endif

                        {{-- Badge de stock --}}
                        <div class="absolute top-2 right-2">
                            <span class="text-xs font-bold px-2 py-1 rounded-full shadow-sm
                                         {{ $product->available_stock > 5 ? 'bg-green-100 text-green-800' :
                                            ($product->available_stock > 0 ? 'bg-amber-100 text-amber-800' :
                                            'bg-red-100 text-red-800') }}">
                                {{ $product->available_stock > 0 ? $product->available_stock . ' uds' : 'AGOTADA' }}
                            </span>
                        </div>

                        {{-- Badge de cantidad en carrito --}}
                        <div x-show="getCartQty({{ $product->id }}) > 0"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="scale-0"
                             x-transition:enter-end="scale-100"
                             class="absolute top-2 left-2">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full
                                         bg-cookie-500 text-white text-sm font-extrabold shadow-lg">
                                <span x-text="getCartQty({{ $product->id }})"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Info del producto --}}
                    <div class="p-3 text-center">
                        <h3 class="font-bold text-cookie-900 text-sm leading-tight">
                            {{ $product->name }}
                        </h3>
                        <p class="text-cookie-500 text-xs font-semibold mt-1">
                            {{ $product->formatted_price }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════ --}}
    {{-- ══ COLUMNA DERECHA — CARRITO / PEDIDO ══ --}}
    {{-- ═══════════════════════════════════════ --}}
    <div class="w-full lg:w-96 lg:sticky lg:top-24 lg:self-start">
        <div class="bg-white rounded-2xl shadow-xl border border-cookie-200 overflow-hidden">

            {{-- Header carrito --}}
            <div class="bg-gradient-to-r from-cookie-600 to-cookie-500 p-5 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-extrabold flex items-center gap-2">
                        🛒 Pedido Actual
                    </h3>
                    <span x-show="cart.length > 0"
                          class="bg-white/20 px-3 py-1 rounded-full text-sm font-bold">
                        <span x-text="totalItems"></span> galleta<span x-show="totalItems !== 1">s</span>
                    </span>
                </div>
                <p x-show="saleType === 'bowl'" class="text-cookie-100 text-xs mt-1">
                    🥣 Modo Bowl — <span x-text="totalItems"></span>/6 seleccionadas
                </p>
            </div>

            {{-- Lista de items --}}
            <div class="p-4 max-h-64 overflow-y-auto cart-scroll">

                {{-- Carrito vacío --}}
                <div x-show="cart.length === 0" class="text-center py-8">
                    <span class="text-4xl">🍪</span>
                    <p class="text-cookie-400 text-sm mt-2">Toca una galleta para agregarla</p>
                </div>

                {{-- Items --}}
                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div class="flex items-center gap-3 py-3 border-b border-cookie-100 last:border-0
                                animate-slide-up">

                        {{-- Mini avatar --}}
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                             :style="'background:' + item.color + '33'">
                            🍪
                        </div>

                        {{-- Nombre --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-cookie-900 truncate"
                               x-text="item.name"></p>
                            <p class="text-xs text-cookie-400"
                               x-text="'$' + (item.price).toLocaleString('es-CO') + ' c/u'"></p>
                        </div>

                        {{-- Controles de cantidad --}}
                        <div class="flex items-center gap-1">
                            <button @click="decrementItem(index)"
                                    class="w-7 h-7 rounded-lg bg-cookie-100 text-cookie-700
                                           hover:bg-cookie-200 flex items-center justify-center
                                           text-sm font-bold transition">
                                −
                            </button>
                            <span class="w-7 text-center text-sm font-extrabold text-cookie-800"
                                  x-text="item.quantity"></span>
                            <button @click="incrementItem(index)"
                                    class="w-7 h-7 rounded-lg bg-cookie-100 text-cookie-700
                                           hover:bg-cookie-200 flex items-center justify-center
                                           text-sm font-bold transition">
                                +
                            </button>
                        </div>

                        {{-- Subtotal --}}
                        <span class="text-sm font-bold text-cookie-700 w-20 text-right"
                              x-text="'$' + (item.price * item.quantity).toLocaleString('es-CO')">
                        </span>

                        {{-- Eliminar --}}
                        <button @click="removeFromCart(index)"
                                class="text-red-400 hover:text-red-600 transition ml-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- ═══ MÉTODO DE PAGO ═══ --}}
            <div x-show="cart.length > 0" class="px-4 pb-4" x-transition>
                <label class="block text-xs font-semibold text-cookie-600 mb-2 uppercase tracking-wide">
                    Método de Pago
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <button @click="paymentMethod = 'efectivo'"
                            :class="paymentMethod === 'efectivo'
                                    ? 'bg-green-100 border-green-500 text-green-800 ring-2 ring-green-300'
                                    : 'bg-white border-gray-200 text-gray-600 hover:border-green-300'"
                            class="p-3 rounded-xl border-2 text-center transition-all duration-200">
                        <span class="text-xl block">💵</span>
                        <span class="text-xs font-semibold block mt-1">Efectivo</span>
                    </button>
                    <button @click="paymentMethod = 'nequi'"
                            :class="paymentMethod === 'nequi'
                                    ? 'bg-purple-100 border-purple-500 text-purple-800 ring-2 ring-purple-300'
                                    : 'bg-white border-gray-200 text-gray-600 hover:border-purple-300'"
                            class="p-3 rounded-xl border-2 text-center transition-all duration-200">
                        <span class="text-xl block">💜</span>
                        <span class="text-xs font-semibold block mt-1">Nequi</span>
                    </button>
                    <button @click="paymentMethod = 'daviplata'"
                            :class="paymentMethod === 'daviplata'
                                    ? 'bg-orange-100 border-orange-500 text-orange-800 ring-2 ring-orange-300'
                                    : 'bg-white border-gray-200 text-gray-600 hover:border-orange-300'"
                            class="p-3 rounded-xl border-2 text-center transition-all duration-200">
                        <span class="text-xl block">🧡</span>
                        <span class="text-xs font-semibold block mt-1">Daviplata</span>
                    </button>
                </div>
            </div>

            {{-- ═══ TOTAL Y BOTÓN ═══ --}}
            <div x-show="cart.length > 0" class="border-t border-cookie-200 p-4" x-transition>

                {{-- Línea de total --}}
                <div class="flex justify-between items-center mb-4">
                    <span class="text-cookie-600 font-semibold">Total:</span>
                    <span class="text-2xl font-extrabold text-cookie-900"
                          x-text="'$' + totalPrice.toLocaleString('es-CO')">
                    </span>
                </div>

                {{-- Advertencia Bowl --}}
                <div x-show="saleType === 'bowl' && totalItems !== 6"
                     class="mb-3 p-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700 text-center">
                    ⚠️ Necesitas seleccionar exactamente <strong>6 galletas</strong> para el Bowl
                </div>

                {{-- Botón de confirmar --}}
                <button @click="checkout()"
                        :disabled="!canCheckout || processing"
                        :class="canCheckout && !processing
                                ? 'bg-gradient-to-r from-cookie-600 to-cookie-500 hover:from-cookie-700 hover:to-cookie-600 shadow-lg hover:shadow-xl'
                                : 'bg-gray-300 cursor-not-allowed'"
                        class="w-full py-4 rounded-xl text-white font-extrabold text-lg
                               transition-all duration-200 flex items-center justify-center gap-2">
                    <template x-if="processing">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </template>
                    <span x-show="!processing">
                        <span x-show="saleType === 'individual'">🍪 Cobrar</span>
                        <span x-show="saleType === 'bowl'">🥣 Cobrar Bowl</span>
                    </span>
                    <span x-show="processing">Procesando...</span>
                </button>

                {{-- Limpiar --}}
                <button @click="clearCart()"
                        class="w-full mt-2 py-2 text-cookie-400 hover:text-red-500 text-sm font-medium transition">
                    🗑️ Vaciar pedido
                </button>
            </div>
        </div>

        {{-- ═══ ÚLTIMA VENTA ═══ --}}
        <div x-show="lastSale" x-transition class="mt-4">
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center animate-slide-up">
                <span class="text-3xl">✅</span>
                <p class="text-green-800 font-bold mt-1" x-text="lastSale?.message"></p>
                <p class="text-green-600 text-sm" x-text="'Total: ' + lastSale?.total"></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function posSystem() {
    return {
        cart: [],
        paymentMethod: '',
        saleType: 'individual',
        processing: false,
        lastSale: null,

        // Stock local para actualizar en tiempo real sin recargar
        localStock: @json($products->mapWithKeys(fn ($p) => [$p->id => $p->available_stock])),

        // ── Tipo de venta ──
        switchType(type) {
            this.saleType = type;
            this.clearCart();
        },

        // ── Agregar al carrito ──
        addToCart(product) {
            const currentStock = this.localStock[product.id] || 0;
            const inCart = this.getCartQty(product.id);

            if (currentStock <= inCart) {
                this.shake();
                return;
            }

            // En modo Bowl, no pasar de 6
            if (this.saleType === 'bowl' && this.totalItems >= 6) {
                this.shake();
                return;
            }

            const existing = this.cart.find(i => i.product_id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1,
                    max_stock: currentStock,
                    color: product.color,
                });
            }
        },

        // ── Controles de cantidad ──
        incrementItem(index) {
            const item = this.cart[index];
            const currentStock = this.localStock[item.product_id] || 0;

            if (item.quantity >= currentStock) return;
            if (this.saleType === 'bowl' && this.totalItems >= 6) return;

            item.quantity++;
        },

        decrementItem(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
            } else {
                this.removeFromCart(index);
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            this.cart = [];
            this.paymentMethod = '';
        },

        // ── Obtener cantidad de un producto en carrito ──
        getCartQty(productId) {
            const item = this.cart.find(i => i.product_id === productId);
            return item ? item.quantity : 0;
        },

        // ── Cálculos ──
        get totalItems() {
            return this.cart.reduce((sum, i) => sum + i.quantity, 0);
        },

        get totalPrice() {
            if (this.saleType === 'bowl') return 60000;
            return this.cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
        },

        get canCheckout() {
            if (this.cart.length === 0) return false;
            if (!this.paymentMethod) return false;
            if (this.saleType === 'bowl' && this.totalItems !== 6) return false;
            return true;
        },

        // ── Procesar venta ──
        async checkout() {
            if (!this.canCheckout || this.processing) return;

            this.processing = true;
            this.lastSale = null;

            try {
                const response = await fetch('{{ route("ventas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        sale_type: this.saleType,
                        payment_method: this.paymentMethod,
                        items: this.cart.map(i => ({
                            product_id: i.product_id,
                            quantity: i.quantity,
                        })),
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    // Actualizar stock local
                    this.cart.forEach(item => {
                        this.localStock[item.product_id] -= item.quantity;
                    });

                    this.lastSale = data;
                    this.clearCart();

                    // Ocultar el mensaje después de 4 segundos
                    setTimeout(() => { this.lastSale = null; }, 4000);
                } else {
                    alert('❌ ' + (data.message || 'Error al procesar la venta'));
                }
            } catch (error) {
                alert('❌ Error de conexión');
                console.error(error);
            } finally {
                this.processing = false;
            }
        },

        // ── Feedback visual ──
        shake() {
            // Pequeño feedback si no se puede agregar
        }
    };
}
</script>
@endpush