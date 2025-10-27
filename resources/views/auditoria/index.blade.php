@extends('layouts.dashboard')

@section('title','Auditoria')

@section('page-title','Auditoria')

@section('quick-filters')
  <form class="flex gap-2">
    <input class="pill" type="text" name="model" value="{{ request('model') }}" placeholder="Model (ex.: Pedido)" />
    <input class="pill" type="number" name="id" value="{{ request('id') }}" placeholder="#ID" />
    <select class="pill" name="action">
      <option value="">Ação</option>
      @foreach(['created','updated','deleted','bulk','custom'] as $a)
        <option value="{{ $a }}" @selected(request('action')===$a)>{{ $a }}</option>
      @endforeach
    </select>
    <button class="pill">Filtrar</button>
  </form>
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold">Logs</div>
    <div class="overflow-auto">
      <table class="table-compact">
        <thead>
          <tr>
            <th class="text-left">Quando</th>
            <th class="text-left">Usuário</th>
            <th class="text-left">Ação</th>
            <th class="text-left">Modelo</th>
            <th class="text-left">#</th>
            <th class="text-right">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $l)
          <tr>
            <td>{{ $l->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $l->user->name ?? '—' }}</td>
            <td><span class="pill">{{ $l->action }}</span></td>
            <td>{{ class_basename($l->model_type) }}</td>
            <td>{{ $l->model_id }}</td>
            <td class="text-right"><a href="{{ route('auditoria.show',$l) }}" class="pill">Ver</a></td>
          </tr>
          @empty
          <tr><td colspan="6" class="px-4 py-6 text-center text-neutral-500">Sem logs.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-4 py-3">{{ $logs->withQueryString()->links() }}</div>
  </div>
@endsection
