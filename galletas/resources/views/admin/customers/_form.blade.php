{{-- ══════════════════════════════
     _form.blade.php — Clientes
══════════════════════════════ --}}
@php $editando = isset($customer); @endphp

{{-- Errores --}}
@if($errors->any())
<div class="mb-6 flex gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4">
    <span class="icon icon-fill text-red-400 flex-shrink-0 mt-0.5">error</span>
    <div>
        <p class="font-bold text-sm mb-1">Corrige los siguientes errores:</p>
        <ul class="text-sm space-y-0.5 list-disc pl-4">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<div class="space-y-6">

    {{-- ── Nombre ───────────────────────────────────────────── --}}
    <div>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
            Nombre completo <span class="text-red-400 normal-case font-normal tracking-normal text-sm">*</span>
        </label>
        <div class="relative">
            <span class="icon icon-sm absolute left-3.5 top-1/2 -translate-y-1/2 text-brand-400">badge</span>
            <input type="text" name="nombre"
                   value="{{ old('nombre', $editando ? $customer->nombre : '') }}"
                   placeholder="Ej: Valentina Ríos"
                   class="field pl-10 @error('nombre') border-red-400 bg-red-50 @enderror"
                   autofocus>
        </div>
        @error('nombre')
        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
            <span class="icon icon-sm">error</span>{{ $message }}
        </p>
        @enderror
    </div>

    {{-- ── Teléfono + Email ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
                Teléfono
            </label>
            <div class="relative">
                <span class="icon icon-sm absolute left-3.5 top-1/2 -translate-y-1/2 text-brand-400">phone</span>
                <input type="text" name="telefono"
                       value="{{ old('telefono', $editando ? $customer->telefono : '') }}"
                       placeholder="3001234567"
                       class="field pl-10">
            </div>
        </div>
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
                Email
            </label>
            <div class="relative">
                <span class="icon icon-sm absolute left-3.5 top-1/2 -translate-y-1/2 text-brand-400">mail</span>
                <input type="email" name="email"
                       value="{{ old('email', $editando ? $customer->email : '') }}"
                       placeholder="correo@ejemplo.com"
                       class="field pl-10 @error('email') border-red-400 bg-red-50 @enderror">
            </div>
            @error('email')
            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                <span class="icon icon-sm">error</span>{{ $message }}
            </p>
            @enderror
        </div>
    </div>

    {{-- ── Dirección ────────────────────────────────────────── --}}
    <div>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-2">
            Dirección
        </label>
        <div class="relative">
            <span class="icon icon-sm absolute left-3.5 top-1/2 -translate-y-1/2 text-brand-400">location_on</span>
            <input type="text" name="direccion"
                   value="{{ old('direccion', $editando ? $customer->direccion : '') }}"
                   placeholder="Cra 15 #45-20"
                   class="field pl-10">
        </div>
    </div>

    {{-- ── Notas / info extra (solo en edición) ────────────── --}}
    @if($editando)
    <div class="rounded-2xl bg-cream-50 border border-cream-200 p-4 flex items-start gap-3">
        <span class="icon icon-sm text-brand-400 flex-shrink-0 mt-0.5">info</span>
        <div class="text-sm text-espresso-700/60 leading-relaxed">
            Cliente registrado el <strong class="text-espresso-900">{{ $customer->created_at->format('d/m/Y') }}</strong>.
            @if($customer->saldo_pendiente > 0)
            Tiene una deuda pendiente de
            <strong class="text-red-600">${{ number_format($customer->saldo_pendiente, 0, ',', '.') }}</strong>.
            @endif
        </div>
    </div>
    @endif

    {{-- ── Divisor ──────────────────────────────────────────── --}}
    <div class="border-t border-cream-200 pt-2"></div>

    {{-- ── Botones ──────────────────────────────────────────── --}}
    <div class="flex gap-3">
        <a href="{{ route('admin.customers.index') }}" class="btn-ghost flex-1 justify-center py-3">
            <span class="icon icon-sm">close</span>
            Cancelar
        </a>
        <button type="submit" class="btn-primary flex-1 justify-center py-3">
            <span class="icon icon-sm">{{ $editando ? 'save' : 'person_add' }}</span>
            {{ $editando ? 'Guardar cambios' : 'Registrar cliente' }}
        </button>
    </div>

</div>