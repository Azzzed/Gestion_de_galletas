@extends('layouts.app')
@section('title', 'Inventario')

@section('content')
<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold text-cookie-900">📦 Control de Inventario</h2>
        <a href="{{ route('inventario.create') }}"
           class="px-4 py-2 bg-cookie-500 text-white rounded-xl text-sm font-bold hover:bg-cookie-600 transition"
           onclick="return confirm('¿Resetear todo el stock a 20 unidades?')">
            🔄 Resetear Stock
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-md border border-cookie-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-cookie-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-cookie-600 uppercase">Galleta</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-cookie-600 uppercase">Precio</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-cookie-600 uppercase">Stock</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-cookie-600 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cookie-100">
                @foreach($products as $product)
                    <tr class="hover:bg-cookie-50 transition"
                        x-data="{ editing: false, qty: {{ $product->available_stock }} }">

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg"
                                     style="background: {{ $product->color_hex }}33">🍪</div>
                                <span class="font-bold text-cookie-900">{{ $product->name }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center font-semibold text-cookie-700">
                            {{ $product->formatted_price }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            {{-- Modo edición --}}
                            <form method="POST" action="{{ route('inventario.update', $product->id) }}"
                                  x-show="editing" x-transition class="inline-flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input type="number" name="quantity" x-model="qty" min="0"
                                       class="w-20 px-3 py-1 rounded-lg border border-cookie-300 text-center text-sm
                                              focus:ring-cookie-500 focus:border-cookie-500">
                                <button type="submit"
                                        class="px-3 py-1 bg-green-500 text-white rounded-lg text-xs font-bold
                                               hover:bg-green-600 transition">✓</button>
                                <button type="button" @click="editing = false"
                                        class="px-3 py-1 bg-gray-200 text-gray-600 rounded-lg text-xs font-bold
                                               hover:bg-gray-300 transition">✕</button>
                            </form>

                            {{-- Modo lectura --}}
                            <span x-show="!editing"
                                  class="text-lg font-extrabold
                                         {{ $product->available_stock > 5 ? 'text-green-600' :
                                            ($product->available_stock > 0 ? 'text-amber-600' : 'text-red-600') }}">
                                {{ $product->available_stock }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <button @click="editing = !editing"
                                    class="px-4 py-2 bg-cookie-100 text-cookie-700 rounded-lg text-xs font-bold
                                           hover:bg-cookie-200 transition">
                                ✏️ Editar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection