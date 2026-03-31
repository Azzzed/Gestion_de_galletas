@extends('layouts.app')
@section('title','Galletas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Inventario de Galletas</h1>
        <p class="text-sm text-gray-400 mt-0.5">Gestión del catálogo y stock</p>
    </div>
    <a href="{{ route('admin.cookies.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-capy-600 hover:bg-capy-700
              text-white font-semibold rounded-xl shadow-md transition-all active:scale-95">
        ➕ Nueva Galleta
    </a>
</div>

{{-- Filtros --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <div class="relative flex-1 min-w-44">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Buscar por nombre o relleno…"
               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-capy-400">
    </div>
    <select name="tamano" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-capy-400">
        <option value="">Todos los tamaños</option>
        <option value="pequeña"  @selected(request('tamano')==='pequeña')>🍪 Pequeña</option>
        <option value="mediana"  @selected(request('tamano')==='mediana')>🍪🍪 Mediana</option>
        <option value="grande"   @selected(request('tamano')==='grande')>🍪🍪🍪 Grande</option>
    </select>
    <select name="estado" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-capy-400">
        <option value="">Todos los estados</option>
        <option value="activa"   @selected(request('estado')==='activa')>✅ Activa</option>
        <option value="pausada"  @selected(request('estado')==='pausada')>⏸ Pausada</option>
        <option value="inactiva" @selected(request('estado')==='inactiva')>🚫 Inactiva</option>
    </select>
    <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white rounded-xl text-sm hover:bg-gray-700 transition-colors">
        Filtrar
    </button>
    @if(request()->hasAny(['buscar','tamano','estado']))
    <a href="{{ route('admin.cookies.index') }}"
       class="px-4 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm hover:bg-gray-50">✕</a>
    @endif
</form>

{{-- Grid de galletas --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"
     x-data="{ confirmDelete: null }">

    @forelse($galletas as $galleta)
    <div class="bg-white rounded-2xl overflow-hidden border border-warm-200 shadow-sm
                transition-all hover:-translate-y-0.5 hover:shadow-md
                @if(!$galleta->activo) opacity-60 @endif">

        {{-- Imagen --}}
        <div class="aspect-square bg-warm-100 relative overflow-hidden">
            @if($galleta->imagen_path)
                <img src="{{ Storage::url($galleta->imagen_path) }}" alt="{{ $galleta->nombre }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <span class="text-5xl opacity-30">🍪</span>
                </div>
            @endif

            {{-- Badge tamaño --}}
            <span class="absolute top-2 left-2 text-xs px-2 py-0.5 rounded-full font-medium
                         bg-capy-100 text-capy-700">
                {{ $galleta->tamano }}
            </span>

            {{-- Toggle pausar (switch) --}}
            <div class="absolute top-2 right-2"
                 x-data="{ pausado: {{ $galleta->pausado ? 'true' : 'false' }} }">
                <button @click="
                    fetch('/admin/cookies/{{ $galleta->id }}/toggle-pausado', {
                        method:'PATCH',
                        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
                    }).then(r=>r.json()).then(d=>{ pausado=d.pausado; })
                "
                :title="pausado ? 'Reanudar venta' : 'Pausar venta'"
                :class="pausado ? 'bg-amber-500' : 'bg-green-500'"
                class="w-8 h-8 rounded-full flex items-center justify-center text-white
                       shadow-md transition-colors text-sm font-bold">
                    <span x-text="pausado ? '⏸' : '▶'"></span>
                </button>
            </div>
        </div>

        <div class="p-3">
            <p class="font-bold text-gray-800 truncate text-sm">{{ $galleta->nombre }}</p>

            {{-- Tags de rellenos --}}
            <div class="flex flex-wrap gap-1 my-1.5">
                @foreach(($galleta->rellenos ?? []) as $tag)
                <span class="text-[10px] bg-capy-50 text-capy-600 border border-capy-200
                             px-2 py-0.5 rounded-full font-medium">
                    {{ $tag }}
                </span>
                @endforeach
            </div>

            <div class="flex items-center justify-between mb-3">
                <span class="text-capy-600 font-bold">{{ $galleta->precio_formateado }}</span>
                <span class="text-xs {{ $galleta->stock > 0 ? 'text-green-600' : 'text-red-500' }} font-medium">
                    Stock: {{ $galleta->stock }}
                </span>
            </div>

            <div class="flex gap-1.5">
                <a href="{{ route('admin.cookies.edit', $galleta) }}"
                   class="flex-1 text-center py-1.5 text-xs font-medium rounded-lg
                          bg-warm-100 hover:bg-warm-200 text-gray-700 transition-colors">
                    ✏️ Editar
                </a>

                {{-- Botón eliminar con confirmación Alpine.js --}}
                <button @click="confirmDelete = {{ $galleta->id }}"
                        class="py-1.5 px-2 text-xs rounded-lg bg-red-50 text-red-500
                               hover:bg-red-100 transition-colors">
                    🗑️
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full flex flex-col items-center py-20 text-gray-300">
        <span class="text-6xl mb-4">🍪</span>
        <p class="text-base text-gray-400 font-medium">No hay galletas registradas</p>
        <a href="{{ route('admin.cookies.create') }}" class="mt-3 text-capy-500 hover:underline text-sm">
            Crear la primera →
        </a>
    </div>
    @endforelse

    {{-- ── Modal de confirmación de borrado ── --}}
    <div x-show="confirmDelete !== null" x-cloak
         x-transition
         @click.self="confirmDelete = null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-6 fade-in text-center">
            <div class="text-5xl mb-4">🗑️</div>
            <h3 class="font-bold text-xl text-gray-800 mb-2">¿Eliminar galleta?</h3>
            <p class="text-sm text-gray-500 mb-6">
                Esta acción no se puede deshacer. Si la galleta tiene ventas asociadas,
                no podrá eliminarse.
            </p>
            <div class="flex gap-3">
                <button @click="confirmDelete = null"
                        class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-600
                               hover:bg-gray-50 font-medium transition-colors">
                    Cancelar
                </button>
                <form :action="`/admin/cookies/${confirmDelete}`" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-3 bg-red-500 hover:bg-red-600 text-white font-bold
                                   rounded-xl transition-colors">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-6">{{ $galletas->links() }}</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush
