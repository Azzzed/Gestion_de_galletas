@extends('layouts.superadmin')
@section('title', 'Sucursales')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#f1eeff;display:flex;align-items:center;gap:10px">
            <span class="icon" style="color:#a78bfa">store</span> Sucursales
        </h1>
        <p style="color:rgba(196,181,253,0.45);font-size:13px;margin-top:4px">Gestión de sucursales del sistema</p>
    </div>
    <a href="{{ route('superadmin.branches.create') }}"
       style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:white;font-weight:700;font-size:14px;text-decoration:none;transition:all .2s;box-shadow:0 4px 14px rgba(124,58,237,0.35)">
        <span class="icon" style="font-size:18px">add</span>
        Nueva Sucursal
    </a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
    @forelse($branches as $branch)
    <div style="background:#13111f;border:1px solid rgba(139,92,246,0.15);border-radius:16px;overflow:hidden;position:relative">

        {{-- Barra de color de la sucursal --}}
        <div style="height:4px;background:{{ $branch->color ?? '#ea6008' }}"></div>

        <div style="padding:20px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:44px;height:44px;border-radius:12px;background:rgba(124,58,237,0.15);border:1px solid rgba(124,58,237,0.25);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <span class="icon" style="color:#a78bfa;font-size:22px">store</span>
                    </div>
                    <div>
                        <p style="font-weight:700;color:#f1eeff;font-size:15px">{{ $branch->nombre }}</p>
                        @if($branch->ciudad)
                        <p style="font-size:12px;color:rgba(196,181,253,0.4);margin-top:2px;display:flex;align-items:center;gap:4px">
                            <span class="icon" style="font-size:13px">location_on</span>{{ $branch->ciudad }}
                        </p>
                        @endif
                    </div>
                </div>
                <span style="font-size:10px;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap;
                    {{ $branch->activo ? 'background:rgba(52,211,153,0.12);color:#34d399;border:1px solid rgba(52,211,153,0.25)' : 'background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.2)' }}">
                    {{ $branch->activo ? '● Activa' : '● Inactiva' }}
                </span>
            </div>

            {{-- Stats --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px">
                @foreach([
                    ['users_count', 'group', 'Usuarios'],
                    ['sales_count', 'receipt_long', 'Ventas'],
                    ['customers_count', 'person', 'Clientes'],
                    ['delivery_orders_count', 'local_shipping', 'Domicilios'],
                ] as [$field, $icon, $label])
                <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:10px 6px;text-align:center">
                    <span class="icon" style="font-size:16px;color:rgba(167,139,250,0.6);display:block;margin-bottom:4px">{{ $icon }}</span>
                    <p style="font-weight:700;color:#f1eeff;font-size:15px">{{ $branch->$field ?? 0 }}</p>
                    <p style="font-size:9px;color:rgba(255,255,255,0.25);text-transform:uppercase;letter-spacing:.05em;margin-top:2px">{{ $label }}</p>
                </div>
                @endforeach
            </div>

            @if($branch->telefono)
            <p style="font-size:12px;color:rgba(196,181,253,0.35);margin-bottom:14px;display:flex;align-items:center;gap:5px">
                <span class="icon" style="font-size:14px">phone</span>{{ $branch->telefono }}
            </p>
            @endif

            {{-- Acciones --}}
            <div style="display:flex;gap:8px">
                <a href="{{ route('superadmin.branches.edit', $branch) }}"
                   style="flex:1;text-align:center;padding:8px;border-radius:9px;font-size:13px;font-weight:600;color:rgba(196,181,253,0.7);text-decoration:none;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;gap:6px;transition:all .2s"
                   onmouseover="this.style.background='rgba(124,58,237,0.15)';this.style.color='#c4b5fd'"
                   onmouseout="this.style.background='rgba(255,255,255,0.05)';this.style.color='rgba(196,181,253,0.7)'">
                    <span class="icon" style="font-size:16px">edit</span> Editar
                </a>
                <a href="{{ route('superadmin.ventas', ['branch_id' => $branch->id]) }}"
                   style="flex:1;text-align:center;padding:8px;border-radius:9px;font-size:13px;font-weight:600;color:rgba(234,96,8,0.8);text-decoration:none;background:rgba(234,96,8,0.08);border:1px solid rgba(234,96,8,0.2);display:flex;align-items:center;justify-content:center;gap:6px;transition:all .2s"
                   onmouseover="this.style.background='rgba(234,96,8,0.15)'"
                   onmouseout="this.style.background='rgba(234,96,8,0.08)'">
                    <span class="icon" style="font-size:16px">bar_chart</span> Ver ventas
                </a>
                <form method="POST" action="{{ route('superadmin.branches.destroy', $branch) }}"
                      onsubmit="return confirm('¿Eliminar sucursal «{{ $branch->nombre }}»? Solo si no tiene ventas.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="padding:8px 12px;border-radius:9px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.15);color:rgba(248,113,113,0.6);cursor:pointer;transition:all .2s;display:flex;align-items:center"
                            onmouseover="this.style.background='rgba(239,68,68,0.15)';this.style.color='#f87171'"
                            onmouseout="this.style.background='rgba(239,68,68,0.07)';this.style.color='rgba(248,113,113,0.6)'">
                        <span class="icon" style="font-size:17px">delete</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:80px 20px">
        <span class="icon" style="font-size:48px;color:rgba(255,255,255,0.1);display:block;margin-bottom:12px">store_mall_directory</span>
        <p style="color:rgba(255,255,255,0.3);font-size:14px">No hay sucursales creadas</p>
        <a href="{{ route('superadmin.branches.create') }}" style="display:inline-block;margin-top:16px;padding:10px 20px;border-radius:10px;background:rgba(124,58,237,0.2);color:#c4b5fd;text-decoration:none;font-size:13px;font-weight:600">
            Crear la primera sucursal
        </a>
    </div>
    @endforelse
</div>

@endsection
