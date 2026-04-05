<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema — Capy Crunch</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        html, body {
            min-height: 100vh;
            background: #08070f;
            font-family: 'DM Sans', sans-serif;
            color: #f1eeff;
            overflow-x: hidden;
        }

        /* ── Fondo animado ────────────────────────────────── */
        .scene {
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 80% 50% at 50% -5%, rgba(124,58,237,0.22) 0%, transparent 65%),
                radial-gradient(ellipse 35% 35% at 85% 85%, rgba(124,58,237,0.07) 0%, transparent 55%),
                #08070f;
            z-index: 0;
        }
        .scene::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(139,92,246,0.18) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: radial-gradient(ellipse 85% 70% at 50% 50%, black 0%, transparent 100%);
        }
        .scene::after {
            content: '';
            position: absolute;
            left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(124,58,237,0.5) 50%, transparent 100%);
            animation: scan 9s ease-in-out infinite;
            top: 0;
        }
        @keyframes scan {
            0%   { top: -2px; opacity: 0; }
            4%   { opacity: 1; }
            96%  { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        /* ── Layout centrado ──────────────────────────────── */
        .page {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 40px 20px;
        }

        /* ── Card principal ───────────────────────────────── */
        .card {
            width: 100%; max-width: 440px;
            background: #13111f;
            border: 1px solid rgba(139,92,246,0.2);
            border-radius: 20px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(124,58,237,0.08),
                0 40px 90px rgba(0,0,0,0.65),
                inset 0 1px 0 rgba(255,255,255,0.06);
            animation: fadeUp 0.5s cubic-bezier(.22,.68,0,1.15) both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform: translateY(22px) scale(0.97); }
            to   { opacity:1; transform: translateY(0) scale(1); }
        }

        /* ── Header ───────────────────────────────────────── */
        .card-header {
            padding: 44px 40px 32px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        /* Shield con anillos */
        .shield-outer {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 140px; height: 140px;
            margin-bottom: 8px;
        }
        .ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(124,58,237,0.25);
            animation: pulse 3s ease-in-out infinite;
        }
        .ring-a { width: 80px;  height: 80px;  animation-delay: 0s; }
        .ring-b { width: 108px; height: 108px; animation-delay: .8s; border-color: rgba(124,58,237,0.14); }
        .ring-c { width: 136px; height: 136px; animation-delay: 1.6s; border-color: rgba(124,58,237,0.07); }
        @keyframes pulse {
            0%,100% { opacity:.7; transform: scale(1); }
            50%      { opacity:1; transform: scale(1.04); }
        }

        .shield-btn {
            position: relative; z-index: 1;
            width: 64px; height: 64px;
            border-radius: 18px;
            background: linear-gradient(145deg, #7c3aed 0%, #4c1d95 100%);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 36px rgba(124,58,237,0.55), 0 8px 20px rgba(0,0,0,0.5);
        }
        .shield-btn .icon {
            font-family: 'Material Symbols Outlined';
            font-size: 30px; color: white;
        }

        .card-title {
            font-family: 'Space Mono', monospace;
            font-size: 22px; font-weight: 700;
            color: #f1eeff;
            letter-spacing: -0.5px;
            margin-top: 20px;
        }
        .card-title em { color: #c4b5fd; font-style: normal; }

        .card-sub {
            font-size: 13px; color: rgba(196,181,253,0.5);
            margin-top: 6px; font-weight: 400;
        }

        .status-pill {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(124,58,237,0.12);
            border: 1px solid rgba(124,58,237,0.28);
            border-radius: 20px;
            padding: 5px 14px;
            font-family: 'Space Mono', monospace;
            font-size: 10px; color: #c4b5fd;
            margin-top: 16px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .dot-live {
            width: 7px; height: 7px;
            background: #34d399; border-radius: 50%;
            box-shadow: 0 0 6px rgba(52,211,153,0.6);
            animation: blink 2s ease-in-out infinite;
        }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.25;} }

        /* ── Body / Formulario ────────────────────────────── */
        .card-body { padding: 32px 40px 36px; }

        /* Alerta error */
        .err-box {
            background: rgba(239,68,68,0.09);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 22px;
            font-size: 13px;
            color: #fca5a5;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .err-box .icon {
            font-family: 'Material Symbols Outlined';
            font-size: 17px; color: #f87171; flex-shrink: 0; margin-top:1px;
        }

        /* Campo */
        .field { margin-bottom: 18px; }
        .field-lbl {
            display: block;
            font-size: 10.5px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.09em;
            color: rgba(196,181,253,0.5);
            margin-bottom: 8px;
        }
        .field-wrap { position: relative; }
        .f-icon {
            font-family: 'Material Symbols Outlined';
            font-size: 17px;
            color: rgba(139,92,246,0.5);
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }
        .f-input {
            width: 100%;
            padding: 13px 14px 13px 43px;
            background: rgba(255,255,255,0.04);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: #f1eeff;
            outline: none;
            transition: all .2s;
        }
        .f-input::placeholder { color: rgba(255,255,255,0.18); }
        .f-input:focus {
            border-color: #7c3aed;
            background: rgba(124,58,237,0.07);
            box-shadow: 0 0 0 3px rgba(124,58,237,0.16);
        }

        /* Remember */
        .remember {
            display: flex; align-items: center; gap: 9px;
            margin-bottom: 22px;
        }
        .remember input { width:15px; height:15px; accent-color:#7c3aed; cursor:pointer; }
        .remember label { font-size:13px; color:rgba(255,255,255,0.3); cursor:pointer; }

        /* Botón */
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            font-family: 'DM Sans', sans-serif;
            font-weight: 700; font-size: 15px;
            border: none; border-radius: 10px;
            cursor: pointer;
            transition: all .2s;
            box-shadow: 0 4px 18px rgba(124,58,237,0.38);
            position: relative; overflow: hidden;
        }
        .btn::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12), transparent);
            opacity: 0; transition: opacity .2s;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(124,58,237,0.52); }
        .btn:hover::after { opacity: 1; }
        .btn:active { transform: translateY(0); }

        /* ── Footer ───────────────────────────────────────── */
        .card-footer {
            padding: 16px 40px 26px;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex; align-items: center; justify-content: space-between;
        }
        .warn-txt {
            display: flex; align-items: center; gap: 7px;
            font-size: 11px; color: rgba(255,255,255,0.2);
        }
        .warn-txt .icon {
            font-family: 'Material Symbols Outlined';
            font-size: 14px; color: rgba(245,158,11,0.45);
        }
        .back-link {
            font-size: 12px; color: rgba(255,255,255,0.22);
            text-decoration: none;
            display: flex; align-items: center; gap: 4px;
            transition: color .2s;
        }
        .back-link .icon {
            font-family: 'Material Symbols Outlined'; font-size: 14px;
        }
        .back-link:hover { color: #c4b5fd; }

        /* ── Firma inferior ───────────────────────────────── */
        .brand-sig {
            margin-top: 28px;
            font-size: 11px; color: rgba(255,255,255,0.12);
            font-family: 'Space Mono', monospace;
            display: flex; align-items: center; gap: 10px;
        }
        .brand-sig::before, .brand-sig::after {
            content: ''; flex: 1; height: 1px;
            background: rgba(124,58,237,0.15);
        }

        @media (max-width: 480px) {
            .card-header { padding: 32px 24px 24px; }
            .card-body   { padding: 24px 24px 30px; }
            .card-footer { padding: 14px 24px 22px; flex-direction: column; gap: 10px; align-items: flex-start; }
        }
    </style>
</head>
<body>

<div class="scene"></div>

<div class="page">
    <div class="card">

        {{-- HEADER --}}
        <div class="card-header">
            <div class="shield-outer">
                <div class="ring ring-a"></div>
                <div class="ring ring-b"></div>
                <div class="ring ring-c"></div>
                <div class="shield-btn">
                    <span class="icon">shield</span>
                </div>
            </div>

            <h1 class="card-title">Panel <em>del Sistema</em></h1>
            <p class="card-sub">Capy Crunch · Gestión Central</p>

            <div>
                <span class="status-pill">
                    <span class="dot-live"></span>
                    Sistema activo
                </span>
            </div>
        </div>

        {{-- BODY --}}
        <div class="card-body">

            @if ($errors->any())
            <div class="err-box">
                <span class="icon">error</span>
                <div>@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
            </div>
            @endif

            @if (session('error'))
            <div class="err-box">
                <span class="icon">error</span>
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('superadmin.login.submit') }}">
                @csrf

                <div class="field">
                    <label class="field-lbl" for="email">Correo electrónico</label>
                    <div class="field-wrap">
                        <span class="f-icon">mail</span>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="admin@capycrunch.com"
                               class="f-input" required autofocus autocomplete="email">
                    </div>
                </div>

                <div class="field">
                    <label class="field-lbl" for="password">Contraseña</label>
                    <div class="field-wrap">
                        <span class="f-icon">lock</span>
                        <input type="password" id="password" name="password"
                               placeholder="••••••••••"
                               class="f-input" required autocomplete="current-password">
                    </div>
                </div>

                <div class="remember">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Mantener sesión activa</label>
                </div>

                <button type="submit" class="btn">Acceder al sistema</button>
            </form>
        </div>

        {{-- FOOTER --}}
        <div class="card-footer">
            <span class="warn-txt">
                <span class="icon">lock</span>
                Área restringida
            </span>
            <a href="{{ route('login') }}" class="back-link">
                <span class="icon">arrow_back</span>
                Acceso sucursales
            </a>
        </div>

    </div>

    <div class="brand-sig">CAPY CRUNCH © {{ date('Y') }}</div>
</div>

</body>
</html>