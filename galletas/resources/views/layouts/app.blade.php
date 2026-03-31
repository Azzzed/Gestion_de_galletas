<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CapyCrunch') — Sistema de Gestión</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        capy: {
                            50:  '#fdf8f3',
                            100: '#faefd9',
                            200: '#f5ddb0',
                            300: '#edc47c',
                            400: '#e4a44a',
                            500: '#d98a2e',
                            600: '#c27020',
                            700: '#a1551b',
                            800: '#83441d',
                            900: '#6c391b',
                            950: '#3c1d0c',
                        },
                        warm: {
                            50:  '#fdfaf6',
                            100: '#f8f1e4',
                            200: '#ecdfc4',
                        }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        [x-cloak]  { display: none !important; }
        * { transition-property: background-color,border-color,color,opacity,transform,box-shadow; transition-duration:150ms; }
        .fade-in   { animation: fadeIn .2s ease-out; }
        .slide-up  { animation: slideUp .25s cubic-bezier(.34,1.56,.64,1); }
        @keyframes fadeIn  { from{opacity:0;transform:translateY(6px)}  to{opacity:1;transform:translateY(0)} }
        @keyframes slideUp { from{opacity:0;transform:scale(.94) translateY(-6px)} to{opacity:1;transform:scale(1) translateY(0)} }
        @keyframes ripple  { to{transform:scale(4);opacity:0} }
        .btn-ripple { position:relative; overflow:hidden; }
        .btn-ripple::after { content:''; position:absolute; border-radius:50%; background:rgba(255,255,255,.3); width:100px; height:100px; margin:auto; top:-50px; left:-50px; pointer-events:none; transform:scale(0); }
        .btn-ripple:active::after { animation:ripple .35s ease-out; }
        ::-webkit-scrollbar { width:5px; height:5px; }
        ::-webkit-scrollbar-track { background:#f8f1e4; }
        ::-webkit-scrollbar-thumb { background:#e4a44a; border-radius:3px; }
    </style>
    @stack('styles')
</head>

<body class="h-full bg-warm-50 font-sans text-gray-800 antialiased">

{{-- Navbar --}}
<nav class="bg-capy-700 shadow-md sticky top-0 z-50">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="flex items-center justify-between h-14">
            <a href="{{ route('pos.index') }}" class="flex items-center gap-2 text-white font-bold text-lg">
                <span class="text-2xl">🐾</span>
                <span class="hidden sm:block tracking-tight">CapyCrunch</span>
            </a>

            <div class="flex items-center gap-1">
                @foreach([
                    ['pos.index',            '🛒', 'POS'],
                    ['admin.cookies.index',  '🍪', 'Galletas'],
                    ['admin.customers.index','👥', 'Clientes'],
                    ['admin.sales.index',    '📋', 'Ventas'],
                ] as [$route, $icon, $label])
                <a href="{{ route($route) }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs(str_replace('.index','',$route).'.*') || request()->routeIs($route)
                             ? 'bg-white/20 text-white'
                             : 'text-white/75 hover:bg-white/15 hover:text-white' }}">
                    <span class="mr-1">{{ $icon }}</span>
                    <span class="hidden sm:inline">{{ $label }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>

{{-- Flash messages --}}
@if(session('success') || session('error'))
<div class="max-w-screen-xl mx-auto px-4 pt-4" id="flash-msg">
    @if(session('success'))
    <div class="fade-in flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-2xl shadow-sm">
        <span class="text-xl">✅</span>
        <p class="text-sm font-medium flex-1">{{ session('success') }}</p>
        <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600 ml-2">✕</button>
    </div>
    @endif
    @if(session('error'))
    <div class="fade-in flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl shadow-sm">
        <span class="text-xl">⚠️</span>
        <p class="text-sm font-medium flex-1">{{ session('error') }}</p>
        <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 ml-2">✕</button>
    </div>
    @endif
</div>
<script>setTimeout(()=>{const e=document.getElementById('flash-msg');if(e){e.style.opacity=0;e.style.transition='opacity .4s';setTimeout(()=>e.remove(),400)}},4000);</script>
@endif

<main class="@yield('main-class','max-w-screen-xl mx-auto px-4 py-6')">
    @yield('content')
</main>

@stack('scripts')
</body>
</html>
