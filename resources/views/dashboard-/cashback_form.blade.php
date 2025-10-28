{{-- PÁGINA: Formulário de Cashback (Criar/Editar) --}}
@extends('layouts.dashboard')

@section('title', ($cashback ? 'Editar' : 'Novo').' Cashback — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:16px">{{ $cashback ? '✏️ Editar Cashback' : '➕ Novo Cashback' }}</h1>

  <form method="POST" action="{{ $cashback ? route('dashboard.cashback.update', $cashback->id) : route('dashboard.cashback.store') }}" style="max-width:640px">
    @csrf
    @if($cashback) @method('PUT') @endif

    <label style="display:block;margin-bottom:12px">
      Cliente <span style="color:#ef4444">*</span>
      <select name="customer_id" class="card" required>
        <option value="">— Selecione um cliente —</option>
        @foreach($customers as $c)
          <option value="{{ $c->id }}" {{ old('customer_id', $cashback->customer_id ?? '') == $c->id ? 'selected' : '' }}>
            {{ $c->name }} ({{ $c->phone }})
          </option>
        @endforeach
      </select>
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <label style="display:block">
        Valor <span style="color:#ef4444">*</span>
        <input name="amount" type="number" step="0.01" min="0" class="card" value="{{ old('amount', $cashback->amount ?? '') }}" required>
      </label>

      <label style="display:block">
        Tipo <span style="color:#ef4444">*</span>
        <select name="type" class="card" required>
          <option value="credit" {{ old('type', $cashback->type ?? '') == 'credit' ? 'selected' : '' }}>💳 Crédito</option>
          <option value="manual" {{ old('type', $cashback->type ?? '') == 'manual' ? 'selected' : '' }}>✋ Manual</option>
          <option value="bonus" {{ old('type', $cashback->type ?? '') == 'bonus' ? 'selected' : '' }}>🎁 Bônus</option>
        </select>
      </label>
    </div>

    @if($cashback)
      <label style="display:block;margin-bottom:12px">
        Status <span style="color:#ef4444">*</span>
        <select name="status" class="card" required>
          <option value="pending" {{ old('status', $cashback->status ?? '') == 'pending' ? 'selected' : '' }}>⏳ Pendente</option>
          <option value="active" {{ old('status', $cashback->status ?? '') == 'active' ? 'selected' : '' }}>✅ Ativo</option>
          <option value="used" {{ old('status', $cashback->status ?? '') == 'used' ? 'selected' : '' }}>💸 Usado</option>
          <option value="expired" {{ old('status', $cashback->status ?? '') == 'expired' ? 'selected' : '' }}>❌ Expirado</option>
        </select>
      </label>
    @endif

    <label style="display:block;margin-bottom:12px">
      Data de Expiração
      <input name="expires_at" type="date" class="card" value="{{ old('expires_at', $cashback->expires_at ?? '') }}">
    </label>

    <label style="display:block;margin-bottom:12px">
      Descrição
      <textarea name="description" class="card" rows="3" placeholder="Motivo do cashback, referência, etc.">{{ old('description', $cashback->description ?? '') }}</textarea>
    </label>

    <div style="display:flex;gap:8px;margin-top:16px">
      <button type="submit" class="btn" style="background:#059669;color:#fff">💾 Salvar</button>
      <a href="{{ route('dashboard.cashback') }}" class="btn" style="background:#6b7280;color:#fff">❌ Cancelar</a>
    </div>
  </form>

</div>

@endsection

