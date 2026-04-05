{{-- ═══════════════════════════════════════════════════════
     resources/views/superadmin/reportes/_filtros.blade.php
     Filtros compartidos para ventas/domicilios/inventario
════════════════════════════════════════════════════════ --}}
<div style="background:#13111f;border:1px solid rgba(139,92,246,0.15);border-radius:12px;padding:16px 20px;margin-bottom:20px">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
        @php $fStyle = "padding:9px 12px;background:rgba(255,255,255,0.05);border:1.5px solid rgba(255,255,255,0.1);border-radius:8px;font-size:13px;color:#f1eeff;outline:none;min-width:140px"; @endphp

        <div>
            <label style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.4);margin-bottom:5px">Sucursal</label>
            <select name="branch_id" style="{{ $fStyle }};background:#1a1628;cursor:pointer">
                <option value="">Todas</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id')==$b->id ? 'selected' : '' }}>{{ $b->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.4);margin-bottom:5px">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" style="{{ $fStyle }}">
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.4);margin-bottom:5px">Hasta</label>
            <input type="date" name="hasta" value="{{ request('hasta') }}" style="{{ $fStyle }}">
        </div>
        <button type="submit" style="padding:9px 18px;background:rgba(124,58,237,0.2);border:1px solid rgba(124,58,237,0.35);border-radius:8px;color:#c4b5fd;font-weight:600;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px">
            <span class="icon" style="font-size:16px">search</span> Filtrar
        </button>
        @if(request()->hasAny(['branch_id','desde','hasta','status']))
        <a href="{{ route($route) }}" style="padding:9px 14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:rgba(255,255,255,0.4);text-decoration:none;font-size:13px">
            <span class="icon" style="font-size:16px">close</span>
        </a>
        @endif
    </form>
</div>
