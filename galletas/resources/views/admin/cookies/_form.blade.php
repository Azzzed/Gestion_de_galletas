{{-- resources/views/admin/cookies/_form.blade.php --}}
@php $editando = isset($cookie); @endphp

{{-- Errores --}}
@if($errors->any())
<div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-4 rounded-2xl mb-6">
    <span class="icon text-red-500 mt-0.5">error</span>
    <div>
        <p class="font-bold text-sm mb-1">Corrige los siguientes errores:</p>
        <ul class="text-sm space-y-0.5 list-disc pl-4">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<div class="space-y-5">

    {{-- Nombre --}}
    <div>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
            Nombre <span class="text-red-400 normal-case text-sm font-normal">*</span>
        </label>
        <div class="relative">
            <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">label</span>
            <input type="text" name="nombre"
                   value="{{ old('nombre', $editando ? $cookie->nombre : '') }}"
                   placeholder="Ej: Galleta de Avena con Chips de Chocolate"
                   class="field pl-9 @error('nombre') border-red-400 bg-red-50 @enderror">
        </div>
        @error('nombre')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><span class="icon icon-sm">info</span>{{ $message }}</p>@enderror
    </div>

    {{-- Tamaño + Precio --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                Tamaño <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400 z-10">straighten</span>
                <select name="tamano" class="field pl-9">
                    @foreach(['pequeña','mediana','grande'] as $t)
                    <option value="{{ $t }}" @selected(old('tamano', $editando ? $cookie->tamano : 'mediana')===$t)>
                        {{ ucfirst($t) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
                Precio <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <span class="text-brand-500 font-bold absolute left-3 top-1/2 -translate-y-1/2 text-sm">$</span>
                <input type="number" name="precio" min="0" step="100"
                       value="{{ old('precio', $editando ? $cookie->precio : '') }}"
                       placeholder="10000"
                       class="field pl-7 @error('precio') border-red-400 bg-red-50 @enderror">
            </div>
            @error('precio')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Rellenos / Tags --}}
    <div x-data="tagInput('{{ old('rellenos', $editando ? implode(',', $cookie->rellenos ?? []) : '') }}')"
         class="space-y-1.5">
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider">
            Rellenos / Ingredientes
            <span class="normal-case font-normal text-espresso-700/40">— presiona Enter o coma para agregar</span>
        </label>

        <div class="min-h-[46px] flex flex-wrap gap-2 px-3 py-2.5 rounded-xl cursor-text transition-all"
             style="background:#fdfaf4;border:1px solid #eecf93"
             @click="$refs.tagInput.focus()"
             :style="$refs.tagInput === document.activeElement ? 'border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,0.12)' : ''">
            <template x-for="(tag, i) in tags" :key="i">
                <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-800 border border-brand-200 px-3 py-1 rounded-full text-sm font-semibold">
                    <span x-text="tag"></span>
                    <button type="button" @click.stop="removeTag(i)"
                            class="text-brand-400 hover:text-brand-700 leading-none w-4 h-4 flex items-center justify-center rounded-full hover:bg-brand-100">
                        <span class="icon icon-sm" style="font-size:14px">close</span>
                    </button>
                </span>
            </template>
            <input x-ref="tagInput" x-model="inputVal"
                   @keydown.enter.prevent="addTag()"
                   @keydown.comma.prevent="addTag()"
                   @keydown.backspace="inputVal===''&&removeTag(tags.length-1)"
                   placeholder="chocolate, vainilla, fresa…"
                   class="flex-1 min-w-24 bg-transparent outline-none text-sm py-0.5 text-espresso-900 placeholder-espresso-700/30">
        </div>
        <input type="hidden" name="rellenos" :value="tags.join(',')">
    </div>

    {{-- Descripción --}}
    <div>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Descripción</label>
        <div class="relative">
            <span class="icon icon-sm absolute left-3 top-3 text-brand-400">notes</span>
            <textarea name="descripcion" rows="3"
                      placeholder="Ingredientes especiales, alérgenos, características…"
                      class="field pl-9 resize-none">{{ old('descripcion', $editando ? $cookie->descripcion : '') }}</textarea>
        </div>
    </div>

    {{-- Stock --}}
    <div>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">Stock del día</label>
        <div class="relative">
            <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">inventory</span>
            <input type="number" name="stock" min="0"
                   value="{{ old('stock', $editando ? $cookie->stock : 0) }}"
                   class="field pl-9">
        </div>
    </div>

    {{-- Imagen --}}
    <div>
        <label class="block text-xs font-bold text-espresso-700/60 uppercase tracking-wider mb-1.5">
            Imagen
            <span class="normal-case font-normal text-espresso-700/40">— JPG/PNG/WebP, máx 10MB</span>
        </label>
        <div class="flex items-center gap-4 p-4 rounded-2xl border border-dashed border-cream-300" style="background:#fdfaf4">
            {{-- Preview --}}
            <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 border border-cream-200 bg-cream-100 flex items-center justify-center">
                @if($editando && $cookie->imagen_path)
                    <img src="{{ Storage::url($cookie->imagen_path) }}" alt="{{ $cookie->nombre }}"
                         class="w-full h-full object-cover" id="imgPreview">
                @else
                    <span class="icon icon-xl text-brand-200" id="imgPlaceholder">add_photo_alternate</span>
                    <img id="imgPreview" class="w-full h-full object-cover hidden">
                @endif
            </div>

            <div class="flex-1">
                <label for="imagen" class="btn-ghost cursor-pointer text-sm inline-flex">
                    <span class="icon icon-sm">upload</span>
                    {{ $editando && $cookie->imagen_path ? 'Cambiar imagen' : 'Subir imagen' }}
                </label>
                <input type="file" name="imagen" id="imagen" accept="image/*"
                       class="hidden"
                       onchange="
                           const f=this.files[0];
                           if(f){
                               const r=new FileReader();
                               r.onload=e=>{
                                   const p=document.getElementById('imgPreview');
                                   const pl=document.getElementById('imgPlaceholder');
                                   p.src=e.target.result; p.classList.remove('hidden');
                                   if(pl)pl.style.display='none';
                               };
                               r.readAsDataURL(f);
                           }
                       ">
                <p class="text-xs text-espresso-700/40 mt-1.5">Formatos: JPG, PNG, WebP</p>
            </div>
        </div>
        @error('imagen')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><span class="icon icon-sm">info</span>{{ $message }}</p>@enderror
    </div>

    {{-- Toggles: Activo / Pausado --}}
    <div class="grid grid-cols-2 gap-3">
        <label class="flex items-center gap-3 p-4 rounded-xl cursor-pointer transition-all border"
               style="background:#f0fdf4;border-color:#bbf7d0">
            <div class="relative">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" id="activo"
                       class="sr-only peer"
                       {{ old('activo', $editando ? $cookie->activo : true) ? 'checked' : '' }}>
                <div class="w-10 h-5 rounded-full transition-colors bg-green-500 peer-checked:bg-green-500 bg-opacity-30 peer-checked:bg-opacity-100" style="background:#d1d5db">
                </div>
                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
            </div>
            <div>
                <p class="text-sm font-bold text-green-800">Activo</p>
                <p class="text-xs text-green-600">Visible en el catálogo</p>
            </div>
            <span class="icon ml-auto text-green-500">check_circle</span>
        </label>

        <label class="flex items-center gap-3 p-4 rounded-xl cursor-pointer transition-all border"
               style="background:#fffbeb;border-color:#fde68a">
            <div class="relative">
                <input type="hidden" name="pausado" value="0">
                <input type="checkbox" name="pausado" value="1" id="pausado"
                       class="sr-only peer"
                       {{ old('pausado', $editando ? $cookie->pausado : false) ? 'checked' : '' }}>
                <div class="w-10 h-5 rounded-full transition-colors" style="background:#d1d5db">
                </div>
                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
            </div>
            <div>
                <p class="text-sm font-bold text-amber-800">Pausar venta</p>
                <p class="text-xs text-amber-600">Ocultar del POS</p>
            </div>
            <span class="icon ml-auto text-amber-500">pause_circle</span>
        </label>
    </div>

    {{-- Botones --}}
    <div class="flex gap-3 pt-2">
        <a href="{{ route('admin.cookies.index') }}" class="flex-1 btn-ghost justify-center py-3 text-center">
            <span class="icon icon-sm">close</span>Cancelar
        </a>
        <button type="submit" class="flex-1 btn-primary justify-center py-3">
            <span class="icon icon-sm">{{ $editando ? 'save' : 'add' }}</span>
            {{ $editando ? 'Guardar cambios' : 'Crear galleta' }}
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