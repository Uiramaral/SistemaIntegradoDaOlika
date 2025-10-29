@extends('layouts.admin')

@section('title', isset($customer) ? 'Editar Cliente' : 'Novo Cliente')
@section('page_title', isset($customer) ? 'Editar Cliente' : 'Novo Cliente')

@section('content')
<div class="container-page">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">
      {{ isset($customer) ? 'Editar Cliente' : 'Novo Cliente' }}
    </h1>
  </div>

  @if ($errors->any())
    <x-alert type="danger">
      <ul class="list-disc pl-5 text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </x-alert>
  @endif

  <x-card>
    <form method="POST" action="{{ isset($customer) ? route('dashboard.customers.update', $customer) : route('dashboard.customers.store') }}" class="space-y-6">
      @csrf
      @if(isset($customer))
        @method('PUT')
      @endif

      <x-form-group label="Nome" required>
        <x-input name="nome" value="{{ old('nome', $customer->nome ?? '') }}" placeholder="Digite o nome completo" />
      </x-form-group>

      <x-form-group label="Telefone">
        <x-input name="telefone" value="{{ old('telefone', $customer->telefone ?? '') }}" placeholder="(11) 99999-9999" />
      </x-form-group>

      <x-form-group label="E-mail">
        <x-input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}" placeholder="cliente@email.com" />
      </x-form-group>

      <x-form-group label="Endereço">
        <x-input name="endereco" value="{{ old('endereco', $customer->endereco ?? '') }}" placeholder="Rua, número, bairro" />
      </x-form-group>

      <x-form-group label="CPF">
        <x-input name="cpf" value="{{ old('cpf', $customer->cpf ?? '') }}" placeholder="000.000.000-00" />
      </x-form-group>

      <x-form-group label="Fiado (R$)">
        <x-input type="number" name="fiado" step="0.01" value="{{ old('fiado', $customer->fiado ?? 0.00) }}" placeholder="0.00" />
        <p class="text-xs text-gray-500 mt-1">Valor em aberto que o cliente pode usar</p>
      </x-form-group>

      <div class="pt-4 flex gap-2">
        <x-button variant="primary" type="submit">
          <i class="fas fa-save mr-2"></i>{{ isset($customer) ? 'Atualizar' : 'Cadastrar' }}
        </x-button>
        <a href="{{ route('dashboard.customers.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-2"></i> Voltar
        </a>
      </div>
    </form>
  </x-card>
</div>
@endsection
