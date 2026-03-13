
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🍪 @yield('title', 'Galletas Valen Lo Mismo')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cookie: {
                            50:  '#FFF8F0',
                            100: '#FFEDD5',
                            200: '#FED7AA',
                            300: '#FDBA74',
                            400: '#FB923C',
                            500: '#F97316',
                            600: '#EA580C',
                            700: '#C2410C',
                            800: '#9A3412',
                            900: '#7C2D12',
                        }
                    }
                }
            }
        }
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .card-bounce { transition: transform 0.15s ease, box-shadow 0.15s ease; }
        .card-bounce:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
        .card-bounce:active { transform: scale(0.97); }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up { animation: slideUp 0.3s ease forwards; }
        .cart-scroll::-webkit-scrollbar { width: 6px; }
        .cart-scroll::-webkit-scrollbar-track { background: #FFF8F0; border-radius: 3px; }
        .cart-scroll::-webkit-scrollbar-thumb { background: #FDBA74; border-radius: 3px; }
        @keyframes pulse-alert {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .animate-pulse-alert { animation: pulse-alert 2s ease-in-out infinite; }
    </style>
</head>
<body class="bg-cookie-50 min-h-screen">

    <nav class="bg-white/80 backdrop-blur-md border-b border-cookie-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">

                <a href="{{ route('ventas.index') }}" class="flex items-center gap-3">
                    <span class="text-3xl">🍪</span>
                    <div>
                        <h1 class="text-lg font-extrabold text-cookie-800 leading-tight">
                            Galletas Valen Lo Mismo
                        </h1>
                        <p class="text-xs text-cookie-500 -mt-0.5">Todas a $10.000</p>
                    </div>
                </a>

                <div class="flex items-center gap-1">
                    <a href="{{ route('ventas.index') }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('ventas.*') ? 'bg-cookie-500 text-white' : 'text-cookie-700 hover:bg-cookie-100' }}">
                        🛒 Ventas
                    </a>
                    <a href="{{ route('inventario.index') }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('inventario.*') ? 'bg-cookie-500 text-white' : 'text-cookie-700 hover:bg-cookie-100' }}">
                        📦 Inventario
                    </a>
                    <a href="{{ route('deudores.index') }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition relative
                              {{ request()->routeIs('deudores.*') ? 'bg-cookie-500 text-white' : 'text-cookie-700 hover:bg-cookie-100' }}">
                        📋 Deudores
                        @php
                            $pendingCount = \App\Services\JsonStorage::getDebtors()->where('total_pending', '>', 0)->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('dashboard.index') }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('dashboard.*') ? 'bg-cookie-500 text-white' : 'text-cookie-700 hover:bg-cookie-100' }}">
                        📊 Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 rounded-xl text-green-800 text-sm animate-slide-up">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-300 rounded-xl text-red-800 text-sm animate-slide-up">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
