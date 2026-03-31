<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cookie;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class CookieController extends Controller
{
    public function index(Request $request): View
    {
        $query = Cookie::withTrashed();

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->filled('tamano')) {
            $query->porTamano($request->tamano);
        }

        if ($request->filled('estado')) {
            match ($request->estado) {
                'pausada'  => $query->pausadas(),
                'inactiva' => $query->where('activo', false),
                default    => $query->where('activo', true)->where('pausado', false),
            };
        }

        $galletas = $query->orderBy('nombre')->paginate(16)->withQueryString();

        return view('admin.cookies.index', compact('galletas'));
    }

    public function create(): View
    {
        return view('admin.cookies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
    'nombre'      => 'required|string|max:150',
    'descripcion' => 'nullable|string',
    'precio'      => 'required|numeric|min:0',
    'rellenos'    => 'nullable|string',
    'tamano'      => 'required|in:pequeña,mediana,grande',
    'stock'       => 'integer|min:0',
    'pausado'     => 'boolean',
    'activo'      => 'boolean',
    'imagen'      => 'nullable|image|mimes:jpeg,png,webp|max:10240',
], [
    'imagen.image'    => 'El archivo debe ser una imagen.',
    'imagen.mimes'    => 'Solo se permiten imágenes JPG, PNG o WebP.',
    'imagen.max'      => 'La imagen no puede superar 10MB.',
    'imagen.uploaded' => 'No se pudo subir la imagen. Verifica el tamaño del archivo.',
    'nombre.required' => 'El nombre es obligatorio.',
    'precio.required' => 'El precio es obligatorio.',
    'tamano.required' => 'El tamaño es obligatorio.',
]);

        // Convertir CSV a array
        $datos['rellenos'] = $this->parseTags($request->rellenos);

        if ($request->hasFile('imagen')) {
            $datos['imagen_path'] = $request->file('imagen')->store('galletas', 'public');
        }

        unset($datos['imagen']);
        Cookie::create($datos);

        return redirect()->route('admin.cookies.index')
            ->with('success', 'Galleta creada exitosamente.');
    }

    public function show(Cookie $cookie): View
    {
        return view('admin.cookies.show', compact('cookie'));
    }

    public function edit(Cookie $cookie): View
    {
        return view('admin.cookies.edit', compact('cookie'));
    }

    public function update(Request $request, Cookie $cookie): RedirectResponse
    {
        $datos = $request->validate([
    'nombre'      => 'required|string|max:150',
    'descripcion' => 'nullable|string',
    'precio'      => 'required|numeric|min:0',
    'rellenos'    => 'nullable|string',
    'tamano'      => 'required|in:pequeña,mediana,grande',
    'stock'       => 'integer|min:0',
    'pausado'     => 'boolean',
    'activo'      => 'boolean',
    'imagen'      => 'nullable|image|mimes:jpeg,png,webp|max:10240',
], [
    'imagen.image'    => 'El archivo debe ser una imagen.',
    'imagen.mimes'    => 'Solo se permiten imágenes JPG, PNG o WebP.',
    'imagen.max'      => 'La imagen no puede superar 10MB.',
    'imagen.uploaded' => 'No se pudo subir la imagen. Verifica el tamaño del archivo.',
    'nombre.required' => 'El nombre es obligatorio.',
    'precio.required' => 'El precio es obligatorio.',
    'tamano.required' => 'El tamaño es obligatorio.',
]);
        $datos['rellenos'] = $this->parseTags($request->rellenos);

        if ($request->hasFile('imagen')) {
            if ($cookie->imagen_path) {
                Storage::disk('public')->delete($cookie->imagen_path);
            }
            $datos['imagen_path'] = $request->file('imagen')->store('galletas', 'public');
        }

        unset($datos['imagen']);
        $cookie->update($datos);

        return redirect()->route('admin.cookies.index')
            ->with('success', 'Galleta actualizada.');
    }

    public function destroy(Cookie $cookie): RedirectResponse
    {
        if ($cookie->tieneVentasAsociadas()) {
            return back()->with('error',
                "No se puede eliminar \"{$cookie->nombre}\" porque tiene ventas asociadas. Puedes pausarla en su lugar."
            );
        }

        if ($cookie->imagen_path) {
            Storage::disk('public')->delete($cookie->imagen_path);
        }

        $cookie->delete();

        return redirect()->route('admin.cookies.index')->with('success', 'Galleta eliminada.');
    }

    /** Toggle pausar/reanudar venta en POS */
    public function togglePausado(Cookie $cookie): JsonResponse
    {
        $cookie->update(['pausado' => ! $cookie->pausado]);

        return response()->json([
            'pausado' => $cookie->pausado,
            'mensaje' => $cookie->pausado ? 'Venta pausada' : 'Venta reanudada',
        ]);
    }

    /** Toggle activo/inactivo */
    public function toggleActivo(Cookie $cookie): RedirectResponse
    {
        $cookie->update(['activo' => ! $cookie->activo]);
        return back()->with('success', $cookie->activo ? 'Galleta activada.' : 'Galleta desactivada.');
    }

    private function parseTags(?string $csv): array
    {
        if (empty($csv)) return [];

        return collect(explode(',', $csv))
            ->map(fn ($t) => trim(strtolower($t)))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
