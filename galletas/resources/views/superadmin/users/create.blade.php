@extends('layouts.superadmin')
@section('title', 'Nuevo Usuario')

@section('content')

<div style="max-width:560px">

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px">
        <a href="{{ route('superadmin.users') }}"
           style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;text-decoration:none;color:rgba(255,255,255,0.5)"
           onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
            <span class="icon" style="font-size:18px">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:#f1eeff">Nuevo Usuario</h1>
            <p style="color:rgba(196,181,253,0.4);font-size:13px;margin-top:2px">Crear admin o vendedor para una sucursal</p>
        </div>
    </div>

    <div style="background:#13111f;border:1px solid rgba(139,92,246,0.18);border-radius:16px;overflow:hidden">
        <div style="height:3px;background:linear-gradient(90deg,#7c3aed,#a78bfa,#7c3aed)"></div>
        <div style="padding:32px">

            @if($errors->any())
            <div style="background:rgba(239,68,68,0.09);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:14px 16px;margin-bottom:24px;font-size:13px;color:#fca5a5">
                @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('superadmin.users.store') }}">
                @csrf

                @php $fieldStyle = "width:100%;padding:12px 14px;background:rgba(255,255,255,0.04);border:1.5px solid rgba(255,255,255,0.1);border-radius:10px;font-size:14px;color:#f1eeff;outline:none;font-family:'DM Sans',sans-serif"; @endphp
                @php $labelStyle = "display:block;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5);margin-bottom:8px"; @endphp

                <div style="display:grid;gap:16px">
                    <div>
                        <label style="{{ $labelStyle }}">Nombre completo <span style="color:#f87171">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej: María García" required
                               style="{{ $fieldStyle }}"
                               onfocus="this.style.borderColor='#7c3aed';this.style.boxShadow='0 0 0 3px rgba(124,58,237,0.15)'"
                               onblur="this.style.borderColor='rgba(255,255,255,0.1)';this.style.boxShadow='none'">
                    </div>

                    <div>
                        <label style="{{ $labelStyle }}">Correo electrónico <span style="color:#f87171">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="usuario@capycrunch.com" required
                               style="{{ $fieldStyle }}"
                               onfocus="this.style.borderColor='#7c3aed';this.style.boxShadow='0 0 0 3px rgba(124,58,237,0.15)'"
                               onblur="this.style.borderColor='rgba(255,255,255,0.1)';this.style.boxShadow='none'">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div>
                            <label style="{{ $labelStyle }}">Contraseña <span style="color:#f87171">*</span></label>
                            <input type="password" name="password" placeholder="Mínimo 6 caracteres" required
                                   style="{{ $fieldStyle }}"
                                   onfocus="this.style.borderColor='#7c3aed';this.style.boxShadow='0 0 0 3px rgba(124,58,237,0.15)'"
                                   onblur="this.style.borderColor='rgba(255,255,255,0.1)';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="{{ $labelStyle }}">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" placeholder="Repetir contraseña"
                                   style="{{ $fieldStyle }}"
                                   onfocus="this.style.borderColor='#7c3aed';this.style.boxShadow='0 0 0 3px rgba(124,58,237,0.15)'"
                                   onblur="this.style.borderColor='rgba(255,255,255,0.1)';this.style.boxShadow='none'">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div>
                            <label style="{{ $labelStyle }}">Rol <span style="color:#f87171">*</span></label>
                            <select name="role" required
                                    style="{{ $fieldStyle }};background:#1a1628;cursor:pointer">
                                <option value="">Seleccionar…</option>
                                <option value="admin"    {{ old('role')==='admin'    ? 'selected' : '' }}>🏪 Admin de Sucursal</option>
                                <option value="vendedor" {{ old('role')==='vendedor' ? 'selected' : '' }}>🛒 Vendedor</option>
                            </select>
                        </div>
                        <div>
                            <label style="{{ $labelStyle }}">Sucursal <span style="color:#f87171">*</span></label>
                            <select name="branch_id" required
                                    style="{{ $fieldStyle }};background:#1a1628;cursor:pointer">
                                <option value="">Seleccionar…</option>
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Info permisos por rol --}}
                <div style="margin-top:20px;padding:14px 16px;background:rgba(124,58,237,0.07);border:1px solid rgba(124,58,237,0.2);border-radius:10px;font-size:12px;color:rgba(196,181,253,0.55)">
                    <p style="font-weight:700;color:rgba(196,181,253,0.7);margin-bottom:6px">Permisos por rol:</p>
                    <p>🏪 <strong style="color:rgba(196,181,253,0.7)">Admin:</strong> Acceso completo a su sucursal (POS, ventas, clientes, galletas, domicilios, estadísticas, promos)</p>
                    <p style="margin-top:4px">🛒 <strong style="color:rgba(196,181,253,0.7)">Vendedor:</strong> POS, ver historial de ventas (solo PDF), clientes, galletas (solo ver), domicilios</p>
                </div>

                <div style="display:flex;gap:12px;margin-top:24px">
                    <button type="submit"
                            style="flex:1;padding:13px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:white;font-weight:700;font-size:15px;border:none;border-radius:10px;cursor:pointer;box-shadow:0 4px 16px rgba(124,58,237,0.35)">
                        Crear usuario
                    </button>
                    <a href="{{ route('superadmin.users') }}"
                       style="padding:13px 20px;border-radius:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.5);text-decoration:none;font-weight:600;font-size:14px">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
