@extends('layouts.dashboard')

@section('title','Cupons de Desconto')

@section('page-title','Cupons de Desconto')

@section('page-subtitle','Gerencie cupons promocionais para seus clientes')

@section('content')

<div class="toolbar">
  <div class="grow">
    <h1 class="page-title">Cupons de Desconto</h1>
    <p class="page-sub">Gerencie cupons promocionais para seus clientes</p>
  </div>
  <div class="actions">
    <a class="btn ghost" href="{{ route('dashboard.index') }}">Voltar</a>
    <a class="btn primary" href="{{ route('cupons.create') }}">+ Criar Cupom</a>
  </div>
</div>

{{-- métricas (fake ou reais) --}}
<div class="grid-2" style="grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-bottom:16px;">
  <div class="card">
    <div class="card-title">Cupons Ativos</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['ativos'] ?? 12 }}</div>
  </div>
  <div class="card">
    <div class="card-title">Total de Usos</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['usos'] ?? 218 }}</div>
  </div>
  <div class="card">
    <div class="card-title">Economia Gerada</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">R$ {{ number_format($stats['economia'] ?? 3240,2,',','.') }}</div>
  </div>
</div>

<div class="card">
  <div class="input-search" style="margin-bottom:12px;">
    <svg class="icon" width="18" height="18" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35" stroke="#999" stroke-width="1.5" fill="none"/><circle cx="10.5" cy="10.5" r="7" stroke="#999" stroke-width="1.5" fill="none"/></svg>
    <input class="inp" placeholder="Buscar cupons..." name="q" value="{{ request('q') }}">
  </div>

  {{-- lista de cupons em cards --}}
  <div style="display:flex; flex-direction:column; gap:12px;">
    @forelse($coupons as $c)
      @php
        $isActive = $c->active ?? true;
        $usos = ($c->used_count ?? 23).'/'.($c->usage_limit ?? 100);
        $tipo = $c->type ?? 'percent'; // 'percent' | 'fixed'
        $valor = $tipo==='percent' ? ($c->value ?? 10).'%' : 'R$ '.number_format($c->value ?? 15,2,',','.');
        $expira = \Illuminate\Support\Carbon::parse($c->expires_at ?? now()->addMonths(2))->format('d/m/Y');
      @endphp
      <div class="coupon-row" style="display:grid; grid-template-columns: 8px 1fr auto; gap:12px; align-items:center; padding:14px; border:1px solid var(--line); border-radius:14px;">
        <div style="width:8px; height:100%; background:linear-gradient(#ffb37a,#ff7a18); border-radius:8px;"></div>

        <div style="display:flex; flex-direction:column; gap:4px;">
          <div style="display:flex; align-items:center; gap:8px;">
            <div style="font-weight:800;">{{ strtoupper($c->code ?? $c->coupon_code ?? 'N/A') }}</div>
            <span class="badge {{ $isActive ? 'orange' : 'gray' }}">{{ $isActive ? 'Ativo' : 'Inativo' }}</span>
          </div>
          <div class="muted" style="display:flex; gap:16px; flex-wrap:wrap;">
            <span>% Tipo: {{ $tipo==='percent' ? 'Percentual' : 'Valor Fixo' }}</span>
            <span>% Valor: {{ $valor }}</span>
            <span>👥 {{ $usos }} usos</span>
            <span>📅 Válido até {{ $expira }}</span>
          </div>
        </div>

        <div style="display:flex; gap:8px;">
          <a class="pill" href="{{ route('cupons.edit',$c->id) }}">Editar</a>
          <form method="post" action="{{ route('cupons.toggle',$c->id) }}" style="display:inline;">
            @csrf
            <button class="pill" type="submit">{{ $isActive ? 'Desativar' : 'Ativar' }}</button>
          </form>
        </div>
      </div>
    @empty
      <div class="muted">Nenhum cupom cadastrado.</div>
    @endforelse
  </div>
</div>

@endsection
