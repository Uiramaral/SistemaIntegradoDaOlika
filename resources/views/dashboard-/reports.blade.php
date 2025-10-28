{{-- PÁGINA: Relatórios (Visualização de Relatórios) --}}
@extends('layouts.dashboard')

@section('title','Relatórios — Dashboard Olika')

@section('page-title','Relatórios')

@section('page-subtitle','Acompanhe o desempenho do seu negócio')

@section('content')

{{-- KPIs --}}
<div class="grid-2" style="grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom:16px;">
  <div class="card">
    <div class="card-title">Receita Total</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">R$ {{ number_format($kpIs['faturamento'] ?? 24580,2,',','.') }}</div>
    <div class="muted" style="font-size:12px; margin-top:4px;">+12,5% vs período anterior</div>
  </div>
  <div class="card">
    <div class="card-title">Total de Pedidos</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $kpIs['qtd_pedidos'] ?? 342 }}</div>
    <div class="muted" style="font-size:12px; margin-top:4px;">+8,3% vs período anterior</div>
  </div>
  <div class="card">
    <div class="card-title">Novos Clientes</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['novos_clientes'] ?? 89 }}</div>
    <div class="muted" style="font-size:12px; margin-top:4px;">+15,2% vs período anterior</div>
  </div>
  <div class="card">
    <div class="card-title">Produtos Vendidos</div>
    <div style="font-weight:800; font-size:28px; margin-top:6px;">{{ $stats['produtos_vendidos'] ?? 1247 }}</div>
    <div class="muted" style="font-size:12px; margin-top:4px;">-3,1% vs período anterior</div>
  </div>
</div>

{{-- blocos de relatório --}}
<div class="grid-2" style="gap:16px;">
  <div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
      <div>
        <div style="font-weight:800;">Relatório de Vendas</div>
        <div class="muted" style="font-size:13px;">Análise completa das vendas do período</div>
      </div>
      <a class="pill" href="{{ route('relatorios.export', ['tipo'=>'vendas']) }}">Baixar</a>
    </div>
    <div style="height:120px; background:#f6f6f6; border-radius:12px; border:2px dashed #ddd; display:flex; align-items:center; justify-content:center;">
      <span class="muted">Gráfico de Vendas</span>
    </div>
    <div class="muted" style="font-size:12px; margin-top:8px;">Últimos 30 dias</div>
  </div>
  
  <div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
      <div>
        <div style="font-weight:800;">Relatório de Produtos</div>
        <div class="muted" style="font-size:13px;">Performance dos produtos mais vendidos</div>
      </div>
      <a class="pill" href="{{ route('relatorios.export', ['tipo'=>'produtos']) }}">Baixar</a>
    </div>
    <div style="height:120px; background:#f6f6f6; border-radius:12px; border:2px dashed #ddd; display:flex; align-items:center; justify-content:center;">
      <span class="muted">Gráfico de Produtos</span>
    </div>
    <div class="muted" style="font-size:12px; margin-top:8px;">Últimos 7 dias</div>
  </div>
  
  <div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
      <div>
        <div style="font-weight:800;">Relatório de Clientes</div>
        <div class="muted" style="font-size:13px;">Comportamento dos clientes</div>
      </div>
      <a class="pill" href="{{ route('relatorios.export', ['tipo'=>'clientes']) }}">Baixar</a>
    </div>
    <div style="height:120px; background:#f6f6f6; border-radius:12px; border:2px dashed #ddd; display:flex; align-items:center; justify-content:center;">
      <span class="muted">Gráfico de Clientes</span>
    </div>
    <div class="muted" style="font-size:12px; margin-top:8px;">Últimos 30 dias</div>
  </div>
  
  <div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
      <div>
        <div style="font-weight:800;">Relatório Financeiro</div>
        <div class="muted" style="font-size:13px;">Resumo financeiro e fluxo de caixa</div>
      </div>
      <a class="pill" href="{{ route('relatorios.export', ['tipo'=>'financeiro']) }}">Baixar</a>
    </div>
    <div style="height:120px; background:#f6f6f6; border-radius:12px; border:2px dashed #ddd; display:flex; align-items:center; justify-content:center;">
      <span class="muted">Gráfico Financeiro</span>
    </div>
    <div class="muted" style="font-size:12px; margin-top:8px;">Este mês</div>
  </div>
</div>

@endsection