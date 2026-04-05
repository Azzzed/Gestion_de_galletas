@extends('layouts.superadmin')
@section('title', 'Editar Sucursal')

@section('content')

<div style="max-width:640px">

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px">
        <a href="{{ route('superadmin.branches') }}"
           style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;text-decoration:none;color:rgba(255,255,255,0.5)"
           onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
            <span class="icon" style="font-size:18px">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:#f1eeff">Editar: {{ $branch->nombre }}</h1>
            <p style="color:rgba(196,181,253,0.4);font-size:13px;margin-top:2px">Modificar datos de la sucursal</p>
        </div>
    </div>

    <div style="background:#13111f;border:1px solid rgba(139,92,246,0.18);border-radius:16px;overflow:hidden">
        <div style="height:3px;background:{{ $branch->color ?? '#ea6008' }}"></div>
        <div style="padding:32px">

            @if($errors->any())
            <div style="background:rgba(239,68,68,0.09);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:14px 16px;margin-bottom:24px;font-size:13px;color:#fca5a5">
                @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('superadmin.branches.update', $branch) }}">
                @csrf @method('PUT')

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                    <div style="grid-column:1/-1">
                        @include('superadmin.branches._field', ['name'=>'nombre','label'=>'Nombre de la sucursal','required'=>true,'value'=>old('nombre',$branch->nombre)])
                    </div>
                    <div>
                        @include('superadmin.branches._field', ['name'=>'slug','label'=>'Slug (URL)','value'=>old('slug',$branch->slug)])
                    </div>
                    <div>
                        @include('superadmin.branches._field', ['name'=>'ciudad','label'=>'Ciudad','value'=>old('ciudad',$branch->ciudad)])
                    </div>
                    <div>
                        @include('superadmin.branches._field', ['name'=>'telefono','label'=>'Teléfono','value'=>old('telefono',$branch->telefono)])
                    </div>
                    <div>
                        <label style="display:block;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5);margin-bottom:8px">Color</label>
                        <input type="color" name="color" value="{{ old('color',$branch->color ?? '#ea6008') }}"
                               style="width:44px;height:40px;border-radius:8px;border:1.5px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.04);cursor:pointer;padding:2px">
                    </div>
                    <div>
                        @include('superadmin.branches._field', ['name'=>'direccion','label'=>'Dirección','value'=>old('direccion',$branch->direccion)])
                    </div>
                    <div style="grid-column:1/-1">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1" {{ old('activo',$branch->activo) ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:#7c3aed;cursor:pointer">
                            <span style="font-size:14px;color:rgba(255,255,255,0.6)">Sucursal activa</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px">
                    <button type="submit"
                            style="flex:1;padding:13px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:white;font-weight:700;font-size:15px;border:none;border-radius:10px;cursor:pointer">
                        Guardar cambios
                    </button>
                    <a href="{{ route('superadmin.branches') }}"
                       style="padding:13px 20px;border-radius:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.5);text-decoration:none;font-weight:600;font-size:14px">
                        Cancelar
                    </a>
                </div>
            </form>

            {{-- Usuarios de esta sucursal --}}
            @if($branch->users->count() > 0)
            <div style="margin-top:28px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.07)">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(196,181,253,0.4);margin-bottom:12px">
                    Usuarios de esta sucursal ({{ $branch->users->count() }})
                </p>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @foreach($branch->users as $u)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:rgba(255,255,255,0.04);border-radius:9px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:30px;height:30px;border-radius:8px;background:rgba(124,58,237,0.2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#c4b5fd">
                                {{ strtoupper(substr($u->name,0,1)) }}
                            </div>
                            <div>
                                <p style="font-size:13px;font-weight:600;color:#f1eeff">{{ $u->name }}</p>
                                <p style="font-size:11px;color:rgba(255,255,255,0.3)">{{ $u->email }}</p>
                            </div>
                        </div>
                        <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;
                            {{ $u->role === 'admin' ? 'background:rgba(124,58,237,0.15);color:#c4b5fd;border:1px solid rgba(124,58,237,0.3)' : 'background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.45)' }}">
                            {{ $u->getRoleLabel() }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
