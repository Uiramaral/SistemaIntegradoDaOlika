@extends('layouts.dashboard')

@section('title','Cupons — Dashboard Olika')

@section('content')

<div class="card">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h1 class="text-xl" style="font-weight:800">Cupons</h1>
    <a href="{{ route('dashboard.coupons.create') }}" class="btn" style="background:#059669;color:#fff">➕ Novo Cupom</a>
  </div>

  @if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
  @if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

  <table style="width:100%;font-size:0.9rem">
    <thead><tr><th>ID</th><th>Código</th><th>Tipo</th><th>Valor</th><th>Válido</th><th>Ativo</th><th>Ações</th></tr></thead>
    <tbody>
      @forelse($coupons as $c)
        <tr>
          <td data-label="ID">#{{ $c->id }}</td>
          <td data-label="Código"><code style="background:#fef3c7;padding:4px 8px;border-radius:4px;font-weight:700">{{ $c->code }}</code></td>
          <td data-label="Tipo">{{ $c->type }}</td>
          <td data-label="Valor">{{ $c->type==='percent' ? $c->value.'%' : 'R$ '.number_format($c->value,2,',','.') }}</td>
          <td data-label="Válido">
            @if($c->starts_at && $c->expires_at)
              <small>{{ \Carbon\Carbon::parse($c->starts_at)->format('d/m') }} até {{ \Carbon\Carbon::parse($c->expires_at)->format('d/m/Y') }}</small>
            @elseif($c->starts_at)
              <small>Desde {{ \Carbon\Carbon::parse($c->starts_at)->format('d/m/Y') }}</small>
            @elseif($c->expires_at)
              <small>Até {{ \Carbon\Carbon::parse($c->expires_at)->format('d/m/Y') }}</small>
            @else
              <small style="color:#10b981">Sempre válido</small>
            @endif
          </td>
          <td data-label="Status">
            <form method="POST" action="{{ route('dashboard.coupons.toggle', $c->id) }}" style="display:inline">
              @csrf
              <button type="submit" class="badge" style="{{ $c->is_active ? 'background:#d1fae5;color:#065f46' : 'background:#fee2e2;color:#991b1b' }}">
                {{ $c->is_active ? '✅ Ativo' : '❌ Inativo' }}
              </button>
            </form>
          </td>
          <td data-label="Ações">
            <div style="display:flex;gap:4px">
              <a href="{{ route('dashboard.coupons.edit', $c->id) }}" class="badge" style="background:#f59e0b;color:#fff">✏️ Editar</a>
              <form method="POST" action="{{ route('dashboard.coupons.destroy', $c->id) }}" style="display:inline" onsubmit="return confirm('Excluir este cupom?')">
                @csrf @method('DELETE')
                <button type="submit" class="badge" style="background:#ef4444;color:#fff">🗑️ Excluir</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;padding:20px">Nenhum cupom cadastrado</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px">{{ $coupons->links() }}</div>

</div>

@endsection

