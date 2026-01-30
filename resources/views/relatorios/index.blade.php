@extends('layouts.dashboard')

@section('title','Relatórios')

@section('page-title','Relatórios')

@section('page-subtitle','Vendas, produtos, cupons e geografia do período selecionado')

@section('quick-filters')
  <form class="flex gap-2" method="GET" action="{{ route('relatorios.index') }}">
    <select name="periodo" class="pill">
      <option value="hoje" @selected(request('periodo')==='hoje')>Hoje</option>
      <option value="semana" @selected(request('periodo')==='semana')>Semana</option>
      <option value="mes" @selected(request('periodo','mes')==='mes')>Mês</option>
      <option value="custom" @selected(request('periodo')==='custom')>Personalizado</option>
    </select>
    <input type="date" name="ini" value="{{ request('ini', optional($ini)->format('Y-m-d')) }}" class="pill" />
    <input type="date" name="fim" value="{{ request('fim', optional($fim)->format('Y-m-d')) }}" class="pill" />
    <select name="status" class="pill">
      <option value="">Todos os status</option>
      @foreach(['agendado'=>'Agendado','producao'=>'Produção','entrega'=>'Entrega','concluido'=>'Concluído','cancelado'=>'Cancelado'] as $k=>$v)
        <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
      @endforeach
    </select>
    <button class="btn-primary">Aplicar</button>
  </form>
@endsection

@section('stat-cards')
  <x-stat-card label="Pedidos" :value="number_format($kpIs['qtd_pedidos'] ?? 0,0,'','.')" hint="Período" />
  <x-stat-card label="Faturamento" :value="'R$ '.number_format($kpIs['faturamento'] ?? 0,2,',','.')" hint="Período" />
  <x-stat-card label="Ticket médio" :value="'R$ '.number_format($kpIs['ticket_medio'] ?? 0,2,',','.')" hint="Período" />
@endsection

@section('content')

  {{-- Série diária --}}
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold">Faturamento diário</div>
    <div class="overflow-auto">
      <table class="table-compact">
        <thead><tr><th class="text-left">Dia</th><th class="text-right">Total</th></tr></thead>
        <tbody>
          @forelse($serieDiaria as $row)
          <tr>
            <td>{{ \Illuminate\Support\Carbon::parse($row->dia)->format('d/m') }}</td>
            <td class="text-right">R$ {{ number_format($row->total,2,',','.') }}</td>
          </tr>
          @empty
          <tr><td colspan="2" class="px-4 py-6 text-center text-neutral-500">Sem dados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Top produtos --}}
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold">Top produtos</div>
    <div class="overflow-auto">
      <table class="table-compact">
        <thead>
          <tr>
            <th class="text-left">Produto</th>
            <th class="text-right">Qtd</th>
            <th class="text-right">Receita</th>
          </tr>
        </thead>
        <tbody>
          @forelse($topProdutos as $p)
          <tr>
            <td>{{ $p->nome }}</td>
            <td class="text-right">{{ $p->qtd }}</td>
            <td class="text-right">R$ {{ number_format($p->receita,2,',','.') }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="px-4 py-6 text-center text-neutral-500">Sem dados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Cupons usados --}}
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold">Cupons usados</div>
    <div class="overflow-auto">
      <table class="table-compact">
        <thead>
          <tr>
            <th class="text-left">Cupom</th>
            <th class="text-right">Pedidos</th>
            <th class="text-right">Receita</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cuponsUsados as $c)
          <tr>
            <td><span class="pill">{{ $c->cupom_codigo }}</span></td>
            <td class="text-right">{{ $c->qtd }}</td>
            <td class="text-right">R$ {{ number_format($c->receita,2,',','.') }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="px-4 py-6 text-center text-neutral-500">Sem dados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Por bairro --}}
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold">Pedidos por bairro</div>
    <div class="overflow-auto">
      <table class="table-compact">
        <thead>
          <tr>
            <th class="text-left">Bairro</th>
            <th class="text-right">Pedidos</th>
            <th class="text-right">Receita</th>
          </tr>
        </thead>
        <tbody>
          @forelse($porBairro as $b)
          <tr>
            <td>{{ $b->bairro ?: '—' }}</td>
            <td class="text-right">{{ $b->qtd }}</td>
            <td class="text-right">R$ {{ number_format($b->receita,2,',','.') }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="px-4 py-6 text-center text-neutral-500">Sem dados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
