@extends('layouts.app')
@section('title', 'Punto de Venta')

@section('content')
<div x-data="posSystem()" x-cloak class="flex flex-col lg:flex-row gap-6">

    {{-- COLUMNA IZQUIERDA — CATÁLOGO --}}
    <div class="flex-1">
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

        {{-- GRID DE TARJETAS --}}
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
                            border-2 transition-all duration-200 select-none group
                            {{ $product->available_stock == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                     :class="getCartQty({{ $product->id }}) > 0
                             ? 'border-cookie-500 ring-2 ring-cookie-300 shadow-lg'
                             : 'border-transparent shadow-md hover:shadow-xl'">

                    <div class="relative aspect-square overflow-hidden bg-gray-50">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}"
                                 alt="Galleta {{ $product->name }}"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                 loading="lazy" draggable="false" />
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center"
                                 style="background: linear-gradient(135deg, {{ $product->color_hex }}22, {{ $product->color_hex }}44)">
                                <span class="text-5xl mb-2">🍪</span>
                            </div>
                        @endif

                        <div class="absolute top-2 right-2 z-10">
                            @if($product->available_stock > 5)
                                <span class="text-xs font-bold px-2 py-1 rounded-full shadow-sm bg-green-100/90 text-green-800">
                                    {{ $product->available_stock }}
                                </span>
                            @elseif($product->available_stock > 0)
                                <span class="text-xs font-bold px-2 py-1 rounded-full shadow-sm bg-amber-100/90 text-amber-800 animate-pulse">
                                    ¡{{ $product->available_stock }}!
                                </span>
                            @else
                                <span class="text-xs font-bold px-2 py-1 rounded-full shadow-sm bg-red-100/90 text-red-800">
                                    AGOTADA
                                </span>
                            @endif
                        </div>

                        <div x-show="getCartQty({{ $product->id }}) > 0"
                             x-transition class="absolute top-2 left-2 z-10">
                            <span class="flex items-center justify-center w-9 h-9 rounded-full
                                         bg-cookie-500 text-white text-sm font-extrabold shadow-lg ring-2 ring-white">
                                <span x-text="getCartQty({{ $product->id }})"></span>
                            </span>
                        </div>
                    </div>

                    <div class="p-3 text-center border-t border-cookie-100">
                        <h3 class="font-bold text-cookie-900 text-sm leading-tight">{{ $product->name }}</h3>
                        <p class="text-cookie-500 text-xs font-semibold mt-1">{{ $product->formatted_price }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- COLUMNA DERECHA — CARRITO --}}
    <div class="w-full lg:w-96 lg:sticky lg:top-24 lg:self-start">
        <div class="bg-white rounded-2xl shadow-xl border border-cookie-200 overflow-hidden">

            <div class="bg-gradient-to-r from-cookie-600 to-cookie-500 p-5 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-extrabold">🛒 Pedido Actual</h3>
                    <span x-show="cart.length > 0" class="bg-white/20 px-3 py-1 rounded-full text-sm font-bold">
                        <span x-text="totalItems"></span> galleta<span x-show="totalItems !== 1">s</span>
                    </span>
                </div>
            </div>

            <div class="p-4 max-h-72 overflow-y-auto cart-scroll">
                <div x-show="cart.length === 0" class="text-center py-8">
                    <span class="text-5xl block mb-3">🍪</span>
                    <p class="text-cookie-400 text-sm">Toca una galleta para agregarla</p>
                </div>

                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div class="flex items-center gap-3 py-3 border-b border-cookie-100 last:border-0 animate-slide-up">
                        <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 shadow-sm border border-cookie-100">
                            <template x-if="item.image">
                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                            </template>
                            <template x-if="!item.image">
                                <div class="w-full h-full flex items-center justify-center text-lg"
                                     :style="'background:' + item.color + '22'">🍪</div>
                            </template>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-cookie-900 truncate" x-text="item.name"></p>
                            <p class="text-xs text-cookie-400" x-text="'$' + (item.price).toLocaleString('es-CO') + ' c/u'"></p>
                        </div>

                        <div class="flex items-center gap-1">
                            <button @click="decrementItem(index)"
                                    class="w-7 h-7 rounded-lg bg-cookie-100 text-cookie-700 hover:bg-red-100 hover:text-red-600
                                           flex items-center justify-center text-sm font-bold transition">−</button>
                            <span class="w-7 text-center text-sm font-extrabold text-cookie-800" x-text="item.quantity"></span>
                            <button @click="incrementItem(index)"
                                    class="w-7 h-7 rounded-lg bg-cookie-100 text-cookie-700 hover:bg-green-100 hover:text-green-600
                                           flex items-center justify-center text-sm font-bold transition">+</button>
                        </div>

                        <span class="text-sm font-bold text-cookie-700 w-20 text-right"
                              x-text="'$' + (item.price * item.quantity).toLocaleString('es-CO')"></span>

                        <button @click="removeFromCart(index)" class="text-red-300 hover:text-red-600 transition ml-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- MÉTODO DE PAGO --}}
            <div x-show="cart.length > 0 && !isFiado" class="px-4 pb-4" x-transition>
                <label class="block text-xs font-semibold text-cookie-600 mb-2 uppercase tracking-wide">Método de Pago</label>
                <div class="grid grid-cols-3 gap-2">
                    <button @click="paymentMethod = 'efectivo'"
                            :class="paymentMethod === 'efectivo' ? 'bg-green-50 border-green-500 ring-2 ring-green-300' : 'bg-white border-gray-200'"
                            class="p-3 rounded-xl border-2 text-center transition-all duration-200">
                        <span class="text-2xl block">💵</span>
                        <span class="text-xs font-bold block mt-1">Efectivo</span>
                    </button>
                    <button @click="paymentMethod = 'nequi'"
                            :class="paymentMethod === 'nequi' ? 'bg-purple-50 border-purple-500 ring-2 ring-purple-300' : 'bg-white border-gray-200'"
                            class="p-3 rounded-xl border-2 text-center transition-all duration-200">
                        <span class="text-2xl block">💜</span>
                        <span class="text-xs font-bold block mt-1">Nequi</span>
                    </button>
                    <button @click="paymentMethod = 'daviplata'"
                            :class="paymentMethod === 'daviplata' ? 'bg-orange-50 border-orange-500 ring-2 ring-orange-300' : 'bg-white border-gray-200'"
                            class="p-3 rounded-xl border-2 text-center transition-all duration-200">
                        <span class="text-2xl block">🧡</span>
                        <span class="text-xs font-bold block mt-1">Daviplata</span>
                    </button>
                </div>
            </div>

            {{-- TOTAL Y BOTONES --}}
            <div x-show="cart.length > 0" class="border-t border-cookie-200 p-4" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-cookie-600 font-semibold text-lg">Total:</span>
                    <span class="text-3xl font-extrabold text-cookie-900" x-text="'$' + totalPrice.toLocaleString('es-CO')"></span>
                </div>

                <div x-show="saleType === 'bowl' && totalItems !== 6"
                     class="mb-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700 text-center">
                    ⚠️ Necesitas <strong>6 galletas</strong> para el Bowl
                </div>

                {{-- Toggle Fiado --}}
                <div class="mb-4 flex items-center justify-between p-3 bg-cookie-50 rounded-xl">
                    <span class="text-sm font-semibold text-cookie-700">📋 ¿Venta fiada?</span>
                    <button @click="isFiado = !isFiado; if(!isFiado) selectedDebtor = null;"
                            :class="isFiado ? 'bg-amber-500' : 'bg-gray-300'"
                            class="relative w-12 h-6 rounded-full transition-colors">
                        <span :class="isFiado ? 'translate-x-6' : 'translate-x-1'"
                              class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                    </button>
                </div>

                {{-- Selector de deudor --}}
                <div x-show="isFiado" class="mb-4" x-transition>
                    <label class="block text-xs font-semibold text-cookie-600 mb-2 uppercase tracking-wide">Seleccionar Deudor</label>

                    <div x-show="!selectedDebtor" class="space-y-2">
                        <select @change="selectDebtor($event.target.value)" x-ref="debtorSelect"
                                class="w-full px-4 py-3 rounded-xl border border-cookie-200 text-cookie-900
                                       focus:ring-2 focus:ring-cookie-500 focus:border-cookie-500">
                            <option value="">-- Seleccionar deudor --</option>
                            <template x-for="debtor in debtors" :key="debtor.id">
                                <option :value="debtor.id" x-text="debtor.name + (debtor.total_pending > 0 ? ' (Debe: ' + debtor.formatted_pending + ')' : '')"></option>
                            </template>
                        </select>
                        <button @click="showNewDebtorModal = true"
                                class="w-full py-2 text-cookie-500 hover:text-cookie-700 text-sm font-medium">
                            ➕ Crear nuevo deudor
                        </button>
                    </div>

                    <div x-show="selectedDebtor" class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold text-amber-800" x-text="selectedDebtor?.name"></p>
                                <p x-show="selectedDebtor?.total_pending > 0" class="text-xs text-amber-600"
                                   x-text="'Deuda actual: ' + selectedDebtor?.formatted_pending"></p>
                            </div>
                            <button @click="selectedDebtor = null; $refs.debtorSelect.value = ''"
                                    class="text-amber-500 hover:text-amber-700">✕</button>
                        </div>
                    </div>
                </div>

                {{-- Botón Cobrar / Fiar --}}
                <button @click="isFiado ? createDebt() : checkout()"
                        :disabled="!canCheckout || processing"
                        :class="canCheckout && !processing
                                ? (isFiado ? 'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700' : 'bg-gradient-to-r from-cookie-600 to-cookie-500 hover:from-cookie-700 hover:to-cookie-600')
                                : 'bg-gray-300 cursor-not-allowed'"
                        class="w-full py-4 rounded-xl text-white font-extrabold text-lg shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                    <span x-show="!processing">
                        <span x-show="!isFiado && saleType === 'individual'">🍪 Cobrar</span>
                        <span x-show="!isFiado && saleType === 'bowl'">🥣 Cobrar Bowl</span>
                        <span x-show="isFiado">📋 Registrar Fiado</span>
                    </span>
                    <span x-show="processing">Procesando...</span>
                </button>

                <button @click="clearCart()"
                        class="w-full mt-2 py-2 text-cookie-400 hover:text-red-500 text-sm font-medium transition">
                    🗑️ Vaciar pedido
                </button>
            </div>
        </div>

        {{-- Notificación última venta --}}
        <div x-show="lastSale" x-transition class="mt-4">
            <div :class="lastSale?.isFiado ? 'bg-amber-50 border-amber-300' : 'bg-green-50 border-green-300'"
                 class="border-2 rounded-2xl p-5 text-center shadow-lg">
                <span class="text-4xl block" x-text="lastSale?.isFiado ? '📋' : '✅'"></span>
                <p :class="lastSale?.isFiado ? 'text-amber-800' : 'text-green-800'" class="font-extrabold text-lg mt-2" x-text="lastSale?.message"></p>
                <p :class="lastSale?.isFiado ? 'text-amber-600' : 'text-green-600'" class="text-sm font-medium" x-text="'Total: ' + lastSale?.total"></p>
            </div>
        </div>
    </div>

    {{-- MODAL NUEVO DEUDOR --}}
    <div x-show="showNewDebtorModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         x-transition>
        <div @click.away="showNewDebtorModal = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-slide-up">
            <h3 class="text-lg font-extrabold text-cookie-900 mb-4">➕ Nuevo Deudor</h3>

            <form @submit.prevent="createNewDebtor">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-cookie-700 mb-2">Nombre *</label>
                        <input type="text" x-model="newDebtorName" required
                               class="w-full px-4 py-3 rounded-xl border border-cookie-200 focus:ring-2 focus:ring-cookie-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-cookie-700 mb-2">Teléfono</label>
                        <input type="tel" x-model="newDebtorPhone"
                               class="w-full px-4 py-3 rounded-xl border border-cookie-200 focus:ring-2 focus:ring-cookie-500">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showNewDebtorModal = false"
                            class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold">Cancelar</button>
                    <button type="submit" :disabled="!newDebtorName"
                            class="flex-1 py-3 bg-cookie-500 text-white rounded-xl font-bold hover:bg-cookie-600 transition">
                        Guardar
                    </button>
                </div>
            </form>
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
        localStock: @json($products->pluck('available_stock', 'id')),

        // Fiado
        isFiado: false,
        debtors: [],
        selectedDebtor: null,
        showNewDebtorModal: false,
        newDebtorName: '',
        newDebtorPhone: '',

        init() {
            this.loadDebtors();
        },

        async loadDebtors() {
            try {
                const response = await fetch('/api/deudores');
                this.debtors = await response.json();
            } catch (e) {
                console.error('Error loading debtors:', e);
            }
        },

        selectDebtor(id) {
            if (!id) {
                this.selectedDebtor = null;
                return;
            }
            this.selectedDebtor = this.debtors.find(d => d.id == id);
        },

        async createNewDebtor() {
            if (!this.newDebtorName) return;

            try {
                const response = await fetch('{{ route("deudores.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.newDebtorName,
                        phone: this.newDebtorPhone,
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    await this.loadDebtors();
                    this.selectedDebtor = data.debtor;
                    this.showNewDebtorModal = false;
                    this.newDebtorName = '';
                    this.newDebtorPhone = '';
                }
            } catch (e) {
                console.error('Error creating debtor:', e);
            }
        },

        switchType(type) {
            this.saleType = type;
            this.clearCart();
        },

        addToCart(product) {
            if (product.stock === 0) return;
            const currentStock = this.localStock[product.id] || 0;
            const inCart = this.getCartQty(product.id);
            if (currentStock <= inCart) return;
            if (this.saleType === 'bowl' && this.totalItems >= 6) return;

            const existing = this.cart.find(i => i.product_id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1,
                    image: product.image,
                    color: product.color,
                });
            }
        },

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
            this.isFiado = false;
            this.selectedDebtor = null;
        },

        getCartQty(productId) {
            const item = this.cart.find(i => i.product_id === productId);
            return item ? item.quantity : 0;
        },

        get totalItems() {
            return this.cart.reduce((sum, i) => sum + i.quantity, 0);
        },

        get totalPrice() {
            if (this.saleType === 'bowl') return 60000;
            return this.cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
        },

        get canCheckout() {
            if (this.cart.length === 0) return false;
            if (this.saleType === 'bowl' && this.totalItems !== 6) return false;
            if (this.isFiado) {
                return this.selectedDebtor !== null;
            }
            return this.paymentMethod !== '';
        },

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
                        items: this.cart.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    this.cart.forEach(item => { this.localStock[item.product_id] -= item.quantity; });
                    this.lastSale = { ...data, isFiado: false };
                    this.clearCart();
                    setTimeout(() => { this.lastSale = null; }, 4000);
                } else {
                    alert('❌ ' + (data.message || 'Error'));
                }
            } catch (e) {
                alert('❌ Error de conexión');
            } finally {
                this.processing = false;
            }
        },

        async createDebt() {
            if (!this.canCheckout || this.processing) return;
            this.processing = true;
            this.lastSale = null;

            try {
                const response = await fetch('{{ route("api.deudores.debt") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        debtor_id: this.selectedDebtor.id,
                        sale_type: this.saleType,
                        items: this.cart.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    this.cart.forEach(item => { this.localStock[item.product_id] -= item.quantity; });
                    this.lastSale = { ...data, isFiado: true };
                    this.clearCart();
                    this.loadDebtors();
                    setTimeout(() => { this.lastSale = null; }, 4000);
                } else {
                    alert('❌ ' + (data.message || 'Error'));
                }
            } catch (e) {
                alert('❌ Error de conexión');
            } finally {
                this.processing = false;
            }
        },
    };
}
</script>
@endpush
