@extends('layouts.dashboard')

@section('title','Mercado Pago — Dashboard Olika')

@section('page-title','Mercado Pago')

@section('page-subtitle','Receba pagamentos online de forma segura e fácil')

@section('content')

@if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif

{{-- métricas topo --}}
<div class="grid-2" style="grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom:16px;">
  <div class="card">
    <div class="card-title">Total Processado</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">R$ {{ number_format($stats['total_processado'] ?? 24580,2,',','.') }}</div>
  </div>
  <div class="card">
    <div class="card-title">Transações</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['transacoes'] ?? 342 }}</div>
  </div>
  <div class="card">
    <div class="card-title">Taxa de Aprovação</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['taxa_aprovacao'] ?? 96 }}%</div>
  </div>
  <div class="card">
    <div class="card-title">Ticket Médio</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">R$ {{ number_format($stats['ticket_medio'] ?? 71.87,2,',','.') }}</div>
  </div>
</div>

<div class="card">
  <div class="card-title">Configurações da Integração</div>
  
  <form method="POST" action="{{ route('dashboard.mp.save') }}" class="form-section">
    @csrf
    
    <div class="field">
      <div class="lbl" style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:#fff7ed; border:1px solid #ffd7b3; border-radius:12px;">
        <div>
          <div style="font-weight:700;">Status da Conexão</div>
          <div class="muted" style="font-size:12px;">Configure suas credenciais</div>
        </div>
        <span class="badge gray">Não Conectado</span>
      </div>
    </div>
    
    <div class="field-row">
      <div class="field" style="grid-column: span 6;">
        <label class="lbl">Public Key</label>
        <input class="inp" name="mercadopago_public_key" value="{{ $keys['mercadopago_public_key'] ?? '' }}" placeholder="APP_USR-xxxx...">
      </div>
      
      <div class="field" style="grid-column: span 6;">
        <label class="lbl">Access Token</label>
        <input class="inp" type="password" name="mercadopago_access_token" value="{{ $keys['mercadopago_access_token'] ?? '' }}" placeholder="APP_USR-xxxx...">
      </div>
    </div>
    
    <div class="field-row">
      <div class="field" style="grid-column: span 6;">
        <label class="lbl">Modo de Produção</label>
        <label style="display:flex; align-items:center; gap:8px;">
          <input type="checkbox" class="inp" style="width:auto;" name="modo_producao" {{ ($keys['mercadopago_environment'] ?? 'production')==='production' ? 'checked' : '' }}>
          <span>Processar pagamentos reais</span>
        </label>
      </div>
      
      <div class="field" style="grid-column: span 6;">
        <label class="lbl">Salvar Cartões</label>
        <label style="display:flex; align-items:center; gap:8px;">
          <input type="checkbox" class="inp" style="width:auto;" name="save_cards" checked>
          <span>Permitir salvar dados</span>
        </label>
      </div>
    </div>
    
    <div class="field-row">
      <div class="field" style="grid-column: span 3;">
        <label class="lbl">Número Máximo de Parcelas</label>
        <input class="inp" type="number" name="max_parcelas" value="12">
      </div>
    </div>
    
    <div class="field-row">
      <div class="field" style="grid-column: span 12;">
        <label class="lbl">Webhook URL</label>
        <input class="inp" name="mercadopago_webhook_url" value="{{ $keys['mercadopago_webhook_url'] ?? route('webhook.mercadopago') }}">
      </div>
    </div>
    
    <div style="margin-top:10px; display:flex; gap:10px;">
      <button class="btn primary" type="submit" style="flex:1;">Salvar</button>
      <button class="btn" type="button">Testar Conexão</button>
    </div>
  </form>
</div>

@endsection