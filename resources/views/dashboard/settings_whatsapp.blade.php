@extends('layouts.dashboard')

@section('title','WhatsApp — Dashboard Olika')

@section('content')

<div class="card">
  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">WhatsApp API (Evolution)</h1>
  
  @if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46">{{ session('ok') }}</div>@endif
  
  <form method="POST" action="{{ route('dashboard.whatsapp.save') }}" class="grid gap-3" style="max-width:640px">
    @csrf
    <label>API URL
      <input name="api_url" class="card" value="{{ $row->api_url ?? 'http://127.0.0.1:8080' }}">
    </label>
    <label>Instance Name
      <input name="instance_name" class="card" value="{{ $row->instance_name ?? 'olika_main' }}">
    </label>
    <label>API Key (AUTHENTICATION_API_KEY)
      <input name="api_key" class="card" value="{{ $row->api_key ?? '' }}">
    </label>
    <label>Nome do Remetente
      <input name="sender_name" class="card" value="{{ $row->sender_name ?? 'Olika Bot' }}">
    </label>
    <button class="btn" style="width:max-content">Salvar</button>
  </form>
</div>

<div class="card" style="margin-top:12px">
  <h2 style="font-weight:600;margin-bottom:8px">Respostas por IA</h2>
  <form method="POST" action="{{ route('dashboard.whatsapp.save') }}" class="grid gap-3" style="max-width:760px">
    @csrf
    <label class="flex items-center gap-2">
      <input type="checkbox" name="ai_enabled" value="1" {{ ($row->ai_enabled ?? 0) ? 'checked':'' }}>
      Ativar IA para mensagens recebidas
    </label>
    <label>OpenAI API Key
      <input name="openai_api_key" class="card" value="{{ $row->openai_api_key ?? '' }}" placeholder="sk-...">
    </label>
    <label>Modelo
      <input name="openai_model" class="card" value="{{ $row->openai_model ?? 'gpt-4o-mini' }}">
    </label>
    <label>System Prompt (persona)
      <textarea name="ai_system_prompt" class="card" rows="5">{{ $row->ai_system_prompt ?? '' }}</textarea>
    </label>
    <label>Número do Admin (opcional, E.164)
      <input name="admin_phone" class="card" value="{{ $row->admin_phone ?? '' }}" placeholder="55DDD9XXXXXXXX">
    </label>
    <button class="btn" style="width:max-content">Salvar IA</button>
  </form>
</div>

<div class="card" style="margin-top:12px">
  <h2 style="font-weight:600;margin-bottom:8px">Conectar dispositivo</h2>
  <p class="text-sm" style="color:#555">Clique para gerar o QR / pairing code e leia no WhatsApp (Ajustes → Dispositivos conectados → Conectar um dispositivo).</p>
  <button class="btn" id="btn-connect">Conectar dispositivo</button>
  <div id="connect-out" style="margin-top:12px"></div>
</div>

<div class="card" style="margin-top:12px">
  <h2 style="font-weight:600;margin-bottom:8px">Status da Instância</h2>
  <button class="btn" id="btn-health">Atualizar status</button>
  <div id="health-out" style="margin-top:10px;color:#333"></div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('btn-connect').addEventListener('click', async ()=>{
  const out = document.getElementById('connect-out');
  out.innerHTML = 'Gerando QR...';
  
  try{
    const r = await fetch('{{ route('dashboard.whatsapp.connect') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
    const j = await r.json();
    
    if(!j.ok){ 
      out.innerHTML = `<div class="badge" style="background:#fee2e2;color:#991b1b">${j.msg||'Falha ao conectar'}</div>`; 
      return; 
    }
    
    let imgSrc = j.qr_base64 || '';
    // pode vir "data:image/png;base64,..." ou só o base64
    if(imgSrc && !imgSrc.startsWith('data:image')) imgSrc = 'data:image/png;base64,'+imgSrc;
    
    out.innerHTML = `
      ${j.pairing_code ? `<div class="badge" style="background:#eef2ff;color:#3730a3">Pairing code: <strong>${j.pairing_code}</strong></div>` : ''}
      ${imgSrc ? `<div style="margin-top:8px"><img src="${imgSrc}" style="max-width:240px;border:1px solid #eee;border-radius:10px"></div>` : '<small>Sem QR na resposta.</small>'}
      <div style="margin-top:8px;color:#555;font-size:12px">Se já estiver conectado, o QR pode não aparecer.</div>
    `;
    
  }catch(e){
    out.innerHTML = `<div class="badge" style="background:#fee2e2;color:#991b1b">Erro ao conectar</div>`;
  }
});

document.getElementById('btn-health').addEventListener('click', async ()=>{
  const out = document.getElementById('health-out');
  out.textContent = 'consultando...';
  
  try{
    const r = await fetch('{{ route('dashboard.whatsapp.health') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
    const j = await r.json();
    
    if(!j.ok){ 
      out.innerHTML = `<div class="badge" style="background:#fee2e2;color:#991b1b">${j.msg||'Falha'}</div>`; 
      return; 
    }
    
    const d = j.data || {};
    const online = d.state === 'open' || d.connected === true;
    
    out.innerHTML = `
      <div class="badge" style="background:${online?'#d1fae5':'#fee2e2'};color:${online?'#065f46':'#991b1b'}">
        ${online?'ONLINE ✅':'OFFLINE ❌'}
      </div>
      <div style="margin-top:6px;font-size:12px">
        Device: ${d.device || d.phone || '-'} |
        Battery: ${d.battery ?? '-'} |
        Platform: ${d.platform ?? '-'}
      </div>
    `;
    
  }catch(e){
    out.innerHTML = `<div class="badge" style="background:#fee2e2;color:#991b1b">Erro de rede</div>`;
  }
});
</script>
@endpush