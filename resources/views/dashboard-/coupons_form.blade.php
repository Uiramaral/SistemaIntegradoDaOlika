{{-- PÁGINA: Formulário de Cupom (Criar/Editar) --}}
@extends('layouts.dashboard')

@section('title', ($coupon ? 'Editar' : 'Novo').' Cupom — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:16px">{{ $coupon ? '✏️ Editar Cupom' : '➕ Novo Cupom' }}</h1>

  <form method="POST" action="{{ $coupon ? route('dashboard.coupons.update', $coupon->id) : route('dashboard.coupons.store') }}" style="max-width:640px">
    @csrf
    @if($coupon) @method('PUT') @endif

    <label style="display:block;margin-bottom:12px">
      Código <span style="color:#ef4444">*</span>
      <input name="code" class="card" value="{{ old('code', $coupon->code ?? '') }}" placeholder="CUPOM10" required>
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <label style="display:block">
        Tipo <span style="color:#ef4444">*</span>
        <select name="type" class="card" required>
          <option value="percent" {{ old('type', $coupon->type ?? '') == 'percent' ? 'selected' : '' }}>Percentual (%)</option>
          <option value="fixed" {{ old('type', $coupon->type ?? '') == 'fixed' ? 'selected' : '' }}>Valor fixo (R$)</option>
        </select>
      </label>

      <label style="display:block">
        Valor <span style="color:#ef4444">*</span>
        <input name="value" type="number" step="0.01" class="card" value="{{ old('value', $coupon->value ?? '') }}" required>
      </label>
    </div>

    <label style="display:block;margin-bottom:12px">
      Valor Mínimo
      <input name="minimum_amount" type="number" step="0.01" class="card" value="{{ old('minimum_amount', $coupon->minimum_amount ?? '') }}" placeholder="0 = sem mínimo">
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <label style="display:block">
        Data Início
        <input name="starts_at" type="date" class="card" value="{{ old('starts_at', $coupon->starts_at ?? '') }}">
      </label>

      <label style="display:block">
        Data Fim
        <input name="expires_at" type="date" class="card" value="{{ old('expires_at', $coupon->expires_at ?? '') }}">
      </label>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <label style="display:block">
        Limite de Usos
        <input name="usage_limit" type="number" class="card" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" placeholder="0 = ilimitado">
      </label>

      <label style="display:block">
        Por Cliente
        <input name="usage_limit_per_customer" type="number" class="card" value="{{ old('usage_limit_per_customer', $coupon->usage_limit_per_customer ?? '') }}" placeholder="0 = ilimitado">
      </label>
    </div>

    <label style="display:block;margin-bottom:12px">
      Visibilidade
      <select name="visibility" class="card">
        <option value="public" {{ old('visibility', $coupon->visibility ?? 'public') == 'public' ? 'selected' : '' }}>Público</option>
        <option value="targeted" {{ old('visibility', $coupon->visibility ?? '') == 'targeted' ? 'selected' : '' }}>Direcionado</option>
      </select>
    </label>

    <label style="display:flex;gap:8px;align-items:center;margin-bottom:16px">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
      Cupom ativo
    </label>

    <div style="display:flex;gap:8px">
      <button type="submit" class="btn" style="background:#059669;color:#fff">💾 Salvar</button>
      <a href="{{ route('dashboard.coupons') }}" class="btn" style="background:#6b7280;color:#fff">❌ Cancelar</a>
    </div>
  </form>

</div>

@endsection

