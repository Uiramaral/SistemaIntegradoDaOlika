{{-- PÁGINA: Status de Pedidos (Configuração de Status) --}}
@extends('layouts.dashboard')

@section('title', 'Status de Pedidos — Dashboard Olika')

@section('content')
<div class="container mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">📦 Status de Pedidos</h1>

  {{-- Feedback --}}
  @if(session('ok'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('ok') }}</div>
  @endif

  {{-- Criar novo status --}}
  <div class="bg-white shadow rounded p-4 mb-6">
    <h2 class="font-semibold mb-2">Novo Status</h2>
    <form method="POST" action="{{ route('dashboard.statuses.store') }}" class="grid gap-3 md:grid-cols-2">
      @csrf
      <input name="code" placeholder="Código interno (ex: preparing)" class="border p-2 rounded" required>
      <input name="name" placeholder="Nome visível (ex: Em preparo)" class="border p-2 rounded" required>
      <label class="flex items-center gap-2"><input type="checkbox" name="is_final"> Finaliza pedido</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="notify_customer" checked> Notificar cliente</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="notify_admin"> Notificar admin</label>
      <select name="whatsapp_template_id" class="border p-2 rounded">
        <option value="">— Template WhatsApp —</option>
        @foreach($templates as $tpl)
          <option value="{{ $tpl->id }}">{{ $tpl->slug }}</option>
        @endforeach
      </select>
      <button class="bg-amber-600 hover:bg-amber-700 text-white py-2 rounded md:col-span-2">Adicionar</button>
    </form>
  </div>

  {{-- Lista --}}
  <div class="bg-white shadow rounded p-4">
    <h2 class="font-semibold mb-2">Status existentes</h2>
    <table class="w-full border text-sm">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="p-2">Código</th>
          <th class="p-2">Nome</th>
          <th class="p-2 text-center">Cliente</th>
          <th class="p-2 text-center">Admin</th>
          <th class="p-2">Template</th>
          <th class="p-2 text-center">Ativo</th>
          <th class="p-2 text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach($statuses as $s)
        <tr class="border-t hover:bg-gray-50">
          <td class="p-2 font-mono">{{ $s->code }}</td>
          <td class="p-2">{{ $s->name }}</td>
          <td class="p-2 text-center">
            <form method="POST" action="{{ route('dashboard.statuses.update', $s->id) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="notify_customer" value="{{ $s->notify_customer?0:1 }}">
              <button class="text-sm px-2 py-1 rounded {{ $s->notify_customer?'bg-green-200 text-green-800':'bg-gray-200 text-gray-600' }}">
                {{ $s->notify_customer?'Sim':'Não' }}
              </button>
            </form>
          </td>
          <td class="p-2 text-center">
            <form method="POST" action="{{ route('dashboard.statuses.update', $s->id) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="notify_admin" value="{{ $s->notify_admin?0:1 }}">
              <button class="text-sm px-2 py-1 rounded {{ $s->notify_admin?'bg-green-200 text-green-800':'bg-gray-200 text-gray-600' }}">
                {{ $s->notify_admin?'Sim':'Não' }}
              </button>
            </form>
          </td>
          <td class="p-2">
            <form method="POST" action="{{ route('dashboard.statuses.update', $s->id) }}">
              @csrf @method('PATCH')
              <select name="whatsapp_template_id" class="border rounded p-1 text-sm" onchange="this.form.submit()">
                <option value="">—</option>
                @foreach($templates as $tpl)
                  <option value="{{ $tpl->id }}" {{ $tpl->id == $s->whatsapp_template_id ? 'selected':'' }}>
                    {{ $tpl->slug }}
                  </option>
                @endforeach
              </select>
            </form>
          </td>
          <td class="p-2 text-center">
            <form method="POST" action="{{ route('dashboard.statuses.update', $s->id) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="active" value="{{ $s->active?0:1 }}">
              <button class="text-sm px-2 py-1 rounded {{ $s->active?'bg-green-200 text-green-800':'bg-red-100 text-red-700' }}">
                {{ $s->active?'Ativo':'Inativo' }}
              </button>
            </form>
          </td>
          <td class="p-2 text-center">
            <form method="POST" action="{{ route('dashboard.statuses.destroy', $s->id) }}" onsubmit="return confirm('Excluir status {{ $s->name }}?')">
              @csrf @method('DELETE')
              <button class="text-red-600 hover:underline text-sm">Excluir</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

