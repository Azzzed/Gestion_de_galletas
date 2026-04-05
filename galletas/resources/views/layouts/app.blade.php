<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Capy Crunch') — Sistema de Gestión</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#fff8ed', 100: '#ffefd0', 200: '#ffd99e',
                            300: '#ffbc63', 400: '#ff9427', 500: '#f97316',
                            600: '#ea6008', 700: '#c24808', 800: '#9a3a10',
                            900: '#7c3110', 950: '#431606',
                        },
                        cream: {
                            50: '#fdfaf4', 100: '#fbf3e2',
                            200: '#f5e5c0', 300: '#eecf93',
                        },
                        espresso: { 700: '#5c2d0a', 800: '#3b1f0e', 900: '#1a0a00' },
                    },
                    fontFamily: {
                        display: ['Syne', 'sans-serif'],
                        sans:    ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }

        body { background: #fdfaf4; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }

        /* Material Icons */
        .icon {
            font-family: 'Material Symbols Outlined';
            font-weight: normal; font-style: normal;
            font-size: 20px; line-height: 1;
            display: inline-block; white-space: nowrap;
            direction: ltr; -webkit-font-smoothing: antialiased;
            vertical-align: middle;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .icon-fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .icon-sm  { font-size: 16px; }
        .icon-lg  { font-size: 24px; }
        .icon-xl  { font-size: 32px; }
        .icon-2xl { font-size: 48px; }

        /* ── Navbar ───────────────────────────────────────────── */
        .navbar {
            background: linear-gradient(135deg, #1a0a00 0%, #3b1f0e 55%, #c24808 100%);
            border-bottom: 1px solid rgba(234,96,8,0.4);
            box-shadow: 0 2px 20px rgba(0,0,0,0.25), 0 0 40px rgba(234,96,8,0.1);
        }
        .nav-link {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 13px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            color: rgba(255,255,255,0.65);
            transition: all 0.18s ease;
        }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .nav-link.active {
            color: #fff;
            background: rgba(249,115,22,0.3);
            box-shadow: inset 0 0 0 1px rgba(249,115,22,0.4);
        }

        /* Botón de logout en navbar */
        .btn-logout {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 13px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            color: rgba(255, 180, 150, 0.85);
            background: rgba(234, 96, 8, 0.12);
            border: 1px solid rgba(234, 96, 8, 0.25);
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .btn-logout:hover {
            color: #fff;
            background: rgba(220, 60, 10, 0.35);
            border-color: rgba(234, 96, 8, 0.5);
        }

        /* ── Cards ────────────────────────────────────────────── */
        .card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #f5e5c0;
            box-shadow: 0 1px 4px rgba(26,10,0,0.06);
        }

        /* ── Buttons ──────────────────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 20px; border-radius: 12px;
            font-size: 14px; font-weight: 700;
            background: linear-gradient(135deg, #ea6008, #c24808);
            color: #fff;
            border: none; cursor: pointer;
            transition: all 0.18s ease;
            box-shadow: 0 2px 8px rgba(234,96,8,0.25);
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 18px; border-radius: 12px;
            font-size: 14px; font-weight: 600;
            color: #5c2d0a;
            background: transparent;
            border: 1.5px solid #eecf93;
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .btn-ghost:hover { background: #fbf3e2; border-color: #ea6008; color: #ea6008; }

        /* ── Table ────────────────────────────────────────────── */
        .tbl-head th {
            padding: 12px 16px;
            background: #fbf3e2;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #5c2d0a;
            text-align: left;
            border-bottom: 1px solid #f5e5c0;
        }
        .tbl-row td {
            padding: 14px 16px;
            border-bottom: 1px solid #f5e5c0;
            font-size: 13px; color: #3b1f0e;
        }
        .tbl-row:last-child td { border-bottom: none; }
        .tbl-row:hover td { background: #fdfaf4; }

        /* ── Badges ───────────────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
        }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-gray   { background: #f3f4f6; color: #374151; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }

        /* ── Form fields ──────────────────────────────────────── */
        .field {
            width: 100%;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1.5px solid #eecf93;
            background: #fff;
            font-size: 13px; color: #1a0a00;
            transition: border-color 0.15s;
            outline: none;
        }
        .field:focus { border-color: #ea6008; box-shadow: 0 0 0 3px rgba(234,96,8,0.1); }

        /* ── Fade animations ──────────────────────────────────── */
        .fade-in { animation: fadeIn 0.25s ease; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Scrollbar ────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: #fbf3e2; }
        ::-webkit-scrollbar-thumb { background: #f97316; border-radius: 4px; }

        /* ── Page heading ─────────────────────────────────────── */
        .page-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 22px; color: #1a0a00; }
        .page-subtitle { font-size: 13px; color: #9a6a3a; margin-top: 2px; }

        /* ── Role badge en navbar ─────────────────────────────── */
        .role-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .role-badge-superadmin { background: rgba(124,58,237,0.25); color: #c4b5fd; border: 1px solid rgba(124,58,237,0.35); }
        .role-badge-admin      { background: rgba(234,96,8,0.25);   color: #fed7aa; border: 1px solid rgba(234,96,8,0.35); }
        .role-badge-vendedor   { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.15); }
    </style>
    @stack('styles')
</head>

<body class="h-full antialiased">

{{-- ══ NAVBAR ══════════════════════════════════════════════════ --}}
<nav class="navbar sticky top-0 z-50">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="flex items-center justify-between h-14">

            {{-- Brand: logo + nombre + rol --}}
            <a href="{{ route('pos.index') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl overflow-hidden ring-2 ring-white/20 shadow-lg flex-shrink-0">
                    <img src="/images/capy-crunch-logo.jpg" alt="Capy Crunch"
                         class="w-full h-full object-cover"
                         onerror="this.style.display='none';this.parentElement.style.background='#ea6008';this.parentElement.innerHTML+='<span class=\'icon\' style=\'color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;width:100%;height:100%\'>cookie</span>'">
                </div>
                <div class="hidden sm:block leading-tight">
                    <p class="text-white font-display font-bold text-[15px] tracking-tight group-hover:text-brand-300 transition-colors">
                        Capy Crunch
                    </p>
                    {{-- ✅ FIX 4: rol del usuario actual --}}
                    @auth
                        @if(auth()->user()->isSuperAdmin())
                            <span class="role-badge role-badge-superadmin">👑 Super Admin</span>
                        @elseif(auth()->user()->isAdmin())
                            <span class="role-badge role-badge-admin">
                                🏪 Admin · {{ auth()->user()->branch?->nombre ?? 'Sin sucursal' }}
                            </span>
                        @else
                            <span class="role-badge role-badge-vendedor">
                                🛒 Vendedor · {{ auth()->user()->branch?->nombre ?? 'Sin sucursal' }}
                            </span>
                        @endif
                    @endauth
                </div>
            </a>

            {{-- Nav links --}}
            <div class="flex items-center gap-0.5">
                <a href="{{ route('pos.index') }}"
                   class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                    <span class="icon icon-sm">point_of_sale</span>
                    <span class="hidden sm:inline">POS</span>
                </a>
                <a href="{{ route('admin.cookies.index') }}"
                   class="nav-link {{ request()->routeIs('admin.cookies.*') ? 'active' : '' }}">
                    <span class="icon icon-sm">bakery_dining</span>
                    <span class="hidden sm:inline">Galletas</span>
                </a>
                <a href="{{ route('admin.customers.index') }}"
                   class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <span class="icon icon-sm">group</span>
                    <span class="hidden sm:inline">Clientes</span>
                </a>
                <a href="{{ route('admin.sales.index') }}"
                   class="nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                    <span class="icon icon-sm">receipt_long</span>
                    <span class="hidden sm:inline">Ventas</span>
                </a>
                <a href="{{ route('admin.deliveries.index') }}"
                   class="nav-link {{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}">
                    <span class="icon icon-sm">local_shipping</span>
                    <span class="hidden sm:inline">Domicilios</span>
                </a>
                <a href="{{ route('admin.promo-codes.index') }}"
                   class="nav-link {{ request()->routeIs('admin.promo-codes.*') ? 'active' : '' }}">
                    <span class="icon icon-sm">local_offer</span>
                    <span class="hidden sm:inline">Promos</span>
                </a>
                <a href="{{ route('admin.stats.index') }}"
                   class="nav-link {{ request()->routeIs('admin.stats.*') ? 'active' : '' }}">
                    <span class="icon icon-sm">analytics</span>
                    <span class="hidden sm:inline">Estadísticas</span>
                </a>

                {{-- ✅ FIX: botón cerrar sesión con estilo --}}
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <span class="icon icon-sm">logout</span>
                        <span class="hidden sm:inline">Salir</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- ══ FLASH ════════════════════════════════════════════════════ --}}
@if(session('success') || session('error') || session('info'))
<div class="max-w-screen-xl mx-auto px-4 pt-4" id="flash-msg">
    @if(session('success'))
    <div class="fade-in flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-2xl shadow-sm mb-2">
        <span class="icon icon-fill text-green-600">check_circle</span>
        <p class="text-sm font-semibold flex-1">{{ session('success') }}</p>
        <button onclick="this.parentElement.remove()"><span class="icon icon-sm text-green-400 hover:text-green-700">close</span></button>
    </div>
    @endif
    @if(session('error'))
    <div class="fade-in flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl shadow-sm mb-2">
        <span class="icon icon-fill text-red-500">error</span>
        <p class="text-sm font-semibold flex-1">{{ session('error') }}</p>
        <button onclick="this.parentElement.remove()"><span class="icon icon-sm text-red-400 hover:text-red-600">close</span></button>
    </div>
    @endif
    @if(session('info'))
    <div class="fade-in flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-2xl shadow-sm mb-2">
        <span class="icon icon-fill text-blue-500">info</span>
        <p class="text-sm font-semibold flex-1">{{ session('info') }}</p>
        <button onclick="this.parentElement.remove()"><span class="icon icon-sm text-blue-400 hover:text-blue-600">close</span></button>
    </div>
    @endif
</div>
<script>setTimeout(()=>{const e=document.getElementById('flash-msg');if(e){e.style.opacity=0;e.style.transition='opacity .4s';setTimeout(()=>e.remove(),400)}},4000);</script>
@endif

{{-- ══ CONTENT ══════════════════════════════════════════════════ --}}
<main class="@yield('main-class','max-w-screen-xl mx-auto px-4 py-6')">
    @yield('content')
</main>

@stack('scripts')
</body>
</html>