{{-- resources/views/admin/cookies/_form.blade.php --}}
@php $editando = isset($cookie); @endphp

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4">
    <p class="font-semibold text-sm mb-1">Corrige los errores:</p>
    <ul class="text-sm list-disc pl-4 space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="space-y-5">

    {{-- Nombre --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Nombre <span class="text-red-400">*</span>
        </label>
        <input type="text" name="nombre"
               value="{{ old('nombre', $editando ? $cookie->nombre : '') }}"
               placeholder="Ej: Galleta de Avena con Chips"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-capy-400
                      @error('nombre') border-red-400 @enderror">
    </div>

    {{-- Tamaño y Precio --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Tamaño <span class="text-red-400">*</span>
            </label>
            <select name="tamano"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                           focus:outline-none focus:ring-2 focus:ring-capy-400">
                @foreach(['pequeña','mediana','grande'] as $t)
                <option value="{{ $t }}" @selected(old('tamano', $editando ? $cookie->tamano : 'mediana')===$t)>
                    {{ ucfirst($t) }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Precio <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                <input type="number" name="precio" min="0" step="100"
                       value="{{ old('precio', $editando ? $cookie->precio : '') }}"
                       placeholder="3500"
                       class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 text-sm
                              focus:outline-none focus:ring-2 focus:ring-capy-400">
            </div>
        </div>
    </div>

    {{-- Rellenos (tags) --}}
    <div x-data="tagInput('{{ old('rellenos', $editando ? implode(',', $cookie->rellenos ?? []) : '') }}')"
         class="space-y-2">
        <label class="block text-sm font-semibold text-gray-700">
            Rellenos / Ingredientes
            <span class="text-xs font-normal text-gray-400">(presiona Enter o coma para agregar)</span>
        </label>

        {{-- Tags mostrados --}}
        <div class="flex flex-wrap gap-2 p-3 border border-gray-200 rounded-xl min-h-[3rem]
                    bg-warm-50 cursor-text" @click="$refs.tagInput.focus()">
            <template x-for="(tag, i) in tags" :key="i">
                <span class="inline-flex items-center gap-1.5 bg-capy-100 text-capy-700
                             border border-capy-200 px-3 py-1 rounded-full text-sm font-medium">
                    <span x-text="tag"></span>
                    <button type="button" @click="removeTag(i)"
                            class="text-capy-400 hover:text-capy-700 leading-none">×</button>
                </span>
            </template>
            <input x-ref="tagInput" x-model="inputVal"
                   @keydown.enter.prevent="addTag()"
                   @keydown.comma.prevent="addTag()"
                   @keydown.backspace="inputVal===''&&removeTag(tags.length-1)"
                   placeholder="chocolate, vainilla…"
                   class="flex-1 min-w-20 bg-transparent outline-none text-sm py-1">
        </div>

        {{-- Campo oculto con el valor CSV --}}
        <input type="hidden" name="rellenos" :value="tags.join(',')">
    </div>

    {{-- Descripción --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción</label>
        <textarea name="descripcion" rows="3"
                  placeholder="Ingredientes especiales, alérgenos, etc."
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                         focus:outline-none focus:ring-2 focus:ring-capy-400 resize-none">{{ old('descripcion', $editando ? $cookie->descripcion : '') }}</textarea>
    </div>

    {{-- Stock --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stock del día</label>
        <input type="number" name="stock" min="0"
               value="{{ old('stock', $editando ? $cookie->stock : 0) }}"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-capy-400">
    </div>

    {{-- Imagen --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Imagen
            <span class="text-xs font-normal text-gray-400">(JPG/PNG/WebP, máx 2MB)</span>
        </label>
        <div class="flex items-center gap-4">
            @if($editando && $cookie->imagen_path)
            <img src="{{ Storage::url($cookie->imagen_path) }}" alt="Imagen actual"
                 class="w-20 h-20 rounded-xl object-cover border border-warm-200">
            @else
            <div class="w-20 h-20 rounded-xl bg-warm-100 flex items-center justify-center
                        text-4xl text-gray-200 border-2 border-dashed border-warm-200">🍪</div>
            @endif
            <input type="file" name="imagen" accept="image/*"
                   class="block text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                          file:text-sm file:font-medium file:bg-warm-100 file:text-capy-700
                          hover:file:bg-warm-200 cursor-pointer">
        </div>
    </div>

    {{-- Toggles activo / pausado --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="flex items-center gap-3 p-4 bg-warm-50 rounded-xl">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" id="activo" name="activo" value="1"
                   {{ old('activo', $editando ? $cookie->activo : true) ? 'checked' : '' }}
                   class="w-5 h-5 rounded accent-capy-500 cursor-pointer">
            <label for="activo" class="text-sm font-medium text-gray-700 cursor-pointer">
                ✅ Activo
            </label>
        </div>
        <div class="flex items-center gap-3 p-4 bg-amber-50 rounded-xl">
            <input type="hidden" name="pausado" value="0">
            <input type="checkbox" id="pausado" name="pausado" value="1"
                   {{ old('pausado', $editando ? $cookie->pausado : false) ? 'checked' : '' }}
                   class="w-5 h-5 rounded accent-amber-500 cursor-pointer">
            <label for="pausado" class="text-sm font-medium text-gray-700 cursor-pointer">
                ⏸ Pausar venta
            </label>
        </div>
    </div>

    {{-- Botones --}}
    <div class="flex gap-3 pt-2">
        <a href="{{ route('admin.cookies.index') }}"
           class="flex-1 text-center py-3 rounded-xl border border-gray-200 text-gray-600
                  hover:bg-gray-50 font-medium transition-colors">
            Cancelar
        </a>
        <button type="submit"
                class="flex-1 py-3 bg-capy-600 hover:bg-capy-700 text-white font-bold
                       rounded-xl shadow-md transition-all active:scale-95">
            {{ $editando ? '💾 Guardar cambios' : '✅ Crear galleta' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
function tagInput(initial) {
    return {
        tags: initial ? initial.split(',').map(t=>t.trim()).filter(Boolean) : [],
        inputVal: '',
        addTag() {
            const t = this.inputVal.trim().toLowerCase();
            if (t && !this.tags.includes(t)) this.tags.push(t);
            this.inputVal = '';
        },
        removeTag(i) { this.tags.splice(i, 1); }
    };
}
</script>
@endpush
