{{-- ══════════════════════════════════════════════
     ARCHIVO: resources/views/admin/customers/index.blade.php
══════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title','Clientes')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Clientes</h1>
        <p class="text-sm text-gray-400 mt-0.5">Gestión de clientes y deudas</p>
    </div>
    <a href="{{ route('admin.customers.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-capy-600 hover:bg-capy-700
              text-white font-semibold rounded-xl shadow-md transition-all active:scale-95">
        ➕ Nuevo Cliente
    </a>
</div>

{{-- Filtros --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <div class="relative flex-1 min-w-44">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Nombre o teléfono…"
               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-capy-400">
    </div>
    <select name="estado" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-capy-400">
        <option value="">Todos</option>
        <option value="activo"   @selected(request('estado')==='activo')>✅ Activos</option>
        <option value="inactivo" @selected(request('estado')==='inactivo')>⏸ Inactivos</option>
    </select>
    <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white rounded-xl text-sm hover:bg-gray-700 transition-colors">
        Filtrar
    </button>
    @if(request()->hasAny(['buscar','estado']))
    <a href="{{ route('admin.customers.index') }}"
       class="px-4 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm hover:bg-gray-50">✕</a>
    @endif
</form>

{{-- Tabla --}}
<div class="bg-white rounded-2xl border border-warm-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-warm-100">
            <thead class="bg-warm-50">
                <tr>
                    @foreach(['Cliente','Teléfono','Dirección','Compras','Deuda pendiente','Estado',''] as $col)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ $col }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-warm-100">
                @forelse($clientes as $cliente)
                <tr class="hover:bg-warm-50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-capy-100 flex items-center
                                        justify-center text-capy-600 font-bold text-sm shrink-0">
                                {{ strtoupper(substr($cliente->nombre,0,1)) }}
                            </div>
                            <p class="font-semibold text-gray-800 text-sm">{{ $cliente->nombre }}</p>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $cliente->telefono ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 max-w-xs truncate">
                        {{ $cliente->direccion ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="w-8 h-8 rounded-full bg-capy-100 text-capy-700 text-sm
                                     font-bold inline-flex items-center justify-center">
                            {{ $cliente->sales_count }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        @php $saldo = $cliente->saldo_pendiente; @endphp
                        @if($saldo > 0)
                        <span class="text-sm font-bold text-red-600">
                            ${{ number_format($saldo, 0, ',', '.') }}
                        </span>
                        @else
                        <span class="text-sm text-green-600 font-medium">✅ Al día</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $cliente->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-1.5 justify-end">
                            <a href="{{ route('admin.customers.show', $cliente) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-capy-500 hover:bg-capy-50 transition-colors"
                               title="Ver perfil">👁️</a>
                            <a href="{{ route('admin.customers.edit', $cliente) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                               title="Editar">✏️</a>
                            <form method="POST" action="{{ route('admin.customers.destroy', $cliente) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ addslashes($cliente->nombre) }}?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                        title="Eliminar">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <span class="text-5xl block mb-3">👥</span>
                        <p class="text-gray-400">No hay clientes registrados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $clientes->links() }}</div>
@endsection
