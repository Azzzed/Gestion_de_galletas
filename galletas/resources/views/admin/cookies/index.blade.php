@extends('layouts.app')
@section('title','Galletas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
            <span class="icon icon-lg text-brand-500">bakery_dining</span>
            Inventario de Galletas
        </h1>
        <p class="page-subtitle">Gestión del catálogo y stock</p>
    </div>
    <a href="{{ route('admin.cookies.create') }}" class="btn-primary">
        <span class="icon icon-sm">add</span>
        Nueva Galleta
    </a>
</div>

{{-- Filtros --}}
<div class="card p-4 mb-6 flex flex-wrap gap-3">
    <form method="GET" class="flex flex-wrap gap-3 flex-1">
        <div class="relative flex-1 min-w-44">
            <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">search</span>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar por nombre o relleno…"
                   class="field pl-9 text-sm">
        </div>
        <select name="tamano" class="field text-sm w-auto min-w-[160px]">
            <option value="">Todos los tamaños</option>
            <option value="pequeña"  @selected(request('tamano')==='pequeña')>Pequeña</option>
            <option value="mediana"  @selected(request('tamano')==='mediana')>Mediana</option>
            <option value="grande"   @selected(request('tamano')==='grande')>Grande</option>
        </select>
        <select name="estado" class="field text-sm w-auto min-w-[150px]">
            <option value="">Todos los estados</option>
            <option value="activa"   @selected(request('estado')==='activa')>Activa</option>
            <option value="pausada"  @selected(request('estado')==='pausada')>Pausada</option>
            <option value="inactiva" @selected(request('estado')==='inactiva')>Inactiva</option>
        </select>
        <button type="submit" class="btn-primary py-2">
            <span class="icon icon-sm">filter_list</span>
            Filtrar
        </button>
        @if(request()->hasAny(['buscar','tamano','estado']))
        <a href="{{ route('admin.cookies.index') }}" class="btn-ghost py-2">
            <span class="icon icon-sm">close</span>
        </a>
        @endif
    </form>
</div>

{{-- Grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"
     x-data="{ confirmDelete: null }">

    @forelse($galletas as $galleta)
    <div class="card card-hover overflow-hidden transition-all
                @if(!$galleta->activo) opacity-60 @endif">

        {{-- Imagen --}}
        <div class="aspect-square relative overflow-hidden" style="background:#fbf3e2">
            @if($galleta->imagen_path)
                <img src="{{ Storage::url($galleta->imagen_path) }}" alt="{{ $galleta->nombre }}"
                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <span class="icon icon-2xl text-brand-200">bakery_dining</span>
                </div>
            @endif

            {{-- Badge tamaño --}}
            <span class="absolute top-2 left-2 badge badge-orange text-[10px]">
                {{ $galleta->tamano }}
            </span>

            {{-- Toggle pausar --}}
            <div class="absolute top-2 right-2"
                 x-data="{ pausado: {{ $galleta->pausado ? 'true' : 'false' }} }">
                <button @click="
                    fetch('/admin/cookies/{{ $galleta->id }}/toggle-pausado', {
                        method:'PATCH',
                        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
                    }).then(r=>r.json()).then(d=>{ pausado=d.pausado; })
                "
                :title="pausado ? 'Reanudar venta' : 'Pausar venta'"
                :class="pausado ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-500 hover:bg-green-600'"
                class="w-8 h-8 rounded-full flex items-center justify-center text-white shadow-lg transition-all">
                    <span class="icon icon-sm" x-text="pausado ? 'pause' : 'play_arrow'"></span>
                </button>
            </div>
        </div>

        <div class="p-3">
            <p class="font-bold text-espresso-900 text-sm truncate mb-1">{{ $galleta->nombre }}</p>

            {{-- Rellenos --}}
            <div class="flex flex-wrap gap-1 mb-2 min-h-[20px]">
                @foreach(($galleta->rellenos ?? []) as $tag)
                <span class="text-[10px] bg-brand-50 text-brand-700 border border-brand-100 px-1.5 py-0.5 rounded-full font-medium">{{ $tag }}</span>
                @endforeach
            </div>

            <div class="flex items-center justify-between mb-3">
                <span class="text-brand-600 font-bold text-sm">{{ $galleta->precio_formateado }}</span>
                <span class="text-xs font-semibold flex items-center gap-0.5 {{ $galleta->stock > 5 ? 'text-green-600' : ($galleta->stock > 0 ? 'text-amber-600' : 'text-red-500') }}">
                    <span class="icon icon-sm">inventory</span>
                    {{ $galleta->stock }}
                </span>
            </div>

            <div class="flex gap-1.5">
                <a href="{{ route('admin.cookies.edit', $galleta) }}"
                   class="flex-1 btn-ghost text-center justify-center py-1.5 text-xs rounded-lg">
                    <span class="icon icon-sm">edit</span>
                    Editar
                </a>
                <button @click="confirmDelete = {{ $galleta->id }}"
                        class="btn-danger py-1.5 px-2 text-xs rounded-lg">
                    <span class="icon icon-sm">delete</span>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full py-20 flex flex-col items-center text-espresso-700/30">
        <span class="icon icon-2xl mb-3">bakery_dining</span>
        <p class="text-base font-semibold text-espresso-700/40">No hay galletas registradas</p>
        <a href="{{ route('admin.cookies.create') }}" class="mt-4 btn-primary text-sm">
            <span class="icon icon-sm">add</span>Crear la primera
        </a>
    </div>
    @endforelse

    {{-- Modal confirmación --}}
    <div x-show="confirmDelete !== null" x-cloak x-transition
         @click.self="confirmDelete = null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-6 slide-up text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                <span class="icon icon-xl text-red-500">delete</span>
            </div>
            <h3 class="font-display font-bold text-xl text-espresso-900 mb-2">¿Eliminar galleta?</h3>
            <p class="text-sm text-espresso-700/60 mb-6">Esta acción no se puede deshacer. Si tiene ventas asociadas, no podrá eliminarse.</p>
            <div class="flex gap-3">
                <button @click="confirmDelete = null" class="flex-1 btn-ghost justify-center py-3">
                    Cancelar
                </button>
                <form :action="`/admin/cookies/${confirmDelete}`" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full btn-danger justify-center py-3 rounded-xl font-bold">
                        <span class="icon icon-sm">delete</span> Eliminar
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