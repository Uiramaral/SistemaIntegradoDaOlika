{{-- PÁGINA: Formulário de Cliente (Criar/Editar) --}}
@extends('layouts.dashboard')

@section('title', ($customer ? 'Editar' : 'Novo').' Cliente — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:16px">{{ $customer ? '✏️ Editar Cliente' : '➕ Novo Cliente' }}</h1>

  <form method="POST" action="{{ $customer ? route('dashboard.customers.update', $customer->id) : route('dashboard.customers.store') }}" style="max-width:640px">
    @csrf
    @if($customer) @method('PUT') @endif

    <label style="display:block;margin-bottom:12px">
      Nome <span style="color:#ef4444">*</span>
      <input name="name" class="card" value="{{ old('name', $customer->name ?? '') }}" required>
    </label>

    <label style="display:block;margin-bottom:12px">
      Telefone <span style="color:#ef4444">*</span>
      <input name="phone" class="card" value="{{ old('phone', $customer->phone ?? '') }}" placeholder="5511999999999" required>
    </label>

    <label style="display:block;margin-bottom:12px">
      Email
      <input name="email" type="email" class="card" value="{{ old('email', $customer->email ?? '') }}">
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <label style="display:block">
        CPF
        <input name="cpf" class="card" value="{{ old('cpf', $customer->cpf ?? '') }}">
      </label>

      <label style="display:block">
        Data de Nascimento
        <input name="birth_date" type="date" class="card" value="{{ old('birth_date', $customer->birth_date ?? '') }}">
      </label>
    </div>

    <div style="display:flex;gap:8px;margin-top:16px">
      <button type="submit" class="btn" style="background:#059669;color:#fff">💾 Salvar</button>
      <a href="{{ route('dashboard.customers') }}" class="btn" style="background:#6b7280;color:#fff">❌ Cancelar</a>
    </div>
  </form>

</div>

@endsection

