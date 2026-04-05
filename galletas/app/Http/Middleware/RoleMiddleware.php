<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * RoleMiddleware
 *
 * Uso en rutas:
 *   ->middleware('role:admin')       → admin de sucursal O superadmin
 *   ->middleware('role:vendedor')    → cualquier usuario de sucursal (admin o vendedor)
 *   ->middleware('role:superadmin')  → solo superadmin del sistema
 *
 * También verifica que el usuario esté activo.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role = 'vendedor')
    {
        // 1. Debe estar autenticado
        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para continuar.');
        }

        $user = auth()->user();

        // 2. Debe estar activo
        if (! $user->activo) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        }

        // 3. Verificar rol requerido
        $autorizado = match ($role) {
            'superadmin' => $user->isSuperAdmin(),
            'admin'      => $user->isAdmin(),             // admin o superadmin
            'vendedor'   => $user->hasBranch(),           // cualquier usuario de sucursal
            default      => false,
        };

        if (! $autorizado) {
            // Superadmin que intenta entrar a rutas de sucursal
            if ($user->isSuperAdmin() && in_array($role, ['vendedor', 'admin'])) {
                return redirect()->route('superadmin.dashboard')
                    ->with('info', 'El super admin gestiona el sistema desde el panel principal.');
            }

            // Vendedor que intenta acceder a rutas de admin
            if ($user->isVendedor() && $role === 'admin') {
                return redirect()->route('pos.index')
                    ->with('error', 'No tienes permisos para acceder a esa sección.');
            }

            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}
