@extends('layouts.dashboard')

@section('title','Cashback — Dashboard Olika')

@section('content')

<div class="card">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h1 class="text-xl" style="font-weight:800">Cashback</h1>
    <a href="{{ route('dashboard.cashback.create') }}" class="btn" style="background:#059669;color:#fff">➕ Novo Cashback</a>
  </div>

  @if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
  @if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

  <table style="width:100%">
    <thead><tr><th>ID</th><th>Cliente</th><th>Valor</th><th>Tipo</th><th>Status</th><th>Expira</th><th>Criado</th><th>Ações</th></tr></thead>
    <tbody>
      @forelse($cashbacks as $cb)
        <tr>
          <td data-label="ID">#{{ $cb->id }}</td>
          <td data-label="Cliente">
            <strong>{{ $cb->customer_name ?? '—' }}</strong><br>
            <small style="color:#6b7280">{{ $cb->customer_phone ?? '—' }}</small>
          </td>
          <td data-label="Valor">R$ {{ number_format($cb->amount,2,',','.') }}</td>
          <td data-label="Tipo">
            @if($cb->type === 'credit') <span class="badge" style="background:#dbeafe;color:#1e40af">💳 Crédito</span>
            @elseif($cb->type === 'manual') <span class="badge" style="background:#fef3c7;color:#92400e">✋ Manual</span>
            @else <span class="badge" style="background:#d1fae5;color:#065f46">🎁 Bônus</span>
            @endif
          </td>
          <td data-label="Status">
            @if($cb->status === 'pending') <span class="badge" style="background:#fef3c7;color:#92400e">⏳ Pendente</span>
            @elseif($cb->status === 'active') <span class="badge" style="background:#d1fae5;color:#065f46">✅ Ativo</span>
            @elseif($cb->status === 'used') <span class="badge" style="background:#eee;color:#666">💸 Usado</span>
            @else <span class="badge" style="background:#fee2e2;color:#991b1b">❌ Expirado</span>
            @endif
          </td>
          <td data-label="Expira">{{ $cb->expires_at ? \Carbon\Carbon::parse($cb->expires_at)->format('d/m/Y') : '—' }}</td>
          <td data-label="Criado">{{ \Carbon\Carbon::parse($cb->created_at)->format('d/m/Y') }}</td>
          <td data-label="Ações">
            <div style="display:flex;gap:4px">
              <a href="{{ route('dashboard.cashback.edit', $cb->id) }}" class="badge" style="background:#f59e0b;color:#fff">✏️ Editar</a>
              <form method="POST" action="{{ route('dashboard.cashback.destroy', $cb->id) }}" style="display:inline" onsubmit="return confirm('Excluir este cashback?')">
                @csrf @method('DELETE')
                <button type="submit" class="badge" style="background:#ef4444;color:#fff">🗑️ Excluir</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;padding:20px">Nenhum cashback registrado</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px">{{ $cashbacks->links() }}</div>

</div>

@endsection
