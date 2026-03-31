{{-- ══════════════════════════════
     _form.blade.php — Clientes
══════════════════════════════ --}}
@php $editando = isset($customer); @endphp

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
    <p class="font-semibold mb-1">Corrige los errores:</p>
    <ul class="list-disc pl-4 space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="space-y-5">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Nombre completo <span class="text-red-400">*</span>
        </label>
        <input type="text" name="nombre"
               value="{{ old('nombre', $editando ? $customer->nombre : '') }}"
               placeholder="Ej: Valentina Ríos"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-capy-400
                      @error('nombre') border-red-400 @enderror">
        @error('nombre')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono</label>
            <input type="text" name="telefono"
                   value="{{ old('telefono', $editando ? $customer->telefono : '') }}"
                   placeholder="3001234567"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-capy-400">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
            <input type="email" name="email"
                   value="{{ old('email', $editando ? $customer->email : '') }}"
                   placeholder="correo@ejemplo.com"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-capy-400">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dirección</label>
        <input type="text" name="direccion"
               value="{{ old('direccion', $editando ? $customer->direccion : '') }}"
               placeholder="Cra 15 #45-20"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-capy-400">
    </div>

    <div class="flex gap-3 pt-2">
        <a href="{{ route('admin.customers.index') }}"
           class="flex-1 text-center py-3 rounded-xl border border-gray-200 text-gray-600
                  hover:bg-gray-50 font-medium transition-colors">
            Cancelar
        </a>
        <button type="submit"
                class="flex-1 py-3 bg-capy-600 hover:bg-capy-700 text-white font-bold
                       rounded-xl shadow-md transition-all active:scale-95">
            {{ $editando ? '💾 Guardar cambios' : '✅ Registrar cliente' }}
        </button>
    </div>
</div>
