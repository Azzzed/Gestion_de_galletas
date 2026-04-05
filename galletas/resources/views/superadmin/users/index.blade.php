@extends('layouts.superadmin')
@section('title', 'Usuarios')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#f1eeff;display:flex;align-items:center;gap:10px">
            <span class="icon" style="color:#a78bfa">group</span> Usuarios
        </h1>
        <p style="color:rgba(196,181,253,0.45);font-size:13px;margin-top:4px">Admins y vendedores de todas las sucursales</p>
    </div>
    <a href="{{ route('superadmin.users.create') }}"
       style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:white;font-weight:700;font-size:14px;text-decoration:none;box-shadow:0 4px 14px rgba(124,58,237,0.35)">
        <span class="icon" style="font-size:18px">person_add</span>
        Nuevo Usuario
    </a>
</div>

{{-- Filtros --}}
<div style="background:#13111f;border:1px solid rgba(139,92,246,0.15);border-radius:12px;padding:16px 20px;margin-bottom:20px">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
        <div style="flex:1;min-width:180px">
            <label style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.4);margin-bottom:6px">Buscar</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o correo…"
                   style="width:100%;padding:9px 12px;background:rgba(255,255,255,0.05);border:1.5px solid rgba(255,255,255,0.1);border-radius:8px;font-size:13px;color:#f1eeff;outline:none"
                   onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
        </div>
        <div style="min-width:160px">
            <label style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.4);margin-bottom:6px">Sucursal</label>
            <select name="branch_id"
                    style="width:100%;padding:9px 12px;background:#1a1628;border:1.5px solid rgba(255,255,255,0.1);border-radius:8px;font-size:13px;color:#f1eeff;outline:none">
                <option value="">Todas</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width:140px">
            <label style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.4);margin-bottom:6px">Rol</label>
            <select name="role"
                    style="width:100%;padding:9px 12px;background:#1a1628;border:1.5px solid rgba(255,255,255,0.1);border-radius:8px;font-size:13px;color:#f1eeff;outline:none">
                <option value="">Todos</option>
                <option value="admin"    {{ request('role')==='admin'    ? 'selected' : '' }}>Admin</option>
                <option value="vendedor" {{ request('role')==='vendedor' ? 'selected' : '' }}>Vendedor</option>
            </select>
        </div>
        <button type="submit"
                style="padding:9px 18px;background:rgba(124,58,237,0.2);border:1px solid rgba(124,58,237,0.35);border-radius:8px;color:#c4b5fd;font-weight:600;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px">
            <span class="icon" style="font-size:16px">search</span> Filtrar
        </button>
        @if(request()->hasAny(['buscar','branch_id','role']))
        <a href="{{ route('superadmin.users') }}"
           style="padding:9px 14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:rgba(255,255,255,0.4);text-decoration:none;font-size:13px">
            <span class="icon" style="font-size:16px">close</span>
        </a>
        @endif
    </form>
</div>

{{-- Tabla --}}
<div style="background:#13111f;border:1px solid rgba(139,92,246,0.15);border-radius:14px;overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:rgba(124,58,237,0.08);border-bottom:1px solid rgba(255,255,255,0.07)">
                    @foreach(['Usuario','Correo','Sucursal','Rol','Estado','Acciones'] as $th)
                    <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5)">{{ $th }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05);transition:background .15s"
                    onmouseover="this.style.background='rgba(255,255,255,0.03)'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 16px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:34px;height:34px;border-radius:9px;background:rgba(124,58,237,0.2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:#c4b5fd;flex-shrink:0">
                                {{ strtoupper(substr($u->name,0,1)) }}
                            </div>
                            <p style="font-weight:600;color:#f1eeff;font-size:14px">{{ $u->name }}</p>
                        </div>
                    </td>
                    <td style="padding:14px 16px;font-size:13px;color:rgba(255,255,255,0.45)">{{ $u->email }}</td>
                    <td style="padding:14px 16px">
                        @if($u->branch)
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;background:rgba(124,58,237,0.12);color:#c4b5fd;border:1px solid rgba(124,58,237,0.25)">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $u->branch->color ?? '#7c3aed' }};display:inline-block"></span>
                            {{ $u->branch->nombre }}
                        </span>
                        @else
                        <span style="color:rgba(255,255,255,0.2);font-size:12px">—</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px">
                        <span style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;
                            {{ $u->role === 'admin' ? 'background:rgba(52,211,153,0.1);color:#34d399;border:1px solid rgba(52,211,153,0.2)' : 'background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.45);border:1px solid rgba(255,255,255,0.1)' }}">
                            {{ $u->getRoleLabel() }}
                        </span>
                    </td>
                    <td style="padding:14px 16px">
                        <span style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;
                            {{ $u->activo ? 'background:rgba(52,211,153,0.1);color:#34d399' : 'background:rgba(239,68,68,0.1);color:#f87171' }}">
                            {{ $u->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td style="padding:14px 16px">
                        <div style="display:flex;align-items:center;gap:6px">
                            <a href="{{ route('superadmin.users.edit', $u) }}"
                               style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;text-decoration:none;color:rgba(196,181,253,0.6);transition:all .2s"
                               onmouseover="this.style.background='rgba(124,58,237,0.2)';this.style.color='#c4b5fd'"
                               onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(196,181,253,0.6)'"
                               title="Editar">
                                <span class="icon" style="font-size:16px">edit</span>
                            </a>
                            {{-- Toggle activo --}}
                            <form method="POST" action="{{ route('superadmin.users.toggle', $u) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;cursor:pointer;color:{{ $u->activo ? 'rgba(248,113,113,0.6)' : 'rgba(52,211,153,0.6)' }};transition:all .2s"
                                        title="{{ $u->activo ? 'Desactivar' : 'Activar' }}"
                                        onmouseover="this.style.background='rgba(124,58,237,0.15)'"
                                        onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                                    <span class="icon" style="font-size:16px">{{ $u->activo ? 'block' : 'check_circle' }}</span>
                                </button>
                            </form>
                            {{-- Eliminar --}}
                            <form method="POST" action="{{ route('superadmin.users.destroy', $u) }}"
                                  onsubmit="return confirm('¿Eliminar usuario {{ $u->name }}?')" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="width:32px;height:32px;border-radius:8px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center;cursor:pointer;color:rgba(248,113,113,0.5);transition:all .2s"
                                        onmouseover="this.style.background='rgba(239,68,68,0.15)';this.style.color='#f87171'"
                                        onmouseout="this.style.background='rgba(239,68,68,0.07)';this.style.color='rgba(248,113,113,0.5)'">
                                    <span class="icon" style="font-size:16px">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:60px;text-align:center;color:rgba(255,255,255,0.2)">
                        <span class="icon" style="font-size:40px;display:block;margin-bottom:10px">group_off</span>
                        No hay usuarios registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.06)">
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection
