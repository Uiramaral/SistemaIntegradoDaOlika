@extends('layouts.dashboard')

@section('title','Pedidos')

@section('page-title','Pedidos')

@section('page-actions')
  <a href="{{ route('pedidos.create') }}" class="btn-primary">Novo Pedido</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Hoje" :value="$stats['total_hoje'] ?? 0" />
  <x-stat-card label="Produção" :value="$stats['em_producao'] ?? 0" />
  <x-stat-card label="Em entrega" :value="$stats['em_entrega'] ?? 0" />
  <x-stat-card label="Concluídos" :value="$stats['concluidos'] ?? 0" />
@endsection

@section('quick-filters')
  <form method="GET" class="flex gap-2">
    <select name="periodo" class="pill">
      @foreach(['hoje'=>'Hoje','semana'=>'Semana','mes'=>'Mês','all'=>'Tudo'] as $k=>$v)
        <option value="{{ $k }}" @selected(request('periodo', 'hoje')===$k)>{{ $v }}</option>
      @endforeach
    </select>
    <select name="status" class="pill">
      <option value="">Todos</option>
      @foreach(['agendado'=>'Agendado','producao'=>'Produção','entrega'=>'Entrega','concluido'=>'Concluído','cancelado'=>'Cancelado'] as $k=>$v)
        <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
      @endforeach
    </select>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar cliente ou #pedido" class="pill" />
    <button class="pill">Filtrar</button>
  </form>
@endsection

@section('content')
  <form method="POST" action="{{ route('pedidos.bulk') }}" x-data="{ ids:[], all:false, toggleAll(e){ this.all=e.target.checked; this.ids = this.all ? Array.from(document.querySelectorAll('[data-pedido]')).map(el=>+el.dataset.pedido) : []; }, toggleOne(id,e){ if(e.target.checked){ this.ids.push(id) } else { this.ids = this.ids.filter(i=>i!==id) } this.all = this.ids.length && this.ids.length === document.querySelectorAll('[data-pedido]').length } }" class="grid gap-4">
    @csrf

    <div class="card p-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <label class="pill"><input type="checkbox" @change="toggleAll" /> Selecionar tudo</label>
        <div class="pill" x-text="ids.length + ' selecionado(s)'">0 selecionado(s)</div>
      </div>
      <div class="flex items-center gap-2">
        <select name="acao" class="pill" required>
          <option value="status">Alterar status</option>
          <option value="data">Alterar data/hora</option>
          <option value="status_e_data">Status + data/hora</option>
        </select>
        <select name="status" class="pill">
          <option value="">— Status —</option>
          @foreach(['agendado'=>'Agendado','producao'=>'Produção','entrega'=>'Entrega','concluido'=>'Concluído','cancelado'=>'Cancelado'] as $k=>$v)
            <option value="{{ $k }}">{{ $v }}</option>
          @endforeach
        </select>
        <input type="datetime-local" name="data_entrega" class="pill" />
        <input type="hidden" name="ids" :value="JSON.stringify(ids)" />
        <button class="btn-primary" onclick="return confirm('Aplicar ação em massa?')">Aplicar</button>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="px-4 py-3 border-b font-semibold">Listagem</div>
      <div class="overflow-auto">
        <table class="table-compact">
          <thead>
            <tr>
              <th class="text-left">Sel</th>
              <th class="text-left">#</th>
              <th class="text-left">Cliente</th>
              <th class="text-left">Status</th>
              <th class="text-left">Entrega</th>
              <th class="text-right">Itens</th>
              <th class="text-right">Total</th>
              <th class="text-right">Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pedidos as $p)
            <tr data-pedido="{{ $p->id }}">
              <td>
                <input type="checkbox" @change="toggleOne({{ $p->id }}, $event)" />
              </td>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->cliente->nome ?? '-' }}</td>
              <td><span class="pill">{{ ucfirst($p->status) }}</span></td>
              <td>{{ $p->data_entrega?->format('d/m H:i') ?? '—' }}</td>
              <td class="text-right">{{ $p->itens_count }}</td>
              <td class="text-right">R$ {{ number_format($p->total,2,',','.') }}</td>
              <td class="text-right">
                <a class="pill" href="{{ route('pedidos.show',$p) }}">Ver</a>
                <a class="pill" href="{{ route('pedidos.edit',$p) }}">Editar</a>
                <x-map-link :href="optional($p->cliente)->maps_url" mode="icon" size="sm" />
              </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-6 text-center text-neutral-500">Sem pedidos.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="px-4 py-3">{{ $pedidos->withQueryString()->links() }}</div>
    </div>
  </form>
@endsection
