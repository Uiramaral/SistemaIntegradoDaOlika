@extends('layouts.dashboard')

@section('title','Consignações')

@section('page-title','Consignações')

@section('page-actions')
  <a href="{{ route('consignacoes.create') }}" class="btn-primary">Nova Consignação</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Abertas" :value="$stats['abertas'] ?? 0" />
  <x-stat-card label="Liquidadas" :value="$stats['liquidadas'] ?? 0" />
  <x-stat-card label="Canceladas" :value="$stats['canceladas'] ?? 0" />
@endsection

@section('quick-filters')
  <form class="flex gap-2" method="GET">
    <select name="periodo" class="pill">
      <option value="hoje" @selected(request('periodo')==='hoje')>Hoje</option>
      <option value="semana" @selected(request('periodo')==='semana')>Semana</option>
      <option value="mes" @selected(request('periodo','mes')==='mes')>Mês</option>
      <option value="all" @selected(request('periodo')==='all')>Tudo</option>
    </select>
    <select name="status" class="pill">
      <option value="">Todos</option>
      @foreach(['aberta'=>'Aberta','liquidada'=>'Liquidada','cancelada'=>'Cancelada'] as $k=>$v)
        <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
      @endforeach
    </select>
    <input type="search" class="pill" name="q" value="{{ request('q') }}" placeholder="Buscar parceiro/#" />
    <button class="pill">Filtrar</button>
  </form>
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold">Lista</div>
    <div class="overflow-auto">
      <table class="table-compact">
        <thead>
          <tr>
            <th class="text-left">#</th>
            <th class="text-left">Parceiro</th>
            <th class="text-left">Envio</th>
            <th class="text-left">Status</th>
            <th class="text-right">Itens</th>
            <th class="text-right">Vendido</th>
            <th class="text-right">Comissão</th>
            <th class="text-right">Líquido</th>
            <th class="text-right">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($consignacoes as $c)
          <tr>
            <td>#{{ $c->id }}</td>
            <td>{{ $c->parceiro->nome ?? '-' }}</td>
            <td>{{ \Illuminate\Support\Carbon::parse($c->data_envio)->format('d/m/Y') }}</td>
            <td><span class="pill">{{ ucfirst($c->status) }}</span></td>
            <td class="text-right">{{ $c->itens_count }}</td>
            <td class="text-right">R$ {{ number_format($c->total_vendido,2,',','.') }}</td>
            <td class="text-right">R$ {{ number_format($c->valor_comissao,2,',','.') }}</td>
            <td class="text-right">R$ {{ number_format($c->valor_liquido,2,',','.') }}</td>
            <td class="text-right">
              <a class="pill" href="{{ route('consignacoes.show',$c) }}">Ver</a>
              <a class="pill" href="{{ route('consignacoes.edit',$c) }}">Editar</a>
            </td>
          </tr>
          @empty
          <tr><td colspan="9" class="px-4 py-6 text-center text-neutral-500">Sem consignações.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-4 py-3">{{ $consignacoes->withQueryString()->links() }}</div>
  </div>
@endsection
