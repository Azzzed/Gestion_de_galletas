@extends('layouts.app')
@section('title', $debtor->name)

@section('content')
<div x-data="debtorDetail()">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('deudores.index') }}" class="text-cookie-500 hover:text-cookie-700 text-sm">
            ← Volver a deudores
        </a>
    </div>

    {{-- Perfil del deudor --}}
    <div class="bg-white rounded-2xl shadow-md border-2 p-6 mb-6
                {{ $debtor->has_alert ? 'border-red-300' : 'border-cookie-200' }}">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold
                            {{ $debtor->has_alert ? 'bg-red-100 text-red-600' : 'bg-cookie-100 text-cookie-600' }}">
                    {{ strtoupper(substr($debtor->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-cookie-900">{{ $debtor->name }}</h2>
                    @if($debtor->phone)
                        <p class="text-cookie-500">📞 {{ $debtor->phone }}</p>
                    @endif
                    @if($debtor->notes)
                        <p class="text-cookie-400 text-sm mt-1">📝 {{ $debtor->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('deudores.edit', $debtor->id) }}"
                   class="px-4 py-2 bg-cookie-100 text-cookie-700 rounded-xl text-sm font-bold hover:bg-cookie-200 transition">
                    ✏️ Editar
                </a>
                @if($debtor->total_pending == 0)
                    <form method="POST" action="{{ route('deudores.destroy', $debtor->id) }}"
                          onsubmit="return confirm('¿Eliminar este deudor permanentemente?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-100 text-red-600 rounded-xl text-sm font-bold hover:bg-red-200 transition">
                            🗑️ Eliminar
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Estadísticas --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
            <div class="bg-cookie-50 rounded-xl p-4 text-center">
                <p class="text-xs text-cookie-500 uppercase tracking-wide">Deuda Total</p>
                <p class="text-2xl font-extrabold {{ $debtor->total_pending > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $debtor->formatted_pending }}
                </p>
            </div>
            <div class="bg-cookie-50 rounded-xl p-4 text-center">
                <p class="text-xs text-cookie-500 uppercase tracking-wide">Compras Fiadas</p>
                <p class="text-2xl font-extrabold text-cookie-700">{{ $debtor->total_purchases }}</p>
            </div>
            <div class="bg-cookie-50 rounded-xl p-4 text-center">
                <p class="text-xs text-cookie-500 uppercase tracking-wide">Pendientes</p>
                <p class="text-2xl font-extrabold text-amber-600">{{ $debtor->pending_purchases }}</p>
            </div>
            <div class="bg-cookie-50 rounded-xl p-4 text-center">
                <p class="text-xs text-cookie-500 uppercase tracking-wide">Cliente desde</p>
                <p class="text-lg font-bold text-cookie-700">{{ $debtor->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Historial de deudas --}}
    <div class="bg-white rounded-2xl shadow-md border border-cookie-200 p-6">
        <h3 class="text-lg font-extrabold text-cookie-900 mb-4">📜 Historial de Compras Fiadas</h3>

        @if($debts->isEmpty())
            <div class="text-center py-8">
                <span class="text-4xl block mb-2">📋</span>
                <p class="text-cookie-400">Este deudor no tiene compras fiadas</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($debts as $debt)
                    <div class="border-2 rounded-xl p-4 transition-all
                                {{ $debt->status === 'paid' ? 'border-green-200 bg-green-50/50' : 
                                   ($debt->status === 'partial' ? 'border-yellow-200 bg-yellow-50/50' : 'border-red-200 bg-red-50/50') }}">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">
                                    {{ $debt->sale_type === 'bowl' ? '🥣' : '🍪' }}
                                </span>
                                <div>
                                    <p class="font-bold text-cookie-900">
                                        {{ $debt->sale_type === 'bowl' ? 'Bowl de 6' : 'Compra Individual' }}
                                    </p>
                                    <p class="text-xs text-cookie-400">
                                        {{ $debt->created_at->format('d/m/Y h:i A') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                             {{ $debt->status === 'paid' ? 'bg-green-100 text-green-700' : 
                                                ($debt->status === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {!! $debt->status_label !!}
                                </span>
                            </div>
                        </div>

                        {{-- Items --}}
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($debt->items as $item)
                                <div class="flex items-center gap-2 bg-white rounded-lg px-3 py-1 border border-cookie-100">
                                    @if($item->product->image_url)
                                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                                             class="w-6 h-6 rounded object-cover">
                                    @endif
                                    <span class="text-sm">{{ $item->quantity }}× {{ $item->product->name }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Montos --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                            <div class="flex items-center gap-4">
                                <span class="text-cookie-500">
                                    Total: <strong class="text-cookie-800">{{ $debt->formatted_total }}</strong>
                                </span>
                                @if($debt->paid_amount > 0)
                                    <span class="text-green-600">
                                        Pagado: <strong>{{ $debt->formatted_paid }}</strong>
                                    </span>
                                @endif
                                @if($debt->remaining > 0)
                                    <span class="text-red-600">
                                        Pendiente: <strong>{{ $debt->formatted_remaining }}</strong>
                                    </span>
                                @endif
                            </div>

                            {{-- Botón de pago --}}
                            @if($debt->status !== 'paid')
                                <button @click="openPaymentModal({{ $debt->id }}, {{ $debt->remaining }}, '{{ $debt->formatted_remaining }}')"
                                        class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-bold hover:bg-green-600 transition">
                                    💰 Registrar Pago
                                </button>
                            @endif
                        </div>

                        {{-- Historial de pagos --}}
                        @if($debt->payments->count() > 0)
                            <div class="mt-3 pt-3 border-t border-cookie-100">
                                <p class="text-xs font-bold text-cookie-500 mb-2">Historial de pagos:</p>
                                <div class="space-y-1">
                                    @foreach($debt->payments as $payment)
                                        <div class="flex items-center justify-between text-xs text-cookie-600 bg-white rounded-lg px-3 py-2">
                                            <span>{{ \Carbon\Carbon::parse($payment['date'])->format('d/m/Y h:i A') }}</span>
                                            <span>
                                                {{ $payment['method'] === 'efectivo' ? '💵' : ($payment['method'] === 'nequi' ? '💜' : '🧡') }}
                                                ${{ number_format($payment['amount'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal de Pago --}}
    <div x-show="showPaymentModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="showPaymentModal = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-slide-up">

            <h3 class="text-lg font-extrabold text-cookie-900 mb-4">💰 Registrar Pago</h3>

            <div class="mb-4 p-4 bg-cookie-50 rounded-xl text-center">
                <p class="text-sm text-cookie-500">Saldo pendiente:</p>
                <p class="text-2xl font-extrabold text-red-600" x-text="paymentMaxFormatted"></p>
            </div>

            <form @submit.prevent="submitPayment">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-cookie-700 mb-2">Monto a pagar</label>
                    <div class="flex gap-2">
                        <input type="number" x-model="paymentAmount" :max="paymentMax" min="1000" step="1000"
                               class="flex-1 px-4 py-3 rounded-xl border border-cookie-200 text-lg font-bold text-center
                                      focus:ring-2 focus:ring-cookie-500 focus:border-cookie-500">
                        <button type="button" @click="paymentAmount = paymentMax"
                                class="px-4 py-2 bg-cookie-100 text-cookie-700 rounded-xl text-sm font-bold hover:bg-cookie-200">
                            Todo
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-cookie-700 mb-2">Método de pago</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="paymentMethod = 'efectivo'"
                                :class="paymentMethod === 'efectivo' ? 'ring-2 ring-green-500 bg-green-50' : 'bg-white'"
                                class="p-3 rounded-xl border-2 border-gray-200 text-center transition">
                            <span class="text-2xl block">💵</span>
                            <span class="text-xs font-bold">Efectivo</span>
                        </button>
                        <button type="button" @click="paymentMethod = 'nequi'"
                                :class="paymentMethod === 'nequi' ? 'ring-2 ring-purple-500 bg-purple-50' : 'bg-white'"
                                class="p-3 rounded-xl border-2 border-gray-200 text-center transition">
                            <span class="text-2xl block">💜</span>
                            <span class="text-xs font-bold">Nequi</span>
                        </button>
                        <button type="button" @click="paymentMethod = 'daviplata'"
                                :class="paymentMethod === 'daviplata' ? 'ring-2 ring-orange-500 bg-orange-50' : 'bg-white'"
                                class="p-3 rounded-xl border-2 border-gray-200 text-center transition">
                            <span class="text-2xl block">🧡</span>
                            <span class="text-xs font-bold">Daviplata</span>
                        </button>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showPaymentModal = false"
                            class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="!canSubmitPayment || processing"
                            :class="canSubmitPayment && !processing ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-300 cursor-not-allowed'"
                            class="flex-1 py-3 text-white rounded-xl font-bold transition flex items-center justify-center gap-2">
                        <span x-show="!processing">✅ Confirmar Pago</span>
                        <span x-show="processing">Procesando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Notificación de éxito --}}
    <div x-show="successMessage" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-6 right-6 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-2xl z-50">
        <p class="font-bold" x-text="successMessage"></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function debtorDetail() {
    return {
        showPaymentModal: false,
        paymentDebtId: null,
        paymentMax: 0,
        paymentMaxFormatted: '',
        paymentAmount: 0,
        paymentMethod: '',
        processing: false,
        successMessage: '',

        openPaymentModal(debtId, max, maxFormatted) {
            this.paymentDebtId = debtId;
            this.paymentMax = max;
            this.paymentMaxFormatted = maxFormatted;
            this.paymentAmount = max;
            this.paymentMethod = '';
            this.showPaymentModal = true;
        },

        get canSubmitPayment() {
            return this.paymentAmount > 0 && 
                   this.paymentAmount <= this.paymentMax && 
                   this.paymentMethod !== '';
        },

        async submitPayment() {
            if (!this.canSubmitPayment || this.processing) return;

            this.processing = true;

            try {
                const response = await fetch(`/api/deudas/${this.paymentDebtId}/payment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: parseInt(this.paymentAmount),
                        payment_method: this.paymentMethod,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.showPaymentModal = false;
                    this.successMessage = data.message;
                    setTimeout(() => {
                        this.successMessage = '';
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('❌ ' + (data.message || 'Error al procesar el pago'));
                }
            } catch (error) {
                alert('❌ Error de conexión');
                console.error(error);
            } finally {
                this.processing = false;
            }
        }
    };
}
</script>
@endpush
