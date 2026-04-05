<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Models\Sale;
use App\Models\DeliveryOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════


// ─────────────────────────────────────────────────────────────────────────────
// REEMPLAZA solo el método dashboard() en SuperAdminController.php
// ─────────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        // withoutGlobalScopes() necesario porque superadmin no tiene branch_id
        $branches = Branch::withoutGlobalScopes()
            ->withCount(['users', 'sales', 'deliveryOrders'])
            ->where('activo', true)   // ← true (boolean), no 1
            ->get();

        // KPIs globales
        $kpis = [
            'sucursales'   => Branch::withoutGlobalScopes()->count(),
            'usuarios'     => User::where('role', '!=', 'superadmin')->count(),
            'ventas_hoy'   => Sale::withoutGlobalScopes()
                ->whereDate('created_at', today())
                ->where('estado', 'completada')
                ->count(),
            'ingresos_hoy' => (float) Sale::withoutGlobalScopes()
                ->whereDate('created_at', today())
                ->where('estado', 'completada')
                ->sum('total')
                + (float) DeliveryOrder::withoutGlobalScopes()
                ->whereDate('created_at', today())
                ->whereIn('payment_status', ['paid', 'partial'])
                ->sum('paid_amount'),
        ];

        // Ventas por sucursal (últimos 30 días) — carga simple
        $ventasPorSucursal = Branch::withoutGlobalScopes()
            ->where('activo', true)
            ->get();

        return view('superadmin.dashboard', compact('branches', 'kpis', 'ventasPorSucursal'));
    }

    // ══════════════════════════════════════════════════════════
    // SUCURSALES — CRUD
    // ══════════════════════════════════════════════════════════

    public function branches()
    {
        $branches = Branch::withoutGlobalScopes()
            ->withCount(['users', 'sales', 'customers', 'deliveryOrders'])
            ->latest()
            ->get();

        return view('superadmin.branches.index', compact('branches'));
    }

    public function branchCreate()
    {
        return view('superadmin.branches.create');
    }

    public function branchStore(Request $request)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:150',
            'slug'      => 'nullable|string|max:80|unique:branches,slug',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
            'ciudad'    => 'nullable|string|max:100',
            'color'     => 'nullable|string|max:20',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['nombre']);

        $branch = Branch::create($data);

        // Crear cliente de mostrador para la nueva sucursal
        Customer::withoutGlobalScopes()->create([
            'branch_id' => $branch->id,
            'nombre'    => 'Cliente de Mostrador',
            'activo'    => true,
        ]);

        return redirect()->route('superadmin.branches')
            ->with('success', "Sucursal «{$branch->nombre}» creada exitosamente.");
    }

    public function branchEdit(Branch $branch)
    {
        $branch->load(['users' => fn ($q) => $q->where('role', '!=', 'superadmin')]);
        return view('superadmin.branches.edit', compact('branch'));
    }

    public function branchUpdate(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:150',
            'slug'      => "nullable|string|max:80|unique:branches,slug,{$branch->id}",
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
            'ciudad'    => 'nullable|string|max:100',
            'color'     => 'nullable|string|max:20',
            'activo'    => 'boolean',
        ]);

        $branch->update($data);

        return redirect()->route('superadmin.branches')
            ->with('success', "Sucursal actualizada.");
    }

    public function branchDestroy(Branch $branch)
    {
        // Verificar que no tenga ventas
        if ($branch->sales()->withoutGlobalScopes()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una sucursal con ventas registradas.');
        }

        $branch->delete();
        return redirect()->route('superadmin.branches')
            ->with('success', 'Sucursal eliminada.');
    }

    // ══════════════════════════════════════════════════════════
    // USUARIOS — CRUD (admins de sucursal + vendedores)
    // ══════════════════════════════════════════════════════════

    public function users(Request $request)
    {
        $query = User::with('branch')
            ->where('role', '!=', 'superadmin');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('buscar')) {
            $query->where(fn ($q) =>
                $q->where('name', 'ilike', "%{$request->buscar}%")
                  ->orWhere('email', 'ilike', "%{$request->buscar}%")
            );
        }

        $users    = $query->latest()->paginate(20)->withQueryString();
        $branches = Branch::withoutGlobalScopes()->activas()->get(['id', 'nombre']);

        return view('superadmin.users.index', compact('users', 'branches'));
    }

    public function userCreate()
    {
        $branches = Branch::withoutGlobalScopes()->activas()->get(['id', 'nombre']);
        return view('superadmin.users.create', compact('branches'));
    }

    public function userStore(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
            'role'      => 'required|in:admin,vendedor',
            'branch_id' => 'required|exists:branches,id',
        ], [
            'email.unique'     => 'Este correo ya está registrado.',
            'branch_id.exists' => 'Selecciona una sucursal válida.',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['activo']   = true;

        User::create($data);

        return redirect()->route('superadmin.users')
            ->with('success', "Usuario «{$data['name']}» creado.");
    }

    public function userEdit(User $user)
    {
        abort_if($user->isSuperAdmin(), 403);
        $branches = Branch::withoutGlobalScopes()->activas()->get(['id', 'nombre']);
        return view('superadmin.users.edit', compact('user', 'branches'));
    }

    public function userUpdate(Request $request, User $user)
    {
        abort_if($user->isSuperAdmin(), 403);

        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'email'     => "required|email|unique:users,email,{$user->id}",
            'role'      => 'required|in:admin,vendedor',
            'branch_id' => 'required|exists:branches,id',
            'activo'    => 'boolean',
            'password'  => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('superadmin.users')
            ->with('success', "Usuario actualizado.");
    }

    public function userDestroy(User $user)
    {
        abort_if($user->isSuperAdmin(), 403);
        $user->delete();
        return redirect()->route('superadmin.users')
            ->with('success', 'Usuario eliminado.');
    }

    public function userToggle(User $user)
    {
        abort_if($user->isSuperAdmin(), 403);
        $user->update(['activo' => ! $user->activo]);
        return response()->json(['activo' => $user->activo]);
    }

    // ══════════════════════════════════════════════════════════
    // REPORTES GLOBALES (vista de todas las sucursales)
    // ══════════════════════════════════════════════════════════

    public function ventas(Request $request)
    {
        $query = Sale::withoutGlobalScopes()
            ->with(['customer', 'branch'])
            ->withCount('items');

        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);
        if ($request->filled('desde'))     $query->whereDate('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))     $query->whereDate('created_at', '<=', $request->hasta);

        $ventas   = $query->latest()->paginate(25)->withQueryString();
        $branches = Branch::withoutGlobalScopes()->activas()->get(['id', 'nombre']);

        return view('superadmin.reportes.ventas', compact('ventas', 'branches'));
    }

    public function domicilios(Request $request)
    {
        $query = DeliveryOrder::withoutGlobalScopes()->with(['customer', 'branch']);

        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);
        if ($request->filled('desde'))     $query->whereDate('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))     $query->whereDate('created_at', '<=', $request->hasta);
        if ($request->filled('status'))    $query->where('status', $request->status);

        $domicilios = $query->latest()->paginate(25)->withQueryString();
        $branches   = Branch::withoutGlobalScopes()->activas()->get(['id', 'nombre']);

        return view('superadmin.reportes.domicilios', compact('domicilios', 'branches'));
    }

    public function inventario(Request $request)
    {
        $query = \App\Models\Cookie::withoutGlobalScopes()->with('branch');

        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);

        $cookies  = $query->orderBy('branch_id')->orderBy('nombre')->paginate(30)->withQueryString();
        $branches = Branch::withoutGlobalScopes()->activas()->get(['id', 'nombre']);

        return view('superadmin.reportes.inventario', compact('cookies', 'branches'));
    }
}
