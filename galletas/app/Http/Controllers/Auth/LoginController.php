<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // LOGIN DE SUCURSAL  →  /login
    // ══════════════════════════════════════════════════════════

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Correo o contraseña incorrectos.',
            ])->withInput($request->only('email'));
        }

        $user = Auth::user();

        // Superadmin NO puede usar el login de sucursal
        if ($user->isSuperAdmin()) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Los administradores del sistema usan el acceso especial.',
            ])->withInput($request->only('email'));
        }

        // Usuario debe tener sucursal asignada
        if (! $user->hasBranch()) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Tu cuenta no tiene una sucursal asignada. Contacta al administrador.',
            ])->withInput($request->only('email'));
        }

        // Usuario debe estar activo
        if (! $user->activo) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Tu cuenta está desactivada.',
            ])->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return $this->redirectAfterLogin($user);
    }

    // ══════════════════════════════════════════════════════════
    // LOGIN DE SUPER ADMIN  →  /superadmin/login
    // ══════════════════════════════════════════════════════════

    public function showSuperAdminLogin()
    {
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }
        return view('auth.superadmin-login');
    }

    public function superAdminLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Credenciales incorrectas.'])
                         ->withInput($request->only('email'));
        }

        $user = Auth::user();

        if (! $user->isSuperAdmin()) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Esta área es solo para administradores del sistema.',
            ])->withInput($request->only('email'));
        }

        if (! $user->activo) {
            Auth::logout();
            return back()->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }

        $request->session()->regenerate();
        return redirect()->route('superadmin.dashboard');
    }

    // ══════════════════════════════════════════════════════════
    // LOGOUT
    // ══════════════════════════════════════════════════════════

    public function logout(Request $request)
    {
        $isSuperAdmin = Auth::check() && Auth::user()->isSuperAdmin();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isSuperAdmin ? 'superadmin.login' : 'login')
            ->with('success', 'Sesión cerrada correctamente.');
    }

    // ── Helper ───────────────────────────────────────────────────
    private function redirectAfterLogin($user)
    {
        return match ($user->role) {
            'superadmin' => redirect()->route('superadmin.dashboard'),
            'admin'      => redirect()->route('admin.sales.index'),
            'vendedor'   => redirect()->route('pos.index'),
            default      => redirect()->route('pos.index'),
        };
    }
}
