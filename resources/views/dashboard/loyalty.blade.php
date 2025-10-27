@extends('layouts.dashboard')

@section('title','Fidelidade — Dashboard Olika')

@section('page-title','Programa de Fidelidade')

@section('page-subtitle','Configure e gerencie o programa de pontos')

@section('content')

@if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
@if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

{{-- métricas topo --}}
<div class="grid-2" style="grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom:16px;">
  <div class="card">
    <div class="card-title">Pontos Emitidos</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['pontos_emitidos'] ?? '12.450' }}</div>
  </div>
  <div class="card">
    <div class="card-title">Pontos Resgatados</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['pontos_resgatados'] ?? '8.230' }}</div>
  </div>
  <div class="card">
    <div class="card-title">Clientes Ativos</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['clientes_ativos'] ?? '342' }}</div>
  </div>
  <div class="card">
    <div class="card-title">Taxa de Resgate</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['taxa_resgate'] ?? '66' }}%</div>
  </div>
</div>

<div class="card">
  <div class="card-title">Configurações do Programa</div>

  <form method="post" action="{{ route('dashboard.loyalty.save') }}" class="form-section">
    @csrf

    <label class="field">
      <div class="lbl" style="display:flex; align-items:center; gap:8px;">
        Programa Ativo
        <input type="checkbox" name="active" value="1" {{ (old('active', $settings['active'] ?? true)) ? 'checked' : '' }} style="width:auto;">
      </div>
    </label>

    <div class="field-row">
      <div class="field" style="grid-column: span 6;">
        <label class="lbl">Pontos por Real (R$)</label>
        <input class="inp" name="points_per_real" value="{{ old('points_per_real', $settings['points_per_real'] ?? '10') }}">
      </div>

      <div class="field" style="grid-column: span 6;">
        <label class="lbl">Pedido Mínimo (R$)</label>
        <input class="inp" name="min_order" value="{{ old('min_order', $settings['min_order'] ?? '20') }}">
      </div>
    </div>

    <div class="field-row">
      <div class="field" style="grid-column: span 4;">
        <div class="card" style="border:2px dashed var(--line); border-radius:12px; padding:14px;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
            <span class="badge orange">Bronze</span>
            <span class="muted" style="font-size:12px;">nível</span>
          </div>
          <label class="lbl">Pontuação mínima</label>
          <input class="inp" name="bronze_min" value="{{ old('bronze_min', $settings['bronze_min'] ?? '0') }}">
        </div>
      </div>

      <div class="field" style="grid-column: span 4;">
        <div class="card" style="border:2px dashed var(--line); border-radius:12px; padding:14px;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
            <span class="badge gray">Prata</span>
          </div>
          <label class="lbl">Pontuação mínima</label>
          <input class="inp" name="silver_min" value="{{ old('silver_min', $settings['silver_min'] ?? '500') }}">
        </div>
      </div>

      <div class="field" style="grid-column: span 4;">
        <div class="card" style="border:2px dashed var(--line); border-radius:12px; padding:14px;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
            <span class="badge orange">Ouro</span>
          </div>
          <label class="lbl">Pontuação mínima</label>
          <input class="inp" name="gold_min" value="{{ old('gold_min', $settings['gold_min'] ?? '1000') }}">
        </div>
      </div>
    </div>

    <div style="margin-top:10px;">
      <button class="btn primary" type="submit" style="width:100%;">Salvar Configurações</button>
    </div>
  </form>
</div>

@if(isset($participants) && $participants->count() > 0)
<div class="card" style="margin-top:16px;">
  <div class="card-title">Participantes Ativos</div>
  
  <div class="input-search" style="margin-bottom:12px;">
    <svg class="icon" width="18" height="18" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35" stroke="#999" stroke-width="1.5" fill="none"/><circle cx="10.5" cy="10.5" r="7" stroke="#999" stroke-width="1.5" fill="none"/></svg>
    <input class="inp" placeholder="Buscar cliente..." name="q" value="{{ request('q') }}">
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Cliente</th>
        <th>Pontos</th>
        <th>Nível</th>
        <th>Última Compra</th>
        <th class="cell-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      @foreach($participants as $p)
      @php
        $pontos = $p->loyalty_points ?? $p->points ?? 0;
        $nivel = 'bronze';
        if($pontos >= ($settings['gold_min'] ?? 1000)) $nivel = 'gold';
        elseif($pontos >= ($settings['silver_min'] ?? 500)) $nivel = 'silver';
      @endphp
      <tr>
        <td>
          <div style="display:flex; align-items:center; gap:10px;">
            <div class="avatar">{{ strtoupper(substr($p->customer->name ?? 'N/A', 0, 2)) }}</div>
            <div>
              <div style="font-weight:800;">{{ $p->customer->name ?? 'N/A' }}</div>
              <div class="muted">{{ $p->customer->phone ?? '—' }}</div>
            </div>
          </div>
        </td>
        <td style="font-weight:800;">{{ number_format($pontos, 0, ',', '.') }} pts</td>
        <td>
          <span class="badge {{ $nivel === 'gold' ? 'orange' : ($nivel === 'silver' ? 'gray' : 'orange') }}">
            {{ ucfirst($nivel) }}
          </span>
        </td>
        <td class="muted">{{ $p->last_purchase_at ?? '—' }}</td>
        <td class="cell-right">
          <a class="link" href="{{ route('dashboard.customers.show', $p->customer_id) }}">Ver perfil</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  @if(method_exists($participants, 'links'))
  <div style="margin-top:12px;">{{ $participants->links() }}</div>
  @endif
</div>
@endif

@endsection