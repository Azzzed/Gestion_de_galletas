{{-- ═══════════════════════════════════════════════════════════════
     resources/views/superadmin/branches/_field.blade.php
     Partial: campo de formulario oscuro
════════════════════════════════════════════════════════════════ --}}
<label style="display:block;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5);margin-bottom:8px">
    {{ $label }}@if(!empty($required))<span style="color:#f87171;margin-left:2px">*</span>@endif
</label>
<input type="text" name="{{ $name }}" value="{{ $value ?? '' }}"
       placeholder="{{ $placeholder ?? '' }}"
       {{ !empty($required) ? 'required' : '' }}
       style="width:100%;padding:12px 14px;background:rgba(255,255,255,0.04);border:1.5px solid rgba(255,255,255,0.1);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;color:#f1eeff;outline:none;transition:all .2s"
       onfocus="this.style.borderColor='#7c3aed';this.style.background='rgba(124,58,237,0.07)';this.style.boxShadow='0 0 0 3px rgba(124,58,237,0.15)'"
       onblur="this.style.borderColor='rgba(255,255,255,0.1)';this.style.background='rgba(255,255,255,0.04)';this.style.boxShadow='none'">
