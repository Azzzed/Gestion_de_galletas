{{-- resources/views/admin/promo_codes/_form.blade.php --}}
@php $editing = isset($promoCode); @endphp

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
    <p class="font-semibold mb-1">Corrige los errores:</p>
    <ul class="list-disc pl-4 space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div x-data="{ tipo: '{{ old('type', $editing ? $promoCode->type : 'percentage') }}' }" class="space-y-5">

    {{-- Código y descripción --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                Código <span class="text-red-400">*</span>
            </label>
            <input type="text" name="code"
                   value="{{ old('code', $editing ? $promoCode->code : '') }}"
                   placeholder="Ej: VERANO20, FREEENVIO"
                   class="field uppercase"
                   style="text-transform:uppercase">
            <p class="text-xs text-espresso-700/40 mt-1">El usuario lo digitará en el POS</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Descripción interna</label>
            <input type="text" name="description"
                   value="{{ old('description', $editing ? $promoCode->description : '') }}"
                   placeholder="Para qué es este código…"
                   class="field">
        </div>
    </div>

    {{-- Tipo de descuento --}}
    <div>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
            Tipo de descuento <span class="text-red-400">*</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach([
                ['percentage',     'percent',        'bg-purple-500', '% sobre el pedido'],
                ['fixed_amount',   'attach_money',   'bg-green-600',  'Monto fijo'],
                ['free_delivery',  'local_shipping',  'bg-blue-600',  'Domicilio gratis'],
                ['cookie_discount','bakery_dining',  'bg-amber-500',  '% en galletas'],
            ] as [$val, $icon, $bg, $label])
            <label class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                   :class="tipo === '{{ $val }}' ? '{{ $bg }} border-transparent text-white' : 'border-cream-200 bg-white text-espresso-700 hover:border-brand-300'">
                <input type="radio" name="type" value="{{ $val }}" x-model="tipo" class="sr-only">
                <span class="icon">{{ $icon }}</span>
                <span class="text-xs font-bold text-center leading-tight">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Valor del descuento (oculto para domicilio gratis) --}}
    <div x-show="tipo !== 'free_delivery'" x-transition>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
            Valor del descuento <span class="text-red-400">*</span>
        </label>
        <div class="relative">
            <span x-text="tipo === 'fixed_amount' ? '$' : '%'"
                  class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-brand-500 text-sm"></span>
            <input type="number" name="discount_value" min="0"
                   value="{{ old('discount_value', $editing ? $promoCode->discount_value : '') }}"
                   :placeholder="tipo === 'fixed_amount' ? '5000' : '20'"
                   class="field pl-8">
        </div>
    </div>

    {{-- Galletas aplicables (solo para cookie_discount) --}}
    <div x-show="tipo === 'cookie_discount'" x-transition>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
            Galletas a las que aplica
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach($cookies as $cookie)
            <label class="flex items-center gap-2 p-2 rounded-lg border border-cream-200 cursor-pointer hover:border-brand-300 transition-all">
                <input type="checkbox" name="applicable_cookie_ids[]" value="{{ $cookie->id }}"
                       {{ in_array($cookie->id, old('applicable_cookie_ids', $editing ? ($promoCode->applicable_cookie_ids ?? []) : [])) ? 'checked' : '' }}
                       class="accent-brand-500">
                <span class="text-sm font-medium text-espresso-800">{{ $cookie->nombre }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Restricciones --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Pedido mínimo ($)</label>
            <input type="number" name="min_order_amount" min="0"
                   value="{{ old('min_order_amount', $editing ? $promoCode->min_order_amount : 0) }}"
                   class="field">
        </div>
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Máximo de usos</label>
            <input type="number" name="max_uses" min="1"
                   value="{{ old('max_uses', $editing ? $promoCode->max_uses : '') }}"
                   placeholder="Vacío = ilimitado"
                   class="field">
        </div>
    </div>

    {{-- Vigencia --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Válido desde</label>
            <input type="date" name="valid_from"
                   value="{{ old('valid_from', $editing ? $promoCode->valid_from?->toDateString() : '') }}"
                   class="field">
        </div>
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Válido hasta</label>
            <input type="date" name="valid_until"
                   value="{{ old('valid_until', $editing ? $promoCode->valid_until?->toDateString() : '') }}"
                   class="field">
        </div>
    </div>

    {{-- Activo --}}
    <label class="flex items-center gap-3 cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $editing ? $promoCode->is_active : true) ? 'checked' : '' }}
               class="w-4 h-4 accent-brand-500">
        <span class="text-sm font-semibold text-espresso-700">Código activo (disponible para usar en el POS)</span>
    </label>

    {{-- Botones --}}
    <div class="flex gap-3 pt-2">
        <a href="{{ route('admin.promo-codes.index') }}" class="flex-1 btn-ghost justify-center py-3 text-center">
            Cancelar
        </a>
        <button type="submit" class="flex-1 btn-primary justify-center py-3">
            <span class="icon icon-sm">{{ $editing ? 'save' : 'add' }}</span>
            {{ $editing ? 'Guardar cambios' : 'Crear código' }}
        </button>
    </div>
</div>
