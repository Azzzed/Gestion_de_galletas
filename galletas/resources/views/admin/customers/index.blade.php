@extends('layouts.app')
@section('title','Clientes')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
            <span class="icon icon-lg text-brand-500">group</span>
            Clientes
        </h1>
        <p class="page-subtitle">Gestión de clientes y cuentas</p>
    </div>
    <a href="{{ route('admin.customers.create') }}" class="btn-primary">
        <span class="icon icon-sm">person_add</span>
        Nuevo Cliente
    </a>
</div>

{{-- Filtros --}}
<div class="card p-4 mb-6 flex flex-wrap gap-3">
    <form method="GET" class="flex flex-wrap gap-3 flex-1">
        <div class="relative flex-1 min-w-44">
            <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-brand-400">search</span>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Nombre o teléfono…" class="field pl-9 text-sm">
        </div>
        <select name="estado" class="field text-sm w-auto min-w-[150px]">
            <option value="">Todos</option>
            <option value="activo"   @selected(request('estado')==='activo')>Activos</option>
            <option value="inactivo" @selected(request('estado')==='inactivo')>Inactivos</option>
        </select>
        <button type="submit" class="btn-primary py-2">
            <span class="icon icon-sm">filter_list</span>Filtrar
        </button>
        @if(request()->hasAny(['buscar','estado']))
        <a href="{{ route('admin.customers.index') }}" class="btn-ghost py-2">
            <span class="icon icon-sm">close</span>
        </a>
        @endif
    </form>
</div>

{{-- Tabla --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="tbl-head">
                <tr>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th class="text-center">Compras</th>
                    <th>Deuda pendiente</th>
                    <th>Estado</th>
                    <th class="text-right pr-6">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr class="tbl-row">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                            </div>
                            <p class="font-semibold text-espresso-900">{{ $cliente->nombre }}</p>
                        </div>
                    </td>
                    <td class="text-espresso-700/60 text-sm">{{ $cliente->telefono ?? '—' }}</td>
                    <td class="text-espresso-700/60 text-sm max-w-xs truncate">{{ $cliente->direccion ?? '—' }}</td>
                    <td class="text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-100 text-brand-700 text-sm font-bold">
                            {{ $cliente->sales_count }}
                        </span>
                    </td>
                    <td>
                        @php $saldo = $cliente->saldo_pendiente; @endphp
                        @if($saldo > 0)
                        <span class="badge badge-red">
                            <span class="icon icon-sm">credit_card_off</span>
                            ${{ number_format($saldo, 0, ',', '.') }}
                        </span>
                        @else
                        <span class="badge badge-green">
                            <span class="icon icon-sm">check_circle</span>
                            Al día
                        </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $cliente->activo ? 'badge-green' : 'badge-gray' }}">
                            {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center gap-1 justify-end">
                            <a href="{{ route('admin.customers.show', $cliente) }}"
                               class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-brand-600 hover:bg-brand-50 transition-colors"
                               title="Ver perfil">
                                <span class="icon icon-sm">visibility</span>
                            </a>
                            <a href="{{ route('admin.customers.edit', $cliente) }}"
                               class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-espresso-700 hover:bg-cream-100 transition-colors"
                               title="Editar">
                                <span class="icon icon-sm">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.customers.destroy', $cliente) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ addslashes($cliente->nombre) }}?')">
                                @csrf @method('DELETE')
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-espresso-700/40 hover:text-red-500 hover:bg-red-50 transition-colors"
                                        title="Eliminar">
                                    <span class="icon icon-sm">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-20 text-center">
                        <span class="icon icon-2xl text-espresso-700/20 block mb-3">group</span>
                        <p class="text-sm font-medium text-espresso-700/40">No hay clientes registrados</p>
                        <a href="{{ route('admin.customers.create') }}" class="inline-flex btn-primary mt-4 text-sm">
                            <span class="icon icon-sm">person_add</span>Crear primer cliente
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $clientes->links() }}</div>
@endsection