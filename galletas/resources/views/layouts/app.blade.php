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
                            50:  '#fff8ed',
                            100: '#ffefd0',
                            200: '#ffd99e',
                            300: '#ffbc63',
                            400: '#ff9427',
                            500: '#f97316',
                            600: '#ea6008',
                            700: '#c24808',
                            800: '#9a3a10',
                            900: '#7c3110',
                            950: '#431606',
                        },
                        cream: {
                            50:  '#fdfaf4',
                            100: '#fbf3e2',
                            200: '#f5e5c0',
                            300: '#eecf93',
                        },
                        espresso: {
                            700: '#5c2d0a',
                            800: '#3b1f0e',
                            900: '#1a0a00',
                        }
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

        /* Navbar */
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

        /* Cards */
        .card {
            background: #fff; border-radius: 16px;
            border: 1px solid #f5e5c0;
            box-shadow: 0 1px 3px rgba(58,31,14,0.06);
            transition: all 0.2s;
        }
        .card:hover { box-shadow: 0 6px 20px rgba(234,96,8,0.1); border-color: #ffd99e; }

        /* Buttons */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px; border-radius: 10px;
            background: linear-gradient(135deg, #ea6008, #f97316);
            color: #fff; font-weight: 700; font-size: 13px;
            box-shadow: 0 2px 12px rgba(234,96,8,0.3);
            transition: all 0.2s; cursor: pointer;
        }
        .btn-primary:hover { background: linear-gradient(135deg, #c24808, #ea6008); transform: translateY(-1px); box-shadow: 0 4px 18px rgba(234,96,8,0.4); }
        .btn-primary:active { transform: scale(0.97); }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px;
            border: 1px solid #eecf93; background: transparent;
            color: #9a3a10; font-weight: 600; font-size: 13px;
            transition: all 0.2s;
        }
        .btn-ghost:hover { background: #fff8ed; border-color: #f97316; }
        .btn-danger {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px;
            border: 1px solid #fecaca; background: #fef2f2;
            color: #dc2626; font-weight: 600; font-size: 13px;
            transition: all 0.2s;
        }
        .btn-danger:hover { background: #fee2e2; border-color: #f87171; }

        /* Badges */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.02em; }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-amber  { background: #fef9c3; color: #854d0e; }
        .badge-orange { background: #fff7ed; color: #9a3510; border: 1px solid #fed7aa; }
        .badge-gray   { background: #f3f4f6; color: #374151; }

        /* Table */
        .tbl-head th {
            background: #fbf3e2; padding: 11px 16px;
            text-align: left; font-size: 10.5px; font-weight: 700;
            color: #9a3a10; text-transform: uppercase; letter-spacing: 0.07em;
            white-space: nowrap;
        }
        .tbl-row td { padding: 13px 16px; font-size: 13.5px; border-bottom: 1px solid #fbf3e2; color: #3b1f0e; }
        .tbl-row:hover td { background: #fdfaf4; }
        .tbl-row:last-child td { border-bottom: none; }

        /* Input */
        .field {
            width: 100%; padding: 10px 14px; border-radius: 10px;
            border: 1px solid #eecf93; background: #fdfaf4;
            font-size: 13.5px; color: #3b1f0e;
            outline: none; transition: all 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .field:focus { border-color: #f97316; background: #fff; box-shadow: 0 0 0 3px rgba(249,115,22,0.12); }
        .field::placeholder { color: #c9a37c; }

        /* Select */
        select.field { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='%239a3a10'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }

        /* Animations */
        .fade-in  { animation: fadeIn .22s ease-out; }
        .slide-up { animation: slideUp .28s cubic-bezier(.34,1.56,.64,1); }
        @keyframes fadeIn  { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
        @keyframes slideUp { from{opacity:0;transform:scale(.95) translateY(-8px)} to{opacity:1;transform:scale(1) translateY(0)} }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: #fbf3e2; }
        ::-webkit-scrollbar-thumb { background: #f97316; border-radius: 4px; }

        /* Page heading */
        .page-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 22px; color: #1a0a00; }
        .page-subtitle { font-size: 13px; color: #9a6a3a; margin-top: 2px; }
    </style>
    @stack('styles')
</head>

<body class="h-full antialiased">

{{-- ══ NAVBAR ══════════════════════════════════════════════════ --}}
<nav class="navbar sticky top-0 z-50">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="flex items-center justify-between h-14">

            {{-- Brand --}}
            <a href="{{ route('pos.index') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl overflow-hidden ring-2 ring-white/20 shadow-lg flex-shrink-0">
                    <img src="/images/capy-crunch-logo.jpg" alt="Capy Crunch"
                         class="w-full h-full object-cover"
                         onerror="this.style.display='none';this.parentElement.style.background='#ea6008';this.parentElement.innerHTML+='<span class=\'icon\' style=\'color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;width:100%;height:100%\'>cookie</span>'">
                </div>
                <div class="hidden sm:block leading-tight">
                    <p class="text-white font-display font-bold text-[15px] tracking-tight group-hover:text-brand-300 transition-colors">Capy Crunch</p>
                    <p class="text-white/40 text-[10px] tracking-widest uppercase">Gestión</p>
                </div>
            </a>

            {{-- Links --}}
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
            </div>
        </div>
    </div>
</nav>

{{-- ══ FLASH ════════════════════════════════════════════════════ --}}
@if(session('success') || session('error'))
<div class="max-w-screen-xl mx-auto px-4 pt-4" id="flash-msg">
    @if(session('success'))
    <div class="fade-in flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-2xl shadow-sm">
        <span class="icon icon-fill text-green-600">check_circle</span>
        <p class="text-sm font-semibold flex-1">{{ session('success') }}</p>
        <button onclick="this.parentElement.remove()"><span class="icon icon-sm text-green-400 hover:text-green-700">close</span></button>
    </div>
    @endif
    @if(session('error'))
    <div class="fade-in flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl shadow-sm">
        <span class="icon icon-fill text-red-500">error</span>
        <p class="text-sm font-semibold flex-1">{{ session('error') }}</p>
        <button onclick="this.parentElement.remove()"><span class="icon icon-sm text-red-400 hover:text-red-600">close</span></button>
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