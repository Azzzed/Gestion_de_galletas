<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Admin Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        body { font-family:'Inter',sans-serif; background:#0f0a1a; color:white; }
        .font-display { font-family:'Playfair Display',serif; }
        .icon { font-family:'Material Symbols Outlined'; font-size:20px; user-select:none; vertical-align:middle; }

        .sidebar {
            width: 240px;
            background: rgba(255,255,255,0.04);
            border-right: 1px solid rgba(255,255,255,0.08);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
        }
        .main-content { margin-left: 240px; padding: 32px; min-height: 100vh; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            margin: 2px 8px;
            transition: all 0.15s;
        }
        .nav-item:hover { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.85); }
        .nav-item.active { background: rgba(124,58,237,0.2); color: white; border-left: 3px solid #8b5cf6; padding-left: 13px; }

        .nav-section {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.2);
            padding: 16px 24px 6px;
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="sidebar flex flex-col">

    {{-- Logo --}}
    <div class="p-6 border-b" style="border-color:rgba(255,255,255,0.08)">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">
                <span class="icon text-white" style="font-size:16px">shield</span>
            </div>
            <div>
                <p class="font-display font-bold text-white text-sm">Capy Crunch</p>
                <p class="text-[10px]" style="color:rgba(255,255,255,0.35)">Sistema</p>
            </div>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 py-4 overflow-y-auto">

        <p class="nav-section">General</p>
        <a href="{{ route('superadmin.dashboard') }}"
           class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
            <span class="icon">dashboard</span> Dashboard
        </a>

        <p class="nav-section">Gestión</p>
        <a href="{{ route('superadmin.branches') }}"
           class="nav-item {{ request()->routeIs('superadmin.branches*') ? 'active' : '' }}">
            <span class="icon">store</span> Sucursales
        </a>
        <a href="{{ route('superadmin.users') }}"
           class="nav-item {{ request()->routeIs('superadmin.users*') ? 'active' : '' }}">
            <span class="icon">group</span> Usuarios
        </a>

        <p class="nav-section">Reportes</p>
        <a href="{{ route('superadmin.ventas') }}"
           class="nav-item {{ request()->routeIs('superadmin.ventas') ? 'active' : '' }}">
            <span class="icon">receipt_long</span> Ventas
        </a>
        <a href="{{ route('superadmin.domicilios') }}"
           class="nav-item {{ request()->routeIs('superadmin.domicilios') ? 'active' : '' }}">
            <span class="icon">local_shipping</span> Domicilios
        </a>
        <a href="{{ route('superadmin.inventario') }}"
           class="nav-item {{ request()->routeIs('superadmin.inventario') ? 'active' : '' }}">
            <span class="icon">inventory_2</span> Inventario
        </a>
    </nav>

    {{-- Usuario + logout --}}
    <div class="p-4 border-t" style="border-color:rgba(255,255,255,0.08)">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm"
                 style="background:rgba(124,58,237,0.3);color:#c4b5fd">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px]" style="color:rgba(255,255,255,0.35)">👑 Super Admin</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item w-full text-left"
                    style="color:rgba(239,68,68,0.7);margin:0;border-radius:8px">
                <span class="icon" style="font-size:16px">logout</span>
                <span style="font-size:13px">Cerrar sesión</span>
            </button>
        </form>
    </div>
</div>

{{-- Main --}}
<main class="main-content">
    @if(session('success'))
    <div class="mb-5 px-4 py-3 rounded-xl text-sm font-medium text-emerald-300 border"
         style="background:rgba(16,185,129,0.1);border-color:rgba(16,185,129,0.3)">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 px-4 py-3 rounded-xl text-sm font-medium text-red-300 border"
         style="background:rgba(239,68,68,0.1);border-color:rgba(239,68,68,0.3)">
        ❌ {{ session('error') }}
    </div>
    @endif

    @yield('content')
</main>

</body>
</html>
