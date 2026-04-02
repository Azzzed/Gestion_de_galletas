@php
$borderColor = match($delivery->status) {
    'scheduled'  => 'border-amber-300',
    'dispatched' => 'border-blue-300',
    'delivered'  => 'border-green-300',
    'cancelled'  => 'border-red-200 opacity-60',
    default      => 'border-cream-200',
};
$payBadgeColor = match($delivery->payment_status) {
    'paid'    => 'bg-green-100 text-green-700',
    'pending' => 'bg-red-100 text-red-700',
    'partial' => 'bg-amber-100 text-amber-700',
    default   => 'bg-gray-100 text-gray-600',
};
@endphp

<div class="bg-white rounded-2xl border-2 {{ $borderColor }} p-4 shadow-sm hover:shadow-md transition-all cursor-pointer"
     @click="openDetail({{ $delivery->id }})">

    {{-- Fila superior --}}
    <div class="flex items-start justify-between mb-2">
        <div class="min-w-0 flex-1">
            <p class="font-bold text-espresso-900 text-sm truncate">
                {{ $delivery->display_name }}
            </p>
            <p class="text-xs text-espresso-700/50 flex items-center gap-1 mt-0.5">
                <span class="icon" style="font-size:12px">phone</span>
                {{ $delivery->display_phone }}
            </p>
        </div>
        <span class="font-display font-bold text-brand-700 flex-shrink-0 ml-2">
            {{ $delivery->total_formatted }}
        </span>
    </div>

    {{-- Dirección --}}
    <p class="text-xs text-espresso-700/60 flex items-start gap-1 mb-2">
        <span class="icon flex-shrink-0" style="font-size:13px">location_on</span>
        <span class="line-clamp-1">{{ $delivery->delivery_address }}</span>
    </p>

    {{-- Items --}}
    <div class="flex flex-wrap gap-1 mb-3">
        @foreach(($delivery->items ?? []) as $item)
            <span class="text-[10px] bg-cream-100 text-espresso-700 px-2 py-0.5 rounded-full border border-cream-200 font-medium">
                {{ $item['cantidad'] }}× {{ $item['nombre'] }}
            </span>
        @endforeach
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between">
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $payBadgeColor }}">
            {{ $delivery->payment_status_label }}
        </span>
        <div class="flex items-center gap-2">
            @if($delivery->delivery_cost == 0 || $delivery->delivery_cost_type === 'free')
                <span class="text-[10px] text-green-600 font-semibold">✓ Envío gratis</span>
            @else
                <span class="text-[10px] text-espresso-700/40">
                    +${{ number_format($delivery->delivery_cost, 0, ',', '.') }} envío
                </span>
            @endif
            <span class="text-[10px] text-espresso-700/40">
                {{ $delivery->created_at->format('H:i') }}
            </span>
        </div>
    </div>

    {{-- Botones de acción rápida (sin Alpine, solo forms POST) --}}
    @if($delivery->status !== 'delivered' && $delivery->status !== 'cancelled')
    <div class="flex gap-1.5 mt-3 pt-2.5 border-t border-cream-200"
         @click.stop>{{-- evitar que el click propague al openDetail --}}

        @if($delivery->status === 'scheduled')
        <form action="{{ route('admin.deliveries.status', $delivery) }}"
              method="POST" class="flex-1">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="dispatched">
            <button type="submit"
                    class="w-full py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-1">
                <span class="icon" style="font-size:13px">local_shipping</span>
                Despachar
            </button>
        </form>

        @elseif($delivery->status === 'dispatched')
        <form action="{{ route('admin.deliveries.status', $delivery) }}"
              method="POST" class="flex-1">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="delivered">
            <button type="submit"
                    class="w-full py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-1">
                <span class="icon" style="font-size:13px">check_circle</span>
                Entregado
            </button>
        </form>
        @endif

    </div>
    @endif

</div>