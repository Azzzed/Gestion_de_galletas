@extends('layouts.app')
@section('title', 'Editar Deudor')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <a href="{{ route('deudores.show', $debtor->id) }}" class="text-cookie-500 hover:text-cookie-700 text-sm">
            ← Volver al perfil
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-md border border-cookie-200 p-6">
        <h2 class="text-xl font-extrabold text-cookie-900 mb-6">✏️ Editar Deudor</h2>

        <form method="POST" action="{{ route('deudores.update', $debtor->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-cookie-700 mb-2">
                    Nombre Completo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" required
                       value="{{ old('name', $debtor->name) }}"
                       class="w-full px-4 py-3 rounded-xl border border-cookie-200 text-cookie-900
                              focus:ring-2 focus:ring-cookie-500 focus:border-cookie-500">
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-cookie-700 mb-2">
                    Teléfono
                </label>
                <input type="tel" name="phone" id="phone"
                       value="{{ old('phone', $debtor->phone) }}"
                       class="w-full px-4 py-3 rounded-xl border border-cookie-200 text-cookie-900
                              focus:ring-2 focus:ring-cookie-500 focus:border-cookie-500">
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold text-cookie-700 mb-2">
                    Notas
                </label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-cookie-200 text-cookie-900
                                 focus:ring-2 focus:ring-cookie-500 focus:border-cookie-500 resize-none">{{ old('notes', $debtor->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="{{ route('deudores.show', $debtor->id) }}"
                   class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-center hover:bg-gray-200 transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex-1 py-3 bg-cookie-500 text-white rounded-xl font-bold hover:bg-cookie-600 transition">
                    💾 Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
