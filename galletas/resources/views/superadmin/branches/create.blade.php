{{-- ═══════════════════════════════════════════════════════════════
     resources/views/superadmin/branches/create.blade.php
     (para edit.blade.php cambia el @extends y usa $branch)
════════════════════════════════════════════════════════════════ --}}
@extends('layouts.superadmin')
@section('title', 'Nueva Sucursal')

@section('content')

<div style="max-width:640px">

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px">
        <a href="{{ route('superadmin.branches') }}"
           style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;text-decoration:none;color:rgba(255,255,255,0.5);transition:all .2s"
           onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
            <span class="icon" style="font-size:18px">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:#f1eeff">Nueva Sucursal</h1>
            <p style="color:rgba(196,181,253,0.4);font-size:13px;margin-top:2px">Crear una nueva sucursal del sistema</p>
        </div>
    </div>

    <div style="background:#13111f;border:1px solid rgba(139,92,246,0.18);border-radius:16px;overflow:hidden">
        <div style="height:3px;background:linear-gradient(90deg,#7c3aed,#a78bfa,#7c3aed)"></div>
        <div style="padding:32px">

            @if($errors->any())
            <div style="background:rgba(239,68,68,0.09);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:14px 16px;margin-bottom:24px;font-size:13px;color:#fca5a5">
                @foreach($errors->all() as $e)<p style="margin-bottom:2px">• {{ $e }}</p>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('superadmin.branches.store') }}">
                @csrf

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                    <div style="grid-column:1/-1">
                        @include('superadmin.branches._field', ['name'=>'nombre','label'=>'Nombre de la sucursal','placeholder'=>'Ej: Sucursal Norte','required'=>true,'value'=>old('nombre')])
                    </div>
                    <div>
                        @include('superadmin.branches._field', ['name'=>'slug','label'=>'Slug (URL)','placeholder'=>'ej: norte','value'=>old('slug')])
                    </div>
                    <div>
                        @include('superadmin.branches._field', ['name'=>'ciudad','label'=>'Ciudad','placeholder'=>'Ej: Bucaramanga','value'=>old('ciudad')])
                    </div>
                    <div>
                        @include('superadmin.branches._field', ['name'=>'telefono','label'=>'Teléfono','placeholder'=>'Ej: 300 123 4567','value'=>old('telefono')])
                    </div>
                    <div>
                        <label style="display:block;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5);margin-bottom:8px">
                            Color identificador
                        </label>
                        <div style="display:flex;align-items:center;gap:10px">
                            <input type="color" name="color" value="{{ old('color','#ea6008') }}"
                                   style="width:44px;height:40px;border-radius:8px;border:1.5px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.04);cursor:pointer;padding:2px">
                            <span style="font-size:12px;color:rgba(196,181,253,0.4)">Se muestra en el dashboard</span>
                        </div>
                    </div>
                    <div>
                        @include('superadmin.branches._field', ['name'=>'direccion','label'=>'Dirección','placeholder'=>'Cra 10 # 20-30','value'=>old('direccion')])
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px">
                    <button type="submit"
                            style="flex:1;padding:13px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:white;font-weight:700;font-size:15px;border:none;border-radius:10px;cursor:pointer;box-shadow:0 4px 16px rgba(124,58,237,0.35)">
                        Crear sucursal
                    </button>
                    <a href="{{ route('superadmin.branches') }}"
                       style="padding:13px 20px;border-radius:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.5);text-decoration:none;font-weight:600;font-size:14px">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
