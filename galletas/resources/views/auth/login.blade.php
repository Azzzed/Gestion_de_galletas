<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — Capy Crunch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        .icon { font-family: 'Material Symbols Outlined'; font-size: 20px; user-select: none; }

        .bg-pattern {
            background-color: #1a0a00;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(234,96,8,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(194,72,8,0.10) 0%, transparent 40%),
                radial-gradient(circle at 50% 90%, rgba(255,117,15,0.08) 0%, transparent 40%);
        }

        .cookie-float {
            animation: float 6s ease-in-out infinite;
        }
        .cookie-float:nth-child(2) { animation-delay: 2s; }
        .cookie-float:nth-child(3) { animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33%       { transform: translateY(-12px) rotate(5deg); }
            66%       { transform: translateY(-6px) rotate(-3deg); }
        }

        .input-field {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1.5px solid #e8d5b0;
            border-radius: 12px;
            background: #fdfaf5;
            font-size: 14px;
            color: #1a0a00;
            outline: none;
            transition: all 0.2s;
        }
        .input-field:focus {
            border-color: #ea6008;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(234,96,8,0.1);
        }
        .input-field::placeholder { color: #b8956a; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #ea6008, #c24808);
            color: white;
            font-weight: 700;
            font-size: 15px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.3px;
        }
        .btn-login:hover { background: linear-gradient(135deg, #d45507, #a83d06); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(234,96,8,0.35); }
        .btn-login:active { transform: translateY(0); }

        .card-glass {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.1);
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center p-4">

    {{-- Cookies flotantes decorativas --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <span class="cookie-float absolute text-5xl opacity-10" style="top:10%;left:8%">🍪</span>
        <span class="cookie-float absolute text-3xl opacity-10" style="top:60%;left:5%">🍪</span>
        <span class="cookie-float absolute text-4xl opacity-10" style="top:25%;right:10%">🍪</span>
        <span class="cookie-float absolute text-2xl opacity-10" style="top:70%;right:8%">🍪</span>
        <span class="cookie-float absolute text-5xl opacity-10" style="bottom:15%;left:50%">🍪</span>
    </div>

    <div class="w-full max-w-sm relative z-10">

        {{-- Card principal --}}
        <div class="card-glass p-8">

            {{-- Logo + nombre --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl overflow-hidden mb-4 shadow-xl ring-4 ring-orange-100">
                    <img src="/images/capy-crunch-logo.jpg" alt="Capy Crunch"
                         class="w-full h-full object-cover"
                         onerror="this.parentElement.style.background='#ea6008';this.style.display='none'">
                </div>
                <h1 class="font-display text-3xl text-espresso font-bold" style="color:#1a0a00">Capy Crunch</h1>
                <p class="text-sm mt-1" style="color:#9a7a5a">Sistema de Gestión</p>
            </div>

            {{-- Alerta de error --}}
            @if ($errors->any())
            <div class="mb-5 px-4 py-3 rounded-xl text-sm font-medium text-red-700 bg-red-50 border border-red-200 flex items-start gap-2">
                <span class="icon text-red-500 flex-shrink-0" style="font-size:18px">error</span>
                <div>
                    @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            @if (session('success'))
            <div class="mb-5 px-4 py-3 rounded-xl text-sm font-medium text-green-700 bg-green-50 border border-green-200">
                {{ session('success') }}
            </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div class="relative">
                    <span class="icon absolute left-3 top-1/2 -translate-y-1/2" style="color:#b8956a;font-size:18px">email</span>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="Correo electrónico"
                           class="input-field" required autofocus>
                </div>

                {{-- Password --}}
                <div class="relative">
                    <span class="icon absolute left-3 top-1/2 -translate-y-1/2" style="color:#b8956a;font-size:18px">lock</span>
                    <input type="password" name="password"
                           placeholder="Contraseña"
                           class="input-field" required>
                </div>

                {{-- Recordarme --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 accent-orange-500 rounded">
                    <label for="remember" class="text-sm cursor-pointer" style="color:#6b4c2a">
                        Mantener sesión iniciada
                    </label>
                </div>

                <button type="submit" class="btn-login mt-2">
                    Iniciar sesión
                </button>
            </form>

            {{-- Roles explicación --}}
            <div class="mt-6 pt-5 border-t" style="border-color:#f0e0c0">
                <p class="text-xs text-center mb-3" style="color:#b8956a; font-weight:600; text-transform:uppercase; letter-spacing:0.05em">
                    Roles disponibles
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl px-3 py-2.5 text-center" style="background:#fdf8f0;border:1px solid #f0e0c0">
                        <p class="text-lg mb-0.5">🏪</p>
                        <p class="text-xs font-bold" style="color:#1a0a00">Admin</p>
                        <p class="text-[10px]" style="color:#9a7a5a">Acceso completo</p>
                    </div>
                    <div class="rounded-xl px-3 py-2.5 text-center" style="background:#fdf8f0;border:1px solid #f0e0c0">
                        <p class="text-lg mb-0.5">🛒</p>
                        <p class="text-xs font-bold" style="color:#1a0a00">Vendedor</p>
                        <p class="text-[10px]" style="color:#9a7a5a">POS y ventas</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Link superadmin --}}
        <div class="text-center mt-5">
            <a href="{{ route('superadmin.login') }}"
               class="text-xs font-medium transition-colors"
               style="color:rgba(255,255,255,0.35)"
               onmouseover="this.style.color='rgba(255,255,255,0.7)'"
               onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                Acceso administrador del sistema →
            </a>
        </div>

    </div>
</body>
</html>
